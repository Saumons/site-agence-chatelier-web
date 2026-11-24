<?php

namespace App\BDD\entity;

use App\enums\OptionTypeEnum;
use InvalidArgumentException;

/**
 * Représente l'entité Option de la base de donnée, qui
 * stocke les informations des options possibles dans
 * le formulaire de devis ainsi que le nombre de jours
 * homme facturé pour celle-ci
 */
class OptionEntity
{
    #region Properties
    private const NOM_LENGTH = 50;
    private const NBJOUR_LENGTH = 15;
    private const NBJOUR_DECI = 2;
    private int|null $idOption;     // généré automatiquement en base / int
    private string $nom;            // varchar(50)
    private float $nbJourHomme;     // decimal(15,2)
    private int|null $nbAttribut;   // représente le nombre de pages/attribut sur lequel l'option porte / int
    private OptionTypeEnum $type;   // enum('webApp','commerce','vitrine','transversal')
    #endregion

    #region Constructor

    /**
     * Instancie un objet de type Option avec un identifiant (optionnelle),
     *  un nom, un nombre de jours homme et un nombre d'attributs (représente
     *  le nombre de pages/attribut sur lequel l'option porte)
     * @param int|null $idOption identifiant de l'option (null si laissé vide)
     * @param string $nom nom de l'option
     * @param int|null $nbAttribut nombre d'attributs
     * @param float $nbJourHomme nombre de jours homme facturé pour l'option
     * @param OptionTypeEnum $type type sur lequel l'option se porte
     * @throws InvalidArgumentException si le nombre de jour homme n'est pas un décimal (15,2)
     *  positif, que le nom est vide ou supperieur à 50 caractères
     */
    public function __construct(string         $nom,
                                float          $nbJourHomme,
                                OptionTypeEnum $type,
                                ?int           $nbAttribut = null,
                                ?int           $idOption = null)
    {
        // test des différentes valeurs
        self::assertNbJourHommeIsValid($nbJourHomme);
        self::assertNomIsValid($nom);

        $this->idOption = $idOption;
        $this->nom = $nom;
        $this->nbJourHomme = $nbJourHomme;
        $this->nbAttribut = $nbAttribut;
        $this->type = $type;
    }

    #endregion

    #region Getters and Setters

    /**
     * Récupère l'identifiant de l'option ou null
     *  si pas encore en base
     * @return int|null l'identifiant de l'option
     */
    public function getIdOption(): ?int
    {
        return $this->idOption;
    }

    /**
     * Récupère le nom de l'option
     * @return string le nom de l'option
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Définit le nom de l'option
     * @param string $nom le nom de l'option
     * @throws InvalidArgumentException si le nom est vide
     */
    public function setNom(string $nom): void
    {
        self::assertNomIsValid($nom);
        $this->nom = $nom;
    }

    /**
     * Récupère le nombre de jours hommes facturés
     * pour l'option
     * @return float le nombre de jours hommes facturés
     *  pour l'option
     */
    public function getNbJourHomme(): float
    {
        return $this->nbJourHomme;
    }

    /**
     * Définit le nombre de jours hommes facturés
     *  pour l'option
     * @param float $nbJourHomme le nombre de jours hommes facturés
     *  pour l'option
     * @throws InvalidArgumentException si le nombre de jour homme est inferieur à 0
     */
    public function setNbJourHomme(float $nbJourHomme): void
    {
        // Test si nbJourHomme est positif et rentre dans un decimal(15,2)
        self::assertNbJourHommeIsValid($nbJourHomme);
        $this->nbJourHomme = $nbJourHomme;
    }

    /**
     * Récupère le nombre d'attributs de l'option
     * @return int|null nombre de pages/attribut sur lequel l'option porte
     */
    public function getNbAttribut(): ?int
    {
        return $this->nbAttribut;
    }

    /**
     * Définit le nombre d'attributs de l'option
     * @param int|null $nbAttribut nombre de pages/attribut sur lequel l'option porte
     */
    public function setNbAttribut(?int $nbAttribut): void
    {
        $this->nbAttribut = $nbAttribut;
    }

    /**
     * Récupère le type de l'option
     * @return OptionTypeEnum Le type de l'option ('webApp','commerce','vitrine','transversal') de type enum
     */
    public function getType(): OptionTypeEnum
    {
        return $this->type;
    }

    /**
     * Définit le type de l'option
     * @param OptionTypeEnum $type Le type de l'option ('webApp','commerce','vitrine','transversal') de type enum
     */
    public function setType(OptionTypeEnum $type): void
    {
        $this->type = $type;
    }

    #endregion

    #region PRIVATE METHOD

    /**
     * Vérifie qu'une valeur est compatible avec un DECIMAL(x,y).
     *
     * @param int $avantVirgule Nombre maximum de chiffres avant la virgule
     * @param int $apresVirgule Nombre maximum de chiffres après la virgule
     * @param float $deci Valeur à tester
     */
    private static function assertIsDecimal(
        int   $avantVirgule,
        int   $apresVirgule,
        float $deci
    ): bool
    {
        $maximum = pow(10, $avantVirgule) - pow(10, -$apresVirgule);

        return abs($deci) <= $maximum
            && abs($deci - round($deci, $apresVirgule)) < PHP_FLOAT_EPSILON;
    }

    private static function assertNbJourHommeIsValid(float $nbJourHomme): void
    {
        // Test si nbJourHomme est positif et rentre dans un decimal(15,2)
        if ($nbJourHomme <= 0
            || !self::assertIsDecimal(OptionEntity::NBJOUR_LENGTH - OptionEntity::NBJOUR_DECI,
                OptionEntity::NBJOUR_DECI,
                $nbJourHomme)) {
            throw new InvalidArgumentException(sprintf(
                "nombre jour homme invalide, est négatif, null ou à plus de %d chiffres avant la virgule ou %s après",
                OptionEntity::NBJOUR_LENGTH - OptionEntity::NBJOUR_DECI,
                OptionEntity::NBJOUR_DECI
            ));
        }
    }

    private static function assertNomIsValid(string $nom): void
    {
        // Test si le nom n'est pas vide est qu'il rentre en varchar(50)
        if (trim($nom) === ''
            || mb_strlen($nom, 'UTF-8') > OptionEntity::NOM_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                "nom invalide, vide ou supérieur à %d caractères.",
                self::NOM_LENGTH
            ));
        }

    }

    #endregion
}
