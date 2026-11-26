<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\BDD\DbManager;
use App\BDD\entity\DevisFormEntity;
use App\BDD\entity\OptionEntity;
use App\BDD\entity\PersonneEntity;
use App\BDD\exception\DatabaseWriteException;
use App\enums\DevisFormStatutEnum;
use App\enums\DevisFormTypeEnum;
use App\enums\OptionTypeEnum;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests d'intégration de DbManager pour la table DEVIS_FORM :
 * - insertDevisForm()
 * - findAllDevisForm()
 * - findDevisFormById()
 *
 * Rappel schéma : DEVIS_FORM(idDevis SMALLINT PK AUTO_INCREMENT,
 * type ENUM(...) NOT NULL, nomProjet VARCHAR(50) NULL,
 * descriptionProjet TEXT NOT NULL, informationComplementaire TEXT NULL,
 * statut ENUM(...) NOT NULL,
 * emailPersonne VARCHAR(100) NOT NULL FK -> PERSONNE(email)).
 *
 * Comme pour CONTACT_FORM, la contrainte de clé étrangère
 * DEVIS_FORM.emailPersonne n'est pas testable via l'API publique
 * insertDevisForm() : la méthode garantit toujours que la personne
 * existe avant l'INSERT.
 */
#[CoversClass(DbManager::class)]
#[Group('integration')]
final class DevisFormIntegrationTest extends AbstractDatabaseIntegrationTestCase
{
    #region insertDevisForm() - cas nominal

    /**
     * Objectif : vérifier que, pour une nouvelle personne, insertDevisForm()
     * crée la PERSONNE, la ligne DEVIS_FORM, ET une ligne DEVIS_OPTION par
     * option associée (avec le bon "compteur"), le tout de façon
     * cohérente dans la même transaction.
     *
     * Utilité : c'est le scénario métier principal (nouveau client
     * demandant un devis avec options). Couvre en un seul test la
     * cascade personne + devis + options et la bonne récupération de
     * lastInsertId() pour idDevis.
     *
     * Risque couvert : rupture de la boucle d'insertion des options,
     * mauvaise valeur de idDevis utilisée pour les liens DEVIS_OPTION.
     */
    public function testInsertDevisFormWithNewPersonneAndOptionsPersistsEverything(): void
    {
        $email = $this->uniqueEmail('devis-new');
        $idOption1 = $this->insertRawOption($this->uniqueOptionNom('devis1'), 2.0);
        $idOption2 = $this->insertRawOption($this->uniqueOptionNom('devis2'), 1.5);

        $devisForm = new DevisFormEntity(
            type: DevisFormTypeEnum::vitrine,
            nomProjet: 'Site vitrine',
            descriptionProjet: 'Un site vitrine pour mon activité.',
            statut: DevisFormStatutEnum::aTraiter,
            infoComplementaire: 'Urgent',
            personne: new PersonneEntity(
                mail: $email,
                nom: 'Nouveau Client',
                prenom: null,
                tel: null,
                estMorale: true
            ),
            options: [
                new OptionEntity(
                    nom: 'ignoré-option1',
                    nbJourHomme: 1.0,
                    type: OptionTypeEnum::vitrine,
                    nbAttribut: 3,
                    idOption: $idOption1
                ),
                new OptionEntity(
                    nom: 'ignoré-option2',
                    nbJourHomme: 1.0,
                    type: OptionTypeEnum::vitrine,
                    nbAttribut: null,
                    idOption: $idOption2
                ),
            ]
        );

        $this->dbManager->insertDevisForm($devisForm);
        $this->trackPersonneEmail($email);

        $stmt = $this->pdo->prepare(
            'SELECT idDevis
             FROM DEVIS_FORM
             WHERE emailPersonne = :email
             ORDER BY idDevis DESC
             LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);

        $idDevis = (int) $stmt->fetchColumn();

        self::assertGreaterThan(0, $idDevis);

        self::assertFalse($this->pdo->inTransaction());
        self::assertSame(
            1,
            $this->countRows(
                'PERSONNE',
                'email = :e',
                ['e' => $email]));
        self::assertSame(
            1,
            $this->countRows(
                'DEVIS_FORM',
                'idDevis = :d',
                ['d' => $idDevis]
            )
        );

        self::assertSame(
            2,
            $this->countRows(
                'DEVIS_OPTION',
                'idDevis = :d',
                ['d' => $idDevis]
            )
        );

        $stmt = $this->pdo->prepare('SELECT compteur FROM `DEVIS_OPTION` WHERE idDevis = :d AND idOption = :o');
        $stmt->execute(['d' => $idDevis, 'o' => $idOption1]);
        self::assertSame(3, (int) $stmt->fetchColumn());
    }

