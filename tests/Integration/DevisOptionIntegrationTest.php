<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\BDD\DbManager;
use App\BDD\entity\OptionEntity;
use App\BDD\exception\DatabaseWriteException;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests d'intégration de DbManager::insertDevisOption().
 *
 * Rappel schéma : DEVIS_OPTION(idDevis SMALLINT, idOption SMALLINT,
 * compteur INT NULL, PRIMARY KEY (idDevis, idOption),
 * FK idOption -> OPTION(idOption), FK idDevis -> DEVIS_FORM(idDevis)).
 *
 * Il n'existe pas de méthode SELECT dédiée à DEVIS_OPTION dans
 * DbManager : la table n'est consultable qu'indirectement via les
 * LEFT JOIN de findAllDevisForm()/findDevisFormById(), déjà couverts
 * par DevisFormIntegrationTest. Ce fichier se concentre donc uniquement
 * sur l'écriture.
 *
 * insertDevisOption() n'ouvre pas de transaction propre (une seule
 * instruction SQL) : la catégorie "transactions" ne s'applique pas ici
 * en tant que telle (le cas d'usage transactionnel - l'appel depuis la
 * boucle d'insertDevisForm() - est couvert par
 * DevisFormIntegrationTest::testInsertDevisFormLeavesTransactionOpenWhenOptionInsertionFails()).
 */
#[CoversClass(DbManager::class)]
#[Group('integration')]
final class DevisOptionIntegrationTest extends AbstractDatabaseIntegrationTestCase
{
    #region cas nominal / valeurs nullables

