<?php

namespace App\BDD\entity;

use InvalidArgumentException;

/**
 * Représente l'entité Personne de la base de donnée, qui
 * stocke les informations des personnes (physique et morale)
 *  ayant utilisé au moins une fois soit le formulaire de
 * contact soit le formulaire devis.
 */
class PersonneEntity
{
    #region Properties
    private const NOMPRENOM_LENGTH = 50;
    private const MAIL_LENGTH = 100;
    private const TEL_LENGTH = 15;

    private string $mail;           // clé primaire / VARCHAR (100)
    private string $nom;            // VARCHAR (50)
    private string|null $prenom;    // VARCHAR (50)
    private string|null $tel;       // stocké sous le format +33 en base / VARCHAR (15)

    private bool $estMorale;        // bool (tinyInt(1))

    #endregion

    #region Constructor

    /**
     * Instancie un objet de type Personne avec un email (clé primaire),
     * un nom (de famille ou de société), un prénom, un numéro de téléphone,
     * et un booléen pour savoir si la personne est une personne physique ou
     * morale
     * @param string $mail email de la personne
     * @param string $nom nom de la personne
     * @param string|null $prenom prénom de la personne
     * @param string|null $tel numéro de téléphone de la personne
     * @param bool $estMorale vrai si c'est une personne morale, faux si physique
     * @throws InvalidArgumentException Si l'email est invalide, si le numéro de téléphone
     *  est invalide, si le prénom est invalide(>50 char) ou si le nom est invalide(>50 char ou vide)
     */
    public function __construct(string $mail,
                                string $nom,
                                ?string $prenom,
                                ?string $tel,
                                bool $estMorale)
    {
        // test des différentes valeurs
        self::assertEmailIsValid($mail);
        self::assertNomIsValid($nom);
        self::assertPrenomIsValid($prenom);

        $this->tel = self::normalizeAndAssertTelIsValid($tel);
        $this->mail = $mail;
        $this->estMorale = $estMorale;
        $this->nom = $nom;
        $this->prenom = $prenom;
    }

    #endregion

    #region Getters and Setters

    /**
     * Récupère l'email de la personne
     * @return string l'email de la personne
     */
    public function getMail(): string
    {
        return $this->mail;
    }

    /**
     * Définit l'email de la personne
     * @param string $mail l'email de la personne
     * @throws InvalidArgumentException Si l'email est invalide
     */
    public function setMail(string $mail): void
    {
        self::assertEmailIsValid($mail);
        $this->mail = $mail;
    }

    /**
     * Récupère le nom de la personne
     * @return string le nom de la personne
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Définit le nom de la personne
     * @param string $nom le nom de la personne
     */
    public function setNom(string $nom): void
    {
        self::assertNomIsValid($nom);
        $this->nom = $nom;
    }

    /**
     * Récupère le prénom de la personne
     * @return string|null le prénom de la personne
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * Définit le prénom de la personne
     * @param string|null $prenom le prénom de la personne
     * @throws InvalidArgumentException si le prénom est supérieur à 50 caractères
     */
    public function setPrenom(?string $prenom): void
    {
        self::assertPrenomIsValid($prenom);
        $this->prenom = $prenom;
    }

    /**
     * Récupère le numéro de téléphone de la personne
     * @return string|null le numéro de téléphone de la personne
     */
    public function getTel(): ?string
    {
        $tel = $this->tel;
        if ($tel !== null) {
            $tel = '0' . substr($tel, 3);
            $tel = chunk_split($tel, 2, ' ');
            $tel = trim($tel);
        }
        return $tel;
    }

    /**
     * Définit le numéro de téléphone de la personne
     * @param string|null $tel le numéro de téléphone de la personne
     * @throws InvalidArgumentException Si le numéro est invalide
     */
    public function setTel(?string $tel): void
    {
        $this->tel = $this->normalizeAndAssertTelIsValid($tel);
    }

    /**
     * Retourne si la personne est morale ou physique
     * @return bool vrai pour morale, faux pour physique
     */
    public function isEstMorale(): bool
    {
        return $this->estMorale;
    }

    /**
     * Définit si la personne est morale ou physique
     * @param bool $estMorale vrai pour morale, faux pour physique
     */
    public function setEstMorale(bool $estMorale): void
    {
        $this->estMorale = $estMorale;
    }

    #endregion

    #region PRIVATE METHOD

    /**
     * Convertit le num de tel en +33 sans espace et vérifie s'il est valide.
     * Si le num est null, renvoi null
     * @param string|null $tel le numéro de téléphone
     * @return string|null le num normalisé ou null si le num est null
     */
    private static function normalizeAndAssertTelIsValid(?string $tel): ?string {
        if (!is_null($tel)) {
            $tel = str_replace(' ', '', $tel);
            // Test si le numéro de téléphone est valide
            if (!preg_match('/^(0|\+33)[1-9](\d{8})$/', $tel)
                || mb_strlen($tel, 'UTF-8') > PersonneEntity::TEL_LENGTH) {
                throw new InvalidArgumentException('Numéro de téléphone invalide');
            } // Passe le numéro de téléphone en version +33
            elseif (str_starts_with($tel, '0')) {
                $tel = '+33' . substr($tel, 1);
            }
        }
        return $tel;
    }

    private static function assertEmailIsValid(string $mail): void {
        // Test si l'email est valide
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)
            || mb_strlen($mail, 'UTF-8') > PersonneEntity::MAIL_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                "email invalide, vide ou supérieur à %d caractères.",
                self::MAIL_LENGTH
            ));
        }
    }

    private static function assertPrenomIsValid(?string $prenom): void {
        // Test si le prenom est valide
        if (!is_null($prenom)
            && mb_strlen($prenom, 'UTF-8') > PersonneEntity::NOMPRENOM_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                "prénom invalide, supérieur à %d caractères.",
                self::NOMPRENOM_LENGTH
            ));
        }
    }

    private static function assertNomIsValid(string $nom): void {
        // Test si le prenom est valide
        if (trim($nom) === ''
                || mb_strlen($nom, 'UTF-8') > PersonneEntity::NOMPRENOM_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                "nom invalide, supérieur à %d caractères.",
                self::NOMPRENOM_LENGTH
            ));
        }
    }

    #endregion

}
