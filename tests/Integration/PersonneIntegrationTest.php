<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\BDD\DbManager;
use App\BDD\entity\PersonneEntity;
use App\BDD\exception\DatabaseWriteException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests d'intégration de DbManager pour la table PERSONNE :
 * - insertPersonne()
 * - findAllPersonne()
 * - findPersonneByEmail()
 *
 * Rappel schéma : PERSONNE(email VARCHAR(100) PK, nom VARCHAR(50) NOT NULL,
 * prenom VARCHAR(50) NULL, tel VARCHAR(15) NULL, estMorale TINYINT(1) NOT NULL).
 * Aucune clé étrangère sortante : pas de scénario de FK manquante à tester
 * pour cette table.
 *
 * Les bornes de longueur (email <= 100, nom <= 50, tel <= 15, format du
 * téléphone) sont déjà garanties par PersonneEntity::__construct() et
 * couvertes par les tests unitaires de l'entité : elles ne sont PAS
 * reproduites ici, conformément à la consigne de ne pas dupliquer une
 * validation déjà garantie par le constructeur.
 */
#[CoversClass(DbManager::class)]
#[Group('integration')]
final class PersonneIntegrationTest extends AbstractDatabaseIntegrationTestCase
{
    #region insertPersonne() - cas nominal

    /**
     * Objectif : vérifier qu'un insert réussi écrit réellement la ligne
     * attendue en base, pour une personne physique ET pour une personne
     * morale (le booléen estMorale impacte le cast vers tinyint(1)).
     *
     * Utilité : c'est le test de référence de l'INSERT ; sans lui, rien
     * ne garantit que le SQL exécuté correspond au mapping attendu des
     * colonnes (email/nom/prenom/tel/estMorale), ni que le format
     * téléphone normalisé par l'entité (+33 sans espace) est bien ce qui
     * est persisté tel quel.
     *
     * Risque couvert : régression silencieuse du mapping colonne <-> propriété,
     * erreur de cast du booléen estMorale en tinyint(1).
     */
    #[DataProvider('personnePhysiqueOuMoraleProvider')]
    public function testInsertPersonneNominalPersistsExpectedRow(bool $estMorale): void
    {
        $email = $this->uniqueEmail('nominal');
        $personne = new PersonneEntity(
            mail: $email,
            nom: 'Durand',
            prenom: 'Alice',
            tel: '0612345678',
            estMorale: $estMorale
        );

        $this->dbManager->insertPersonne($personne);
        $this->trackPersonneEmail($email);

        $row = $this->fetchPersonneRow($email);

        self::assertNotFalse($row, 'La ligne PERSONNE aurait dû être créée.');
        self::assertSame($email, $row['email']);
        self::assertSame('Durand', $row['nom']);
        self::assertSame('Alice', $row['prenom']);
        // PersonneEntity normalise le numéro au format +33 sans espace avant insertion.
        self::assertSame('+33612345678', $row['tel']);
        self::assertSame($estMorale ? 1 : 0, (int) $row['estMorale']);
    }

    public static function personnePhysiqueOuMoraleProvider(): array
    {
        return [
            'personne physique' => [false],
            'personne morale' => [true],
        ];
    }

    #endregion

    #region insertPersonne() - valeurs nullables

    /**
     * Objectif : vérifier que prenom et tel (tous deux nullable en base :
     * PERSONNE.prenom, PERSONNE.tel DEFAULT NULL) sont bien persistés à
     * NULL lorsque l'entité les porte à null.
     *
     * Utilité : seul un test contre la vraie base peut garantir qu'un
     * binding PDO de valeur null produit un NULL SQL et non, par exemple,
     * une chaîne vide ou la chaîne littérale "NULL".
     *
     * Risque couvert : mauvais binding PDO de valeurs null qui romprait
     * silencieusement la sémantique "non renseigné" (impacterait
     * notamment getFormattedTel() et tout filtrage `WHERE tel IS NULL`).
     */
    public function testInsertPersonneWithNullablePrenomAndTelPersistsNullValues(): void
    {
        $email = $this->uniqueEmail('nullable');
        $personne = new PersonneEntity(
            mail: $email,
            nom: 'Société Test',
            prenom: null,
            tel: null,
            estMorale: true
        );

        $this->dbManager->insertPersonne($personne);
        $this->trackPersonneEmail($email);

        $row = $this->fetchPersonneRow($email);

        self::assertNotFalse($row);
        self::assertNull($row['prenom']);
        self::assertNull($row['tel']);
    }