    /**
     * Objectif : vérifier que les colonnes nullable de DEVIS_FORM
     * (nomProjet, informationComplementaire) sont bien persistées à null,
     * et que, comme pour insertContactForm(), une personne déjà existante
     * n'est pas réinsérée.
     *
     * Risque couvert : mauvais binding des colonnes nullable de
     * DEVIS_FORM ; duplication de personne.
     */
    public function testInsertDevisFormWithExistingPersonneAndNullableFieldsPersistsNulls(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('devis-existing'));

        $devisForm = new DevisFormEntity(
            type: DevisFormTypeEnum::commerce,
            nomProjet: null,
            descriptionProjet: 'Description minimale.',
            statut: DevisFormStatutEnum::enCours,
            infoComplementaire: null,
            personne: new PersonneEntity(
                mail: $email,
                nom: 'Ne doit pas écraser',
                prenom: null,
                tel: null,
                estMorale: false
            )
        );

        $this->dbManager->insertDevisForm($devisForm);

        self::assertSame(1, $this->countRows('PERSONNE', 'email = :e', ['e' => $email]));

        $stmt = $this->pdo->prepare(
            'SELECT nomProjet, informationComplementaire
                     FROM `DEVIS_FORM`
                     WHERE emailPersonne = :email
                     ORDER BY idDevis DESC
                     LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        self::assertNotFalse($row);
        self::assertNull($row['nomProjet']);
        self::assertNull($row['informationComplementaire']);
        self::assertSame(
            1,
            $this->countRows(
                'DEVIS_FORM',
                'emailPersonne = :email',
                ['email' => $email]
            )
        );
    }

    #endregion

    #region insertDevisForm() - cas limite : aucune option

    /**
     * Objectif : vérifier qu'un devis sans aucune option (tableau vide,
     * cas valide puisque DevisFormEntity::$options peut être []) est
     * inséré correctement, sans aucune ligne DEVIS_OPTION créée, et sans
     * erreur liée à une boucle vide.
     *
     * Risque couvert : régression sur la boucle foreach de la méthode si
     * elle venait à supposer au moins une option.
     */
    public function testInsertDevisFormWithoutOptionsPersistsDevisOnly(): void
    {
        $email = $this->uniqueEmail('devis-no-option');

        $devisForm = new DevisFormEntity(
            type: DevisFormTypeEnum::webApp,
            nomProjet: 'App sans option',
            descriptionProjet: 'Aucune option sélectionnée.',
            statut: DevisFormStatutEnum::accepte,
            infoComplementaire: null,
            personne: new PersonneEntity(
                mail: $email,
                nom: 'Client Minimal',
                prenom: null,
                tel: null,
                estMorale: false
            )
        );

        $this->dbManager->insertDevisForm($devisForm);

        $this->trackPersonneEmail($email);

        self::assertSame(
            1,
            $this->countRows(
                'PERSONNE',
                'email = :email',
                ['email' => $email]
            )
        );

        self::assertSame(
            1,
            $this->countRows(
                'DEVIS_FORM',
                'emailPersonne = :email',
                ['email' => $email]
            )
        );

        $stmt = $this->pdo->prepare(
            'SELECT idDevis
         FROM `DEVIS_FORM`
         WHERE emailPersonne = :email
         ORDER BY idDevis DESC
         LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);

        $idDevis = (int) $stmt->fetchColumn();

        self::assertGreaterThan(
            0,
            $idDevis
        );

        self::assertSame(
            0,
            $this->countRows(
                'DEVIS_OPTION',
                'idDevis = :idDevis',
                ['idDevis' => $idDevis]
            )
        );
    }

