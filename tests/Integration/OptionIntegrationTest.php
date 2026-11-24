<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\BDD\DbManager;
use App\BDD\entity\OptionEntity;
use App\BDD\exception\DatabaseWriteException;
use App\enums\OptionTypeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests d'intégration de DbManager pour la table OPTION :
 * - insertOption()
 * - findAllOption()
 * - findOptionById()
 *
 * Rappel schéma : OPTION(idOption SMALLINT PK AUTO_INCREMENT,
 * nom VARCHAR(50) NOT NULL UNIQUE, nbJourHomme DECIMAL(15,2) NOT NULL).
 *
 * - Aucune colonne nullable dans OPTION : la catégorie "valeurs nullables"
 *   ne s'applique pas à insertOption() et n'est donc pas testée ici.
 * - OptionEntity::$nbAttribut n'a PAS de colonne correspondante dans la
 *   table OPTION (il correspond à DEVIS_OPTION.compteur) : insertOption()
 *   ne le persiste jamais, ce qui est cohérent avec le SQL de la méthode.
 * - La validité de nbJourHomme (positif, DECIMAL(13,2)) est déjà garantie
 *   par OptionEntity::__construct() et testée au niveau unitaire : elle
 *   n'est pas reproduite ici.
 */
#[CoversClass(DbManager::class)]
#[Group('integration')]
final class OptionIntegrationTest extends AbstractDatabaseIntegrationTestCase
{
    #region insertOption() - cas nominal / bornes de précision

    /**
     * Objectif : vérifier qu'un insert réussi crée bien une ligne OPTION
     * avec le nom et le nbJourHomme attendus, pour une valeur basse et une
     * valeur haute compatibles avec DECIMAL(15,2).
     *
     * Utilité : OptionEntity garantit déjà qu'une valeur hors bornes lève
     * une InvalidArgumentException (couvert par les tests unitaires de
     * l'entité). Ce test-ci vérifie en revanche que les valeurs
     * acceptées par l'entité sont bien stockées par MariaDB SANS perte
     * de précision ni troncature via le binding PDO - ce qu'aucun test
     * unitaire ne peut garantir.
     *
     * Note de conception : la borne haute choisie (11 chiffres entiers +
     * 2 décimales = 13 chiffres significatifs) est volontairement
     * inférieure à la borne absolue théorique de l'entité (13 chiffres
     * entiers + 2 décimales = 15 chiffres significatifs). Au-delà de 14
     * chiffres significatifs, la conversion PHP float -> chaîne utilisée
     * par les requêtes préparées émulées (comportement par défaut de PDO,
     * non modifié par DbConnection) dépend du réglage ini `precision`
     * (14 par défaut) : un test à la borne absolue serait donc dépendant
     * de la configuration PHP de l'environnement d'exécution plutôt que
     * du code testé. La valeur retenue reste une borne haute réaliste et
     * fiable.
     *
     * Risque couvert : troncature/arrondi silencieux d'un DECIMAL(15,2)
     * lors du binding PDO.
     */
    #[DataProvider('nbJourHommeBoundaryProvider')]
    public function testInsertOptionNominalPersistsNbJourHommeAtBoundaries(float $nbJourHomme): void
    {
        $nom = $this->uniqueOptionNom('boundary');
        $option = new OptionEntity(nom: $nom, nbJourHomme: $nbJourHomme, type: OptionTypeEnum::commerce);

        $this->dbManager->insertOption($option);
        $idOption = (int) $this->pdo->lastInsertId();
        $this->trackOptionId($idOption);

        $row = $this->fetchOptionRowByNom($nom);

        self::assertNotFalse($row);
        self::assertSame($nom, $row['nom']);
        self::assertEqualsWithDelta($nbJourHomme, (float) $row['nbJourHomme'], 0.001);
        // Couverture de la nouvelle colonne OPTION.type : persistance brute...
        self::assertSame(OptionTypeEnum::commerce->value, $row['type']);

        // ...et ré-hydratation correcte via DbManager (OptionTypeEnum::tryFrom()).
        $hydrated = $this->dbManager->findOptionById($idOption);
        self::assertNotNull($hydrated);
        self::assertSame(OptionTypeEnum::commerce, $hydrated->getType());
    }