    #endregion

    #region insertPersonne() - contrainte de clé primaire

    /**
     * Objectif : vérifier qu'un second insertPersonne() avec le même
     * email (clé primaire PERSONNE.email) échoue avec une
     * DatabaseWriteException, et que la ligne déjà en base n'est pas
     * altérée par la tentative.
     *
     * Utilité : insertPersonne() ne fait pas d'UPSERT. Ce comportement
     * (doublon -> exception, jamais d'écrasement silencieux) est une
     * garantie métier importante (intégrité du référentiel PERSONNE)
     * qui ne peut être vérifiée qu'en conditions réelles de base.
     *
     * Risque couvert : régression qui transformerait silencieusement le
     * comportement en UPSERT, ou qui romprait la levée de
     * DatabaseWriteException en cas de violation de la PK.
     */
    public function testInsertPersonneWithDuplicateEmailThrowsDatabaseWriteExceptionAndKeepsOriginalRow(): void
    {
        $email = $this->uniqueEmail('dup');
        $this->dbManager->insertPersonne(new PersonneEntity(
            mail: $email,
            nom: 'Original',
            prenom: 'Premier',
            tel: null,
            estMorale: false
        ));
        $this->trackPersonneEmail($email);

        $this->expectException(DatabaseWriteException::class);

        try {
            $this->dbManager->insertPersonne(new PersonneEntity(
                mail: $email,
                nom: 'Différent',
                prenom: 'Second',
                tel: null,
                estMorale: true
            ));
        } finally {
            $row = $this->fetchPersonneRow($email);
            self::assertNotFalse($row);
            self::assertSame('Original', $row['nom'], 'La ligne existante ne doit pas être écrasée.');
        }
    }

    /**
     * Objectif : vérifier que la contrainte d'unicité sur PERSONNE.email
     * s'applique également lorsque seule la CASSE diffère, en raison de
     * la collation utf8mb4_general_ci (insensible à la casse) déclarée
     * sur la colonne.
     *
     * Utilité : ce comportement dépend de la collation du schéma SQL et
     * n'est PAS (et ne peut pas être) vérifié par PersonneEntity, qui
     * ignore tout de la base de données. C'est donc un cas qui relève
     * strictement du test d'intégration.
     *
     * Risque couvert : duplication logique de personnes avec un email
     * identique à la casse près, ce qui romprait l'hypothèse "un email
     * = une personne" sur laquelle s'appuient insertContactForm() et
     * insertDevisForm() (via findPersonneByEmail()).
     */
    public function testInsertPersonneWithSameEmailDifferentCaseThrowsDatabaseWriteException(): void
    {
        $email = $this->uniqueEmail('case');
        $this->dbManager->insertPersonne(new PersonneEntity(
            mail: $email,
            nom: 'Original',
            prenom: null,
            tel: null,
            estMorale: false
        ));
        $this->trackPersonneEmail($email);

        $emailDifferentCase = strtoupper($email);

        $this->expectException(DatabaseWriteException::class);

        try {
            $this->dbManager->insertPersonne(new PersonneEntity(
                mail: $emailDifferentCase,
                nom: 'Autre',
                prenom: null,
                tel: null,
                estMorale: false
            ));
        } finally {
            // Si jamais une ligne avait été créée avec la casse différente
            // (ce qui invaliderait l'hypothèse testée), on la nettoie aussi.
            $this->trackPersonneEmail($emailDifferentCase);
        }
    }

    #endregion

    #region findAllPersonne() - cas nominal