    #endregion

    #region insertDevisForm() - transaction / rollback - ⚠️ défaut découvert

    /**
     * Objectif : documenter un défaut réel de robustesse d'insertDevisForm().
     *
     * ⚠️ DÉFAUT DÉTECTÉ : insertDevisOption() encapsule son PROPRE
     * try/catch(PDOException) et relance systématiquement une
     * DatabaseWriteException (jamais une PDOException). Or l'appel
     *     foreach ($devisForm->getOptions() as $option) {
     *         $this->insertDevisOption($idDevis, $option);
     *     }
     * se trouve À L'INTÉRIEUR du bloc try d'insertDevisForm(), dont le
     * catch ne filtre que `PDOException`. Conséquence : si une option
     * déclenche une violation de contrainte (ici, clé étrangère idOption
     * inexistante), la DatabaseWriteException levée par
     * insertDevisOption() N'EST PAS interceptée par insertDevisForm() :
     * ni rollBack(), ni ré-encapsulation. La transaction reste OUVERTE,
     * et la ligne PERSONNE / DEVIS_FORM déjà insérée dans cette
     * transaction reste ni validée, ni annulée.
     *
     * Ce test prouve :
     *  1. qu'une DatabaseWriteException est bien propagée jusqu'à
     *     l'appelant (message provenant d'insertDevisOption(), inchangé) ;
     *  2. que connexion->inTransaction() est TOUJOURS VRAI juste après
     *     (preuve du défaut : pas de rollback automatique) ;
     *  3. qu'un rollBack() manuel ultérieur fait disparaître le DEVIS_FORM
     *     partiellement inséré, preuve qu'il n'avait jamais été validé.
     *
     * Recommandation : faire en sorte qu'insertDevisForm() intercepte
     * aussi DatabaseWriteException (ou directement \Throwable) autour de
     * la boucle d'insertion des options, afin de garantir le rollback
     * dans tous les cas d'échec.
     *
     * Risque couvert : connexion laissée dans un état transactionnel
     * incohérent après un échec partiel, pouvant affecter les requêtes
     * suivantes exécutées sur la même connexion (ex: requête HTTP
     * suivante si la connexion est réutilisée/partagée).
     */
    public function testInsertDevisFormLeavesTransactionOpenWhenOptionInsertionFails(): void
    {
        $email = $this->uniqueEmail('devis-rollback');
        $invalidOption = new OptionEntity(
            nom: 'inexistante',
            nbJourHomme: 1.0,
            type: OptionTypeEnum::vitrine,
            idOption: 999999
        );

        $devisForm = new DevisFormEntity(
            type: DevisFormTypeEnum::vitrine,
            nomProjet: 'Projet qui va échouer',
            descriptionProjet: 'Description.',
            statut: DevisFormStatutEnum::aTraiter,
            infoComplementaire: null,
            personne: new PersonneEntity(
                mail: $email,
                nom: 'Client Rollback',
                prenom: null,
                tel: null,
                estMorale: false
            ),
            options: [$invalidOption]
        );

        try {
            $this->dbManager->insertDevisForm($devisForm);
            self::fail('Une DatabaseWriteException était attendue.');
        } catch (DatabaseWriteException $e) {
            // 1. Le message vient bien d'insertDevisOption(), sans ré-encapsulation.
            self::assertStringContainsString('option', strtolower($e->getMessage()));

            // 2. Preuve du défaut : la transaction est restée ouverte.
            self::assertTrue(
                $this->pdo->inTransaction(),
                'Défaut attendu : la transaction aurait dû être fermée (commit ou rollback) mais reste ouverte.'
            );

            $idDevisNonValide = (int) $this->pdo->lastInsertId();
            self::assertSame(
                1,
                $this->countRows('DEVIS_FORM', 'idDevis = :d', ['d' => $idDevisNonValide]),
                'La ligne DEVIS_FORM non validée reste visible dans la transaction en cours.'
            );

            // 3. Nettoyage explicite + preuve que rien n'était réellement
            //    persisté : après rollback, la ligne disparaît.
            $this->pdo->rollBack();

            self::assertSame(
                0,
                $this->countRows('DEVIS_FORM', 'idDevis = :d', ['d' => $idDevisNonValide]),
                "Après rollback manuel, le devis ne doit plus exister : il n'avait jamais été validé."
            );
            self::assertSame(0, $this->countRows('PERSONNE', 'email = :e', ['e' => $email]));
        }
    }

