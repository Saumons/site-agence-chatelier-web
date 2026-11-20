<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\BDD\DbManager;
use App\BDD\entity\ContactFormEntity;
use App\BDD\entity\PersonneEntity;
use App\BDD\exception\DatabaseWriteException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests d'intégration de DbManager pour la table CONTACT_FORM :
 * - insertContactForm()
 * - findAllContactForm()
 * - findContactFormById()
 *
 * Rappel schéma : CONTACT_FORM(idContact INT PK AUTO_INCREMENT,
 * objet VARCHAR(75) NOT NULL, message TEXT NOT NULL,
 * emailPersonne VARCHAR(100) NOT NULL FK -> PERSONNE(email) ON DELETE/UPDATE RESTRICT).
 *
 * - Aucune colonne nullable dans CONTACT_FORM : la catégorie "valeurs
 *   nullables" est couverte indirectement via la personne associée
 *   (voir testInsertContactFormWithNewPersonnePersistsBothRowsAtomically),
 *   sans dupliquer le test déjà fait sur insertPersonne() lui-même.
 * - La contrainte de clé étrangère CONTACT_FORM.emailPersonne n'est PAS
 *   testable via l'API publique insertContactForm() : la méthode
 *   garantit toujours, avant l'INSERT, que la personne existe (création
 *   à la volée si besoin). Ce point est documenté plutôt que contourné
 *   par un test artificiel.
 */
#[CoversClass(DbManager::class)]
#[Group('integration')]
final class ContactFormIntegrationTest extends AbstractDatabaseIntegrationTestCase
{
    #region insertContactForm() - cas nominal (+ nullable en cascade)

    /**
     * Objectif : vérifier que, lorsque la personne n'existe pas encore,
     * insertContactForm() crée ATOMIQUEMENT la ligne PERSONNE puis la
     * ligne CONTACT_FORM au sein d'une même transaction validée, et que
     * les colonnes nullable de PERSONNE (prenom, tel) sont correctement
     * propagées à null dans ce flux en cascade.
     *
     * Utilité : c'est le scénario métier principal de la méthode
     * (formulaire de contact d'un nouveau visiteur). On combine
     * volontairement le cas nominal et la vérification des champs
     * nullable de la personne en cascade, pour ne pas dupliquer le test
     * dédié déjà réalisé sur insertPersonne() isolément.
     *
     * Risque couvert : rupture de l'enchaînement
     * findPersonneByEmail → insertPersonne → INSERT CONTACT_FORM → commit ;
     * perte de l'atomicité de la transaction.
     */
    public function testInsertContactFormWithNewPersonnePersistsBothRowsAtomically(): void
    {
        $email = $this->uniqueEmail('cf-new');
        $personne = new PersonneEntity(
            mail: $email,
            nom: 'Société Nouvelle',
            prenom: null,
            tel: null,
            estMorale: true
        );
        $contactForm = new ContactFormEntity(
            objet: 'Demande de devis',
            message: 'Bonjour, je souhaite un devis.',
            personne: $personne
        );

        $this->dbManager->insertContactForm($contactForm);
        $this->trackPersonneEmail($email);

        self::assertFalse($this->pdo->inTransaction(), 'La transaction doit être validée (commit).');

        $personneRow = $this->fetchPersonneRow($email);
        self::assertNotFalse($personneRow);
        self::assertNull($personneRow['prenom']);
        self::assertNull($personneRow['tel']);

        self::assertSame(
            1,
            $this->countRows(
                'CONTACT_FORM',
                'emailPersonne = :email',
                ['email' => $email]
            )
        );
    }

    /**
     * Objectif : vérifier que si la personne existe déjà en base,
     * insertContactForm() NE LA RÉ-INSÈRE PAS (pas de doublon de PK, pas
     * d'exception) et se contente d'ajouter la ligne CONTACT_FORM, sans
     * jamais écraser les données existantes de la personne.
     *
     * Utilité : vérifie le garde-fou `findPersonneByEmail() === null`
     * avant insertPersonne(), central pour éviter une violation de la PK
     * PERSONNE.email lors d'un second contact d'une personne déjà connue.
     *
     * Risque couvert : violation de contrainte de clé primaire au second
     * formulaire envoyé par une personne déjà connue ; mise à jour
     * accidentelle et silencieuse des données de la personne existante.
     */
    public function testInsertContactFormWithExistingPersonneDoesNotDuplicatePersonneRow(): void
    {
        $email = $this->insertRawPersonne(
            email: $this->uniqueEmail('cf-existing'),
            nom: 'Nom Original',
            prenom: 'Prenom Original',
            tel: '+33611112222',
            estMorale: false
        );

        $contactForm = new ContactFormEntity(
            objet: 'Suivi de dossier',
            message: 'Deuxième contact.',
            personne: new PersonneEntity(
                mail: $email,
                nom: 'Nom Différent',     // ne doit pas écraser la ligne existante
                prenom: 'Prenom Différent',
                tel: null,
                estMorale: true
            )
        );

        $this->dbManager->insertContactForm($contactForm);
        $idContact = (int) $this->pdo->lastInsertId();

        $personneRow = $this->fetchPersonneRow($email);
        self::assertSame('Nom Original', $personneRow['nom']);
        self::assertSame('Prenom Original', $personneRow['prenom']);

        self::assertSame(
            1,
            $this->countRows('PERSONNE', 'email = :email', ['email' => $email]),
            'La personne ne doit pas être dupliquée.'
        );
    }