    /**
     * Objectif : vérifier que findAllPersonne() renvoie, parmi
     * l'ensemble des personnes déjà présentes en base (jeu de données de
     * démonstration inclus), une entité correspondant exactement à la
     * ligne insérée par le test.
     *
     * Utilité : la base contient un jeu de données préexistant ; on ne
     * peut donc pas comparer le résultat en taille absolue, seulement
     * vérifier la présence et l'exactitude de la ligne du test
     * (assertion par recherche dans le tableau retourné).
     *
     * Risque couvert : erreur d'hydratation de PersonneEntity depuis la
     * ligne SQL (mapping de colonnes, cast du booléen estMorale).
     */
    public function testFindAllPersonneContainsInsertedRow(): void
    {
        $email = $this->insertRawPersonne(
            email: $this->uniqueEmail('findall'),
            nom: 'Lefevre',
            prenom: 'Marc',
            tel: '+33687654321',
            estMorale: false
        );

        $personnes = $this->dbManager->findAllPersonne();

        $match = $this->findPersonneInArrayByEmail($personnes, $email);

        self::assertNotNull($match, 'La personne insérée doit être présente dans findAllPersonne().');
        self::assertSame('Lefevre', $match->getNom());
        self::assertSame('Marc', $match->getPrenom());
        self::assertSame('+33687654321', $match->getTel());
        self::assertFalse($match->isEstMorale());
    }

    #endregion

    #region findPersonneByEmail() - cas nominal, nullable, non trouvé

    /**
     * Objectif : vérifier que findPersonneByEmail() hydrate correctement
     * tous les champs d'une ligne existante.
     *
     * Risque couvert : erreur de mapping colonne SQL -> propriété entité.
     */
    public function testFindPersonneByEmailExistingEmailReturnsMatchingEntity(): void
    {
        $email = $this->insertRawPersonne(
            email: $this->uniqueEmail('byemail'),
            nom: 'Martin',
            prenom: 'Julie',
            tel: '+33611223344',
            estMorale: false
        );

        $result = $this->dbManager->findPersonneByEmail($email);

        self::assertInstanceOf(PersonneEntity::class, $result);
        self::assertSame($email, $result->getMail());
        self::assertSame('Martin', $result->getNom());
        self::assertSame('Julie', $result->getPrenom());
        self::assertSame('+33611223344', $result->getTel());
        self::assertFalse($result->isEstMorale());
    }

    /**
     * Objectif : vérifier que les colonnes nullable (prenom, tel) sont
     * bien hydratées à null dans l'entité, et non à une chaîne vide.
     *
     * Risque couvert : hydratation incorrecte d'une colonne SQL NULL qui
     * romprait l'égalité stricte attendue par getPrenom()/getTel() === null,
     * et qui ferait échouer la construction de PersonneEntity dans les
     * méthodes appelantes (le constructeur rejette un prénom vide ? non,
     * mais un numéro de téléphone vide/mal formé serait rejeté).
     */
    public function testFindPersonneByEmailWithNullColumnsMapsToNullProperties(): void
    {
        $email = $this->insertRawPersonne(
            email: $this->uniqueEmail('nullread'),
            nom: 'Entreprise Test',
            prenom: null,
            tel: null,
            estMorale: true
        );

        $result = $this->dbManager->findPersonneByEmail($email);

        self::assertInstanceOf(PersonneEntity::class, $result);
        self::assertNull($result->getPrenom());
        self::assertNull($result->getTel());
    }

    /**
     * Objectif : vérifier qu'une recherche sur un email absent de la
     * base renvoie null, sans lever d'exception.
     */
    public function testFindPersonneByEmailNonExistentEmailReturnsNull(): void
    {
        $result = $this->dbManager->findPersonneByEmail($this->uniqueEmail('absent'));

        self::assertNull($result);
    }

    #endregion

    /**
     * @param PersonneEntity[] $personnes
     */
    private function findPersonneInArrayByEmail(array $personnes, string $email): ?PersonneEntity
    {
        foreach ($personnes as $personne) {
            if ($personne->getMail() === $email) {
                return $personne;
            }
        }

        return null;
    }
}
