<?php

namespace App\BDD\entity;

use InvalidArgumentException;

/**
 * Représente l'entité ContactForm de la base de donnée, qui
 * stocke les résultats du formulaire de contact
 *
 * Lié à PersonneEntity
 */
class ContactFormEntity
{
    #region Properties
    private const OBJ_LENGHT = 75;

    private int|null $idContact;            // généré automatiquement en base / int
    private string $objet;                  // varchar(75)
    private string $message;                // text
    private PersonneEntity $personne;

    #endregion

    #region Constructor

    /**
     * Instancie un objet de type ContactForm avec un identifiant (optionnelle),
     * un objet et un message et la personne associée
     * @param string $objet objet du résultat du formulaire
     * @param string $message message du résultat du formulaire
     * @param int|null $idContact identifiant du résultat du formulaire (null si laissé vide)
     * @param PersonneEntity $personne personne associé
     * @throws InvalidArgumentException si le message ou l'objet est vide
     */
    public function __construct(string $objet,
                                string $message,
                                PersonneEntity $personne,
                                ?int $idContact = null)
    {
        self::assertMessageIsValid($message);
        self::assertObjetIsValid($objet);

        $this->idContact = $idContact;
        $this->objet = $objet;
        $this->message = $message;
        $this->personne = $personne;
    }

    #endregion

    #region Getters and Setters

    /**
     * Récupère l'identifiant du résultat du formulaire ou null
     * si pas encore en base
     * @return int|null l'identifiant du résultat du formulaire
     */
    public function getIdContact() : int|null
    {
        return $this->idContact;
    }

    /**
     * Récupère l'objet du résultat du formulaire
     * @return string l'objet du résultat du formulaire
     */
    public function getObjet() : string
    {
        return $this->objet;
    }

    /**
     * Définit l'objet du résultat du formulaire
     * @param string $objet objet du résultat du formulaire
     * @throws InvalidArgumentException si l'objet est vide
     */
    public function setObjet(string $objet): void
    {
        self::assertObjetIsValid($objet);
        $this->objet = $objet;
    }

    /**
     * Récupère le message du résultat du formulaire
     * @return string le message du résultat du formulaire
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * Définit le message du résultat du formulaire
     * @param string $message le message du résultat du formulaire
     * @throws InvalidArgumentException si le message est vide
     */
    public function setMessage(string $message): void
    {
        self::assertMessageIsValid($message);
        $this->message = $message;
    }

    /**
     * Récupère l'entité personne du résultat du formulaire
     * @return PersonneEntity l'entité personne du résultat du formulaire
     */
    public function getPersonne(): PersonneEntity
    {
        return $this->personne;
    }

    /**
     * Définit l'entité personne du résultat du formulaire
     * @param PersonneEntity $personne l'entité personne du résultat du formulaire
     * @return void
     */
    public function setPersonne(PersonneEntity $personne): void
    {
        $this->personne = $personne;
    }

    #endregion

    #region PRIVATE METHOD

    private static function assertMessageIsValid(string $message) : void {
        // Test si le message n'est pas vide
        if (trim($message) === '') {
            throw new InvalidArgumentException('message invalide, est vide');
        }
    }

    private static function assertObjetIsValid(string $objet) : void {
        // Test si l'objet n'est pas vide et qu'il rentre dans un varchar(75)
        if (trim($objet) === ''
                || mb_strlen($objet, 'UTF-8') > self::OBJ_LENGHT) {
            throw new InvalidArgumentException(sprintf(
                "objet invalide, vide ou supérieur à %d caractères.",
                self::OBJ_LENGHT
            ));
        }
    }

    #endregion
}