    public static function nbJourHommeBoundaryProvider(): array
    {
        return [
            'borne basse (0.01)' => [0.01],
            'borne haute réaliste (99999999999.99)' => [99999999999.99],
        ];
    }

    #endregion


    #region OPTION.type - couverture dédiée du nouvel enum OptionTypeEnum

    /**
     * Objectif : valider, pour chacune des quatre valeurs de
     * OptionTypeEnum, le mapping complet :
     *     Enum PHP -> INSERT SQL -> Lecture SQL -> Enum PHP
     * à la fois via insertOption()/findOptionById() (DbManager) et via
     * une lecture SQL brute (fetchOptionRowByNom()), pour détecter aussi
     * bien une erreur de binding à l'écriture qu'une erreur de mapping
     * de colonne à la lecture.
     *
     * Risque couvert : une des quatre valeurs de l'enum (en particulier
     * OptionTypeEnum::trans, dont la valeur SQL 'transversal' diffère du
     * nom du cas PHP) mal transmise au binding PDO, ou mal retrouvée par
     * OptionTypeEnum::tryFrom() lors de la lecture.
     */
    #[DataProvider('optionTypeEnumProvider')]
    public function testInsertOptionAndFindOptionByIdRoundTripsAllOptionTypeEnumValues(
        OptionTypeEnum $type
    ): void {
        $nom = $this->uniqueOptionNom('enum');
        $this->dbManager->insertOption(new OptionEntity(nom: $nom, nbJourHomme: 1.0, type: $type));
        $idOption = (int) $this->pdo->lastInsertId();
        $this->trackOptionId($idOption);

        $row = $this->fetchOptionRowByNom($nom);
        self::assertNotFalse($row);
        self::assertSame($type->value, $row['type']);

        $result = $this->dbManager->findOptionById($idOption);
        self::assertInstanceOf(OptionEntity::class, $result);
        self::assertSame($type, $result->getType());
    }

    public static function optionTypeEnumProvider(): array
    {
        return [
            'webApp' => [OptionTypeEnum::webApp],
            'commerce' => [OptionTypeEnum::commerce],
            'vitrine' => [OptionTypeEnum::vitrine],
            'transversal (trans)' => [OptionTypeEnum::trans],
        ];
    }

    #endregion

    #region findAllOption() / findOptionById()

    /**
     * Objectif : vérifier que findAllOption() renvoie, parmi les options
     * déjà en base (catalogue de démonstration inclus), une entité
     * correspondant exactement à l'option insérée par le test.
     */
    public function testFindAllOptionContainsInsertedRow(): void
    {
        $nom = $this->uniqueOptionNom('findall');
        $idOption = $this->insertRawOption($nom, 3.25, OptionTypeEnum::commerce);

        $options = $this->dbManager->findAllOption();

        $match = null;
        foreach ($options as $option) {
            if ($option->getIdOption() === $idOption) {
                $match = $option;
                break;
            }
        }

        self::assertNotNull($match, "L'option insérée doit être présente dans findAllOption().");
        self::assertSame($nom, $match->getNom());
        self::assertEqualsWithDelta(3.25, $match->getNbJourHomme(), 0.001);
        self::assertSame(OptionTypeEnum::commerce, $match->getType());
    }

    /**
     * Objectif : vérifier que findOptionById() hydrate correctement une
     * ligne existante (identifiant, nom, nbJourHomme).
     */
    public function testFindOptionByIdExistingIdReturnsMatchingEntity(): void
    {
        $nom = $this->uniqueOptionNom('byid');
        $idOption = $this->insertRawOption($nom, 1.75, OptionTypeEnum::trans);

        $result = $this->dbManager->findOptionById($idOption);

        self::assertInstanceOf(OptionEntity::class, $result);
        self::assertSame($idOption, $result->getIdOption());
        self::assertSame($nom, $result->getNom());
        self::assertEqualsWithDelta(1.75, $result->getNbJourHomme(), 0.001);
        self::assertSame(OptionTypeEnum::trans, $result->getType());
    }

    /**
     * Objectif : vérifier qu'une recherche sur un identifiant inexistant
     * renvoie null sans lever d'exception.
     */
    public function testFindOptionByIdNonExistentIdReturnsNull(): void
    {
        $result = $this->dbManager->findOptionById($this->nextNonExistentId('OPTION', 'idOption'));

        self::assertNull($result);
    }

    #endregion
}