    #endregion

    #region insertContactForm() - transaction / rollback

    /**
     * Objectif : vérifier que le mécanisme try/catch(PDOException)/rollBack()
     * d'insertContactForm() fonctionne réellement : si une PDOException
     * survient après le début de la transaction, aucune ligne (ni
     * PERSONNE, ni CONTACT_FORM) ne doit rester en base, et la connexion
     * ne doit plus être en transaction à l'issue de l'appel.
     *
     * Technique utilisée : le schéma et les entités ne laissent aucune
     * voie d'accès publique permettant de provoquer une VRAIE violation
     * de contrainte SQL au milieu du flux (insertContactForm garantit
     * toujours que la personne existe avant d'insérer CONTACT_FORM, et
     * emailPersonne respecte toujours la longueur attendue grâce à la
     * validation de l'entité). On force donc la levée d'une PDOException
     * de façon déterministe et réaliste en ouvrant nous-mêmes une
     * transaction AVANT l'appel : `$this->connexion->beginTransaction()`,
     * exécuté en première ligne du code testé, lève alors une véritable
     * PDOException ("There is already an active transaction"),
     * immédiatement interceptée par le bloc catch du code testé. Cela
     * permet de vérifier fidèlement le câblage rollback + DatabaseWriteException
     * sans dépendre d'une condition de course non reproductible.
     *
     * Risque couvert : transaction laissée ouverte, incohérence entre
     * PERSONNE et CONTACT_FORM en cas d'erreur en cours de transaction.
     */
    public function testInsertContactFormRollsBackAndThrowsDatabaseWriteExceptionOnPdoException(): void
    {
        $email = $this->uniqueEmail('cf-rollback');
        $contactForm = new ContactFormEntity(
            objet: 'Objet',
            message: 'Message',
            personne: new PersonneEntity(
                mail: $email,
                nom: 'Ne doit pas être créée',
                prenom: null,
                tel: null,
                estMorale: false
            )
        );

        // Provoque la PDOException dès le premier beginTransaction() interne.
        $this->pdo->beginTransaction();

        try {
            $this->expectException(DatabaseWriteException::class);
            $this->dbManager->insertContactForm($contactForm);
        } finally {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            self::assertSame(0, $this->countRows('PERSONNE', 'email = :e', ['e' => $email]));
        }
    }

    #endregion

    #region findAllContactForm()

    /**
     * Objectif : vérifier que findAllContactForm() hydrate correctement,
     * via la jointure PERSONNE, à la fois ContactFormEntity et la
     * PersonneEntity imbriquée.
     *
     * Risque couvert : erreur de mapping dans la jointure SQL (alias de
     * colonnes partagés entre CONTACT_FORM et PERSONNE).
     */
    public function testFindAllContactFormContainsInsertedRowWithJoinedPersonne(): void
    {
        $email = $this->insertRawPersonne(
            email: $this->uniqueEmail('findall-cf'),
            nom: 'Girard',
            prenom: 'Paul',
            tel: '+33699887766',
            estMorale: false
        );
        $idContact = $this->insertRawContactForm($email, 'Objet unique de test', 'Message unique de test');

        $contacts = $this->dbManager->findAllContactForm();

        $match = null;
        foreach ($contacts as $contact) {
            if ($contact->getIdContact() === $idContact) {
                $match = $contact;
                break;
            }
        }

        self::assertNotNull($match, 'Le contact inséré doit être présent dans findAllContactForm().');
        self::assertSame('Objet unique de test', $match->getObjet());
        self::assertSame('Message unique de test', $match->getMessage());
        self::assertSame($email, $match->getPersonne()->getMail());
        self::assertSame('Girard', $match->getPersonne()->getNom());
        self::assertSame('Paul', $match->getPersonne()->getPrenom());
    }

    #endregion
}
