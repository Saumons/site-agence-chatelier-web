<?php

namespace App\BDD\entity;
use App\enums\DevisFormStatutEnum;
use \App\enums\DevisFormTypeEnum;
use InvalidArgumentException;

/**
 * Représente l'entité DevisForm de la base de donnée, qui
 * stocke les résultats du formulaire de contact
 *
 * Lié à PersonneEntity et contient une liste
 * d'OptionEntity
 */
class DevisFormEntity
{
    #region Properties
    private const NOMPROJ_LENGTH = 50;
    private int|null $idDevis;                  // généré automatiquement en base / int
    private DevisFormTypeEnum $type;            // enum
    private string|null $nomProjet;             // varchar(50)
    private string $descriptionProjet;          // text
    private DevisFormStatutEnum $statut;        // enum
    private string|null $infoComplementaire;    // text

    private PersonneEntity $personne;
    private array $options;


    #endregion

    #region Constructor

    /**
     * Instancie un objet de type DevisForm avec un identifiant (optionnelle),
     *  un type(énum), un nom de projet (optionnels), un statut(énum), des informations
     *  complémentaires (optionnelles) et l'id de la personne associée (optionnelle)
     * @param DevisFormTypeEnum $type type du devis ('webApp', 'commerce', 'vitrine')
     * @param string|null $nomProjet nom du projet lié au devis
     * @param string $descriptionProjet description du projet
     * @param DevisFormStatutEnum $statut status du devis ('À traiter','En cours','Accepté','Annulé')
     * @param string|null $infoComplementaire infos complémentaires du devis
     * @param PersonneEntity $personne personne associé (null si laissé vide)
     * @param int|null $idDevis identifiant du devis (null si laissé vide)
     * @param OptionEntity[]|null $options options séléctionné dans le devis (null si laissé vide)
     * @throws InvalidArgumentException si la description est vide ou que le nom projet est > 50 char
     */
    public function __construct(DevisFormTypeEnum $type,
                                ?string $nomProjet,
                                string $descriptionProjet,
                                DevisFormStatutEnum $statut,
                                ?string $infoComplementaire,
                                PersonneEntity $personne,
                                ?int $idDevis = null,
                                ?array $options = null)
    {
        self::assertDescriptionProjetIsValid($descriptionProjet);
        self::assertNomProjetIsValid($nomProjet);

        $this->idDevis = $idDevis;
        $this->type = $type;
        $this->nomProjet = $nomProjet;
        $this->descriptionProjet = $descriptionProjet;
        $this->statut = $statut;
        $this->infoComplementaire = $infoComplementaire;
        $this->personne = $personne;
        if ($options === null) {
            $this->options = [];
        } else {
            $this->options = $options;
        }
    }

    #endregion

    #region Getters and Setters

    /**
     * Récupère l'identifiant du devis ou null
     * si pas encore en base
     * @return int|null l'identifiant du devis
     */
    public function getIdDevis(): ?int
    {
        return $this->idDevis;
    }

    /**
     * Récupère le type du devis
     * @return DevisFormTypeEnum le type du devis
     */
    public function getType(): DevisFormTypeEnum
    {
        return $this->type;
    }

    /**
     * Définit le type du devis
     * @param DevisFormTypeEnum $type le type du devis ('webApp','commerce','vitrine') de type enum
     */
    public function setType(DevisFormTypeEnum $type): void
    {
        $this->type = $type;
    }

    /**
     * Récupère le nom du projet lié au devis
     * @return string|null le nom du projet lié au devis
     */
    public function getNomProjet(): ?string
    {
        return $this->nomProjet;
    }

    /**
     * Définit le nom du devis
     * @param string|null $nomProjet le nom du projet lié au devis
     * @throws InvalidArgumentException si le nom projet est > 50 char
     */
    public function setNomProjet(?string $nomProjet): void
    {
        self::assertNomProjetIsValid($nomProjet);
        $this->nomProjet = $nomProjet;
    }

    /**
     * Récupère le status du devis
     * @return DevisFormStatutEnum le status du devis
     */
    public function getStatut(): DevisFormStatutEnum
    {
        return $this->statut;
    }

    /**
     * Définit le statut du devis
     * @param DevisFormStatutEnum $statut statut du devis ('À traiter','En cours','Accepté','Annulé') de type enum
     */
    public function setStatut(DevisFormStatutEnum $statut): void
    {
        $this->statut = $statut;
    }

    /**
     * Récupère les informations complémentaires du devis
     * @return string|null les informations complémentaires du devis
     */
    public function getInfoComplementaire(): ?string
    {
        return $this->infoComplementaire;
    }

    /**
     * Définit les informations complémentaires du devis
     * @param string|null $infoComplementaire les informations complémentaires du devis
     */
    public function setInfoComplementaire(?string $infoComplementaire): void
    {
        $this->infoComplementaire = $infoComplementaire;
    }

    /**
     * Récupère la personne liée au devis
     * @return PersonneEntity la personne liée au devis
     */
    public function getPersonne(): PersonneEntity
    {
        return $this->personne;
    }

    /**
     * Définit la personne liée au devis
     * @param PersonneEntity $personne la personne liée au devis
     */
    public function setPersonne(PersonneEntity $personne): void
    {
        $this->personne = $personne;
    }

    /**
     * Récupère la liste des options choisies dans le devis
     * @return OptionEntity[] la liste des options choisies dans le devis
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Rajoute une option dans la liste des options choisies
     * dans le devis
     * @param OptionEntity $option l'option à rajouté
     */
    public function addOption(OptionEntity $option): void
    {
        $this->options[] = $option;
    }

    /**
     * Enlève une option dans la liste des options choisies
     * dans le devis
     * @param OptionEntity $option l'option à enlever
     * @throws InvalidArgumentException s'il n'y a aucune option ou que l'option n'existe dans la propriété options
     */
    public function removeOption(OptionEntity $option): void
    {
        foreach ($this->options as $key => $currentOption) {
            if ($currentOption->getIdOption() === $option->getIdOption()) {
                unset($this->options[$key]);
                $this->options = array_values($this->options);
                return;
            }
        }

        throw new InvalidArgumentException(
            "Cette option n'existe pas dans options"
        );
    }

    /**
     * Récupère la description du projet liée au devis
     * @return string description du projet liée au devis
     */
    public function getDescriptionProjet(): string
    {
        return $this->descriptionProjet;
    }

    /**
     * Définit la description du projet liée au devis
     * @param string $descriptionProjet la description du projet liée au devis
     * @throws InvalidArgumentException si la description est vide
     */
    public function setDescriptionProjet(string $descriptionProjet): void
    {
        self::assertDescriptionProjetIsValid($descriptionProjet);
        $this->descriptionProjet = $descriptionProjet;
    }

    #endregion

    #region PRIVATE METHOD

    private static function assertDescriptionProjetIsValid(string $descriptionProjet): void {
        // Test si la description est valide (non vide)
        if (trim($descriptionProjet) === '') {
            throw new InvalidArgumentException("description invalide, est vide");
        }
    }

    private static function assertNomProjetIsValid(?string $nomProjet): void {
        // Test si le nom est valide (varchar(50))
        if (!is_null($nomProjet)
                && mb_strlen($nomProjet, 'UTF-8') > self::NOMPROJ_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                "nom du projet invalide, supérieur à %d caractères.",
                self::NOMPROJ_LENGTH
            ));
        }
    }

    #endregion

}