    #endregion

    #region findAllDevisForm()

    /**
     * Objectif : vérifier que findAllDevisForm() agrège correctement,
     * via les LEFT JOIN DEVIS_OPTION / OPTION, toutes les options d'un
     * même devis sous un seul DevisFormEntity (pas de doublon par ligne
     * jointe).
     *
     * Risque couvert : duplication du devis dans le tableau retourné, ou
     * perte d'options lors de l'agrégation des lignes du JOIN.
     */
    public function testFindAllDevisFormAggregatesOptionsOfSameDevis(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('findall-devis'));
        $idDevis = $this->insertRawDevisForm($email, nomProjet: 'Projet Findall');
        $idOption1 = $this->insertRawOption($this->uniqueOptionNom('agg1'), 1.0, OptionTypeEnum::webApp);
        $idOption2 = $this->insertRawOption($this->uniqueOptionNom('agg2'), 2.0, OptionTypeEnum::trans);
        $this->insertRawDevisOption($idDevis, $idOption1, 5);
        $this->insertRawDevisOption($idDevis, $idOption2, null);

        $devisList = $this->dbManager->findAllDevisForm();

        $match = $this->findDevisInArrayById($devisList, $idDevis);

        self::assertNotNull($match);
        self::assertCount(2, $match->getOptions());

        $nbAttributs = array_map(
            static fn (OptionEntity $o) => $o->getNbAttribut(),
            $match->getOptions()
        );
        sort($nbAttributs);
        self::assertSame([null, 5], $nbAttributs);