    /**
     * Objectif : vérifier l'insertion du lien devis/option, avec un
     * compteur renseigné puis avec un compteur null
     * (DEVIS_OPTION.compteur est `int DEFAULT NULL`).
     *
     * Utilité : seule colonne nullable de la table, le "compteur"
     * (nombre de pages/attributs facturés pour cette option sur ce
     * devis) doit être fidèlement propagé, y compris à null.
     *
     * Risque couvert : mauvais binding du paramètre nullable `compteur`.
     */
    #[DataProvider('compteurProvider')]
    public function testInsertDevisOptionNominalPersistsRow(?int $compteur): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('do-nominal'));
        $idDevis = $this->insertRawDevisForm($email);
        $idOption = $this->insertRawOption($this->uniqueOptionNom('do-nominal'), 1.0);

        $this->dbManager->insertDevisOption(
            $idDevis,
            new OptionEntity(nom: 'ignoré', nbJourHomme: 1.0, nbAttribut: $compteur, idOption: $idOption)
        );

        $stmt = $this->pdo->prepare('SELECT compteur FROM `DEVIS_OPTION` WHERE idDevis = :d AND idOption = :o');
        $stmt->execute(['d' => $idDevis, 'o' => $idOption]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        self::assertNotFalse($row);
        if ($compteur === null) {
            self::assertNull($row['compteur']);
        } else {
            self::assertSame($compteur, (int) $row['compteur']);
        }
    }

    public static function compteurProvider(): array
    {
        return [
            'compteur renseigné' => [7],
            'compteur null' => [null],
        ];
    }

    #endregion

    #region contrainte de clé primaire composite

    /**
     * Objectif : vérifier qu'un second insertDevisOption() pour le même
     * couple (idDevis, idOption) - clé primaire composite de
     * DEVIS_OPTION - échoue avec une DatabaseWriteException, sans créer
     * de ligne supplémentaire.
     *
     * Risque couvert : sélection en double de la même option sur un même
     * devis, qui fausserait le comptage (nbJourHomme facturé) côté
     * métier si elle n'était pas bloquée.
     */
    public function testInsertDevisOptionWithDuplicateCompositeKeyThrowsDatabaseWriteException(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('do-dup'));
        $idDevis = $this->insertRawDevisForm($email);
        $idOption = $this->insertRawOption($this->uniqueOptionNom('do-dup'), 1.0);

        $option = new OptionEntity(nom: 'ignoré', nbJourHomme: 1.0, idOption: $idOption);

        $this->dbManager->insertDevisOption($idDevis, $option);

        $this->expectException(DatabaseWriteException::class);

        try {
            $this->dbManager->insertDevisOption($idDevis, $option);
        } finally {
            $count = $this->countRows(
                'DEVIS_OPTION',
                'idDevis = :d AND idOption = :o',
                ['d' => $idDevis, 'o' => $idOption]
            );
            self::assertSame(1, $count);
        }
    }

    #endregion

    #region clés étrangères manquantes

    /**
     * Objectif : vérifier qu'insertDevisOption() échoue avec une
     * DatabaseWriteException lorsque le devis référencé n'existe pas
     * (violation de la contrainte FK DEVIS_OPTION_ibfk_2).
     *
     * Utilité : à la différence d'insertContactForm()/insertDevisForm(),
     * insertDevisOption() est appelable directement et ne garantit
     * elle-même l'existence d'aucune des deux clés étrangères : ce test
     * exerce donc une contrainte réellement atteignable par l'API
     * publique.
     */
    public function testInsertDevisOptionWithNonExistentDevisThrowsDatabaseWriteException(): void
    {
        $idOption = $this->insertRawOption($this->uniqueOptionNom('do-fk-devis'), 1.0);
        $nonExistentIdDevis = $this->nextNonExistentId('DEVIS_FORM', 'idDevis');

        $this->expectException(DatabaseWriteException::class);

        try {
            $this->dbManager->insertDevisOption(
                $nonExistentIdDevis,
                new OptionEntity(nom: 'ignoré', nbJourHomme: 1.0, idOption: $idOption)
            );
        } finally {
            $count = $this->countRows(
                'DEVIS_OPTION',
                'idDevis = :d AND idOption = :o',
                ['d' => $nonExistentIdDevis, 'o' => $idOption]
            );
            self::assertSame(0, $count);
        }
    }

    /**
     * Objectif : vérifier qu'insertDevisOption() échoue avec une
     * DatabaseWriteException lorsque l'option référencée n'existe pas
     * (violation de la contrainte FK DEVIS_OPTION_ibfk_1).
     */
    public function testInsertDevisOptionWithNonExistentOptionThrowsDatabaseWriteException(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('do-fk-option'));
        $idDevis = $this->insertRawDevisForm($email);
        $nonExistentIdOption = $this->nextNonExistentId('OPTION', 'idOption');

        $this->expectException(DatabaseWriteException::class);

        try {
            $this->dbManager->insertDevisOption(
                $idDevis,
                new OptionEntity(nom: 'ignoré', nbJourHomme: 1.0, idOption: $nonExistentIdOption)
            );
        } finally {
            $count = $this->countRows(
                'DEVIS_OPTION',
                'idDevis = :d AND idOption = :o',
                ['d' => $idDevis, 'o' => $nonExistentIdOption]
            );
            self::assertSame(0, $count);
        }
    }

    #endregion

    #region cas limite : dépassement de la plage SMALLINT

    /**
     * Objectif : vérifier le comportement lorsque idOption dépasse la
     * plage de la colonne `smallint` (-32768 à 32767), ce qu'OptionEntity
     * ne valide absolument pas (aucune contrainte de plage sur idOption
     * côté entité : seuls nom et nbJourHomme sont validés).
     *
     * Hypothèse d'environnement : ce test suppose que le sql_mode du
     * serveur inclut STRICT_TRANS_TABLES (comportement par défaut de
     * MariaDB >= 10.2), sans quoi le serveur tronquerait silencieusement
     * la valeur à 32767 au lieu de lever une erreur. Si cette hypothèse
     * ne tient pas sur l'environnement de CI, configurer explicitement
     * le sql_mode du service MariaDB en conséquence.
     *
     * Risque couvert : absence de validation de plage côté entité pour
     * idOption, qui pourrait produire en production une erreur SQL mal
     * comprise (confondue avec une simple option inexistante) plutôt
     * qu'une erreur de saisie explicite en amont.
     */
    public function testInsertDevisOptionWithIdOptionOutOfSmallIntRangeThrowsDatabaseWriteException(): void
    {
        $email = $this->insertRawPersonne(email: $this->uniqueEmail('do-overflow'));
        $idDevis = $this->insertRawDevisForm($email);

        $this->expectException(DatabaseWriteException::class);

        $this->dbManager->insertDevisOption(
            $idDevis,
            new OptionEntity(nom: 'ignoré', nbJourHomme: 1.0, idOption: 99999)
        );
    }

    #endregion
}