        // Couverture OPTION.type : chaque option du devis doit être
        // hydratée avec le bon OptionTypeEnum (et non une valeur par
        // défaut ou celle d'une autre option agrégée par erreur).
        $typesById = [];
        foreach ($match->getOptions() as $option) {
            $typesById[$option->getIdOption()] = $option->getType();
        }
        self::assertSame(OptionTypeEnum::webApp, $typesById[$idOption1]);
        self::assertSame(OptionTypeEnum::trans, $typesById[$idOption2]);
    }

    /**
     * Objectif : vérifier qu'un devis sans aucune option associée est
     * bien renvoyé avec un tableau d'options vide (et non une entrée
     * fantôme provenant du LEFT JOIN à NULL).
     */
    public function testFindAllDevisFormWithoutOptionsReturnsEmptyOptionsArray(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('findall-devis-noopt'));
        $idDevis = $this->insertRawDevisForm($email, nomProjet: 'Sans option');

        $devisList = $this->dbManager->findAllDevisForm();

        $match = $this->findDevisInArrayById($devisList, $idDevis);

        self::assertNotNull($match);
        self::assertSame([], $match->getOptions());
    }

    #endregion

    #region findDevisFormById()

    /**
     * Objectif : vérifier que findDevisFormById() hydrate correctement
     * tous les champs du devis (type, nomProjet, statut, informations
     * complémentaires, personne) ainsi que ses options.
     */
    public function testFindDevisFormByIdExistingIdReturnsMatchingEntityWithOptions(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('byid-devis'));
        $idDevis = $this->insertRawDevisForm(
            $email,
            type: DevisFormTypeEnum::commerce,
            nomProjet: 'Projet ById',
            descriptionProjet: 'Description ById',
            statut: DevisFormStatutEnum::annule,
            infoComplementaire: 'Info'
        );
        $idOption = $this->insertRawOption($this->uniqueOptionNom('byid-opt'), 4.0, OptionTypeEnum::commerce);
        $this->insertRawDevisOption($idDevis, $idOption, 2);

        $result = $this->dbManager->findDevisFormById($idDevis);

        self::assertNotNull($result);
        self::assertSame(DevisFormTypeEnum::commerce, $result->getType());
        self::assertSame('Projet ById', $result->getNomProjet());
        self::assertSame(DevisFormStatutEnum::annule, $result->getStatut());
        self::assertSame('Info', $result->getInfoComplementaire());
        self::assertSame($email, $result->getPersonne()->getMail());
        self::assertCount(1, $result->getOptions());
        self::assertSame(2, $result->getOptions()[0]->getNbAttribut());
        // Couverture OPTION.type sur findDevisFormById() : l'option
        // jointe doit porter l'OptionTypeEnum réellement persisté
        // (et pas celui, différent, du DEVIS_FORM lui-même).
        self::assertSame(OptionTypeEnum::commerce, $result->getOptions()[0]->getType());
    }

    /**
     * Objectif : valider spécifiquement le pipeline d'hydratation du
     * type d'option au travers du LEFT JOIN `OPTION` de
     * findDevisFormById() :
     *     OPTION.type -> LEFT JOIN -> OptionTypeEnum::tryFrom() -> OptionEntity
     * pour chacune des quatre valeurs de l'enum, sur un seul devis
     * portant une option de chaque type.
     *
     * Risque couvert : confusion entre l'alias `o.type AS typeOption`
     * (utilisé pour l'option jointe) et la colonne `df.type` du devis
     * lui-même (ramenée sans alias par `df.*`), qui pourrait faire lire
     * par erreur le type du devis à la place de celui de l'option, ou
     * inversement - ce qui ne serait pas détecté si le devis et son
     * unique option partageaient la même valeur de type.
     */
    public function testFindDevisFormByIdHydratesOptionTypeForEachOptionTypeEnumValue(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('byid-devis-types'));
        // Le DEVIS_FORM porte un type volontairement différent de toutes
        // les options, pour détecter une confusion entre df.type et
        // o.type AS typeOption.
        $idDevis = $this->insertRawDevisForm($email, type: DevisFormTypeEnum::webApp, nomProjet: 'Projet multi-types');

        $expectedTypesByOptionId = [];
        foreach (OptionTypeEnum::cases() as $type) {
            $idOption = $this->insertRawOption($this->uniqueOptionNom('byid-types'), 1.0, $type);
            $this->insertRawDevisOption($idDevis, $idOption, null);
            $expectedTypesByOptionId[$idOption] = $type;
        }

        $result = $this->dbManager->findDevisFormById($idDevis);

        self::assertNotNull($result);
        self::assertSame(DevisFormTypeEnum::webApp, $result->getType());
        self::assertCount(4, $result->getOptions());

        foreach ($result->getOptions() as $option) {
            self::assertArrayHasKey(
                $option->getIdOption(),
                $expectedTypesByOptionId,
                'Option inattendue renvoyée pour ce devis.'
            );
            self::assertSame(
                $expectedTypesByOptionId[$option->getIdOption()],
                $option->getType(),
                sprintf("Type incorrect hydraté pour l'option id:%d.", $option->getIdOption())
            );
        }
    }

    /**
     * Objectif : vérifier qu'une recherche sur un identifiant inexistant
     * renvoie null sans lever d'exception.
     */
    public function testFindDevisFormByIdNonExistentIdReturnsNull(): void
    {
        $result = $this->dbManager->findDevisFormById($this->nextNonExistentId('DEVIS_FORM', 'idDevis'));

        self::assertNull($result);
    }

    #endregion

    /**
     * @param DevisFormEntity[] $devisList
     */
    private function findDevisInArrayById(array $devisList, int $idDevis): ?DevisFormEntity
    {
        foreach ($devisList as $devis) {
            if ($devis->getIdDevis() === $idDevis) {
                return $devis;
            }
        }

        return null;
    }
}
