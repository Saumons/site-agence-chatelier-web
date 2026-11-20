<?php

namespace App\BDD;

#region Dépendances
use App\BDD\entity\ContactFormEntity;
use App\BDD\entity\DevisFormEntity;
use App\BDD\entity\PersonneEntity;
use App\BDD\entity\OptionEntity;
use App\BDD\exception\DatabaseReadException;
use App\BDD\exception\DatabaseWriteException;
use App\enums\DevisFormStatutEnum;
use App\enums\DevisFormTypeEnum;
use PDO;
use PDOException;

#endregion

final class DbManager
{
    #region Properties
    private const DATE_FORMAT = 'd-m-Y H:i:s';
    private PDO $connexion;

    #endregion

    #region Constructor

    public function __construct(PDO $connexion)
    {
        $this->connexion = $connexion;
    }

    #endregion

    #region SELECT

        #region CONTACT_FORM

        /**
         * Récupère tous les enregistrements de la
         * table CONTACT_FORM
         * @return ContactFormEntity[] liste des résultats du formulaire de contact
         *  (ContactFormEntity)
         */
        public function findAllContactForm(): array
        {
            try {
                $sql = "SELECT *
                        FROM `CONTACT_FORM` cf
                        JOIN PERSONNE p
                            ON cf.emailPersonne = p.email";

                $stmt = $this->connexion->prepare($sql);
                $stmt->execute();

                $contactsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $contacts = [];

                foreach ($contactsData as $contact) {
                    $contacts[] = new ContactFormEntity(
                        objet: (string) $contact['objet'],
                        message: (string) $contact['message'],
                        personne: new PersonneEntity(
                            mail: (string) $contact['email'],
                            nom: (string) $contact['nom'],
                            prenom: $contact['prenom'],             // nullable
                            tel: $contact['tel'],                   // nullable
                            estMorale: (bool) $contact['estMorale'],
                        ),
                        idContact: (int) $contact['idContact']
                    );
                }

                return $contacts;

            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture CONTACT_FORM (findAll) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    'Erreur impossible de lire les données des formulaires de contact.',
                    0,
                    $e
                );
            }
        }

        /**
         * Récupère un enregistrement de la table CONTACT_FORM
         * à partir de son identifiant
         * @param int $idContact identifiant du résultat du formulaire de contact
         * @return ContactFormEntity|null résultat du formulaire de contact ou null si pas trouvé
         */
        public function findContactFormById(int $idContact): ?ContactFormEntity
        {
            try {
                $sql = "SELECT *
                        FROM `CONTACT_FORM` cf
                        JOIN PERSONNE p
                            ON cf.emailPersonne = p.email
                        WHERE idContact = :idContact";

                $stmt = $this->connexion->prepare($sql);
                $stmt->execute([
                    'idContact' => $idContact
                ]);

                $contact = $stmt->fetch(PDO::FETCH_ASSOC);

                if (empty($contact)) {
                    return null;
                }

                return new ContactFormEntity(
                    objet: (string) $contact['objet'],
                    message: (string) $contact['message'],
                    personne: new PersonneEntity(
                        mail: (string) $contact['email'],
                        nom: (string) $contact['nom'],
                        prenom: $contact['prenom'],                     // nullable
                        tel: $contact['tel'],                           // nullable
                        estMorale: (bool) $contact['estMorale'],
                    ),
                    idContact: $contact['idContact']
                );

            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture CONTACT_FORM (contact id:{$idContact}) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    "Erreur impossible de lire les données du formulaire de contact id:{$idContact}",
                    0,
                    $e
                );
            }
        }

        #endregion

        #region DEVIS_FORM

        /**
         * Récupère tous les enregistrements de la
         * table DEVIS_FORM
         * @return DevisFormEntity[] liste des devis (DevisFormEntity)
         */
        public function findAllDevisForm() : array
        {
            try {
                $sql = "SELECT df.*,
                                p.*,
                                o.idOption,
                                o.nom AS nomOption,
                                o.nbJourHomme,
                                do.compteur
                        FROM DEVIS_FORM df
                        JOIN PERSONNE p
                            ON p.email = df.emailPersonne
                        LEFT JOIN DEVIS_OPTION do
                            ON do.idDevis = df.idDevis
                        LEFT JOIN `OPTION` o
                            ON o.idOption = do.idOption";
                $stmt = $this->connexion->prepare($sql);
                $stmt->execute();
                $devisData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $devisMap = [];
                foreach ($devisData as $ligne) {

                    $idDevis = $ligne['idDevis'];

                    if (!isset($devisMap[$idDevis])) {
                        $enumStatut = DevisFormStatutEnum::tryFrom($ligne['statut']);
                        $enumType = DevisFormTypeEnum::tryFrom($ligne['type']);

                        if ($enumStatut === null) {
                            throw new DatabaseReadException( sprintf (
                                "Le statut {$ligne['statut']} est invalide. pour le devis id:{$idDevis}."
                            ));
                        } elseif ($enumType === null) {
                            throw new DatabaseReadException(
                                "Le type {$ligne['type']} est invalide. pour le devis id:{$idDevis}."
                            );
                        }
                        $devisMap[$idDevis] = new DevisFormEntity(
                            type: $enumType,
                            nomProjet: $ligne['nomProjet'],                                     // nullable
                            descriptionProjet: (string) $ligne['descriptionProjet'],
                            statut: $enumStatut,
                            infoComplementaire: $ligne['informationComplementaire'],            // nullable
                            personne: new PersonneEntity(
                                mail: (string) $ligne['email'],
                                nom: (string) $ligne['nom'],
                                prenom: $ligne['prenom'],                                       // nullable
                                tel: $ligne['tel'],                                             // nullable
                                estMorale: (bool) $ligne['estMorale'],
                            ),
                            idDevis: (int) $idDevis,
                            options: []
                        );
                    }

                    if ($ligne['idOption'] !== null) {

                        $devisMap[$idDevis]->addOption(
                            new OptionEntity(
                                nom: (string) $ligne['nomOption'],
                                nbJourHomme: (float) $ligne['nbJourHomme'],
                                nbAttribut: $ligne['compteur'],                 // nullable
                                idOption: (int) $ligne['idOption']
                            )
                        );
                    }
                }

                return array_values($devisMap);
            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture DEVIS_FORM (findAll) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    'Erreur impossible de lire les données des devis.',
                    0,
                    $e
                );
            }
        }

        /**
         * Récupère un enregistrement de la table DEVIS_FORM
         * à partir de son identifiant
         * @param int $idDevis identifiant du devis
         * @return DevisFormEntity|null Devis ou null si pas trouvé
         */
        public function findDevisFormById(int $idDevis): ?DevisFormEntity
        {
            try {
                $sql = "SELECT df.*,
                            p.*,
                            o.idOption,
                            o.nom AS nomOption,
                            o.nbJourHomme,
                            do.compteur
                    FROM DEVIS_FORM df
                    JOIN PERSONNE p
                        ON p.email = df.emailPersonne
                    LEFT JOIN DEVIS_OPTION do
                        ON do.idDevis = df.idDevis
                    LEFT JOIN `OPTION` o
                        ON o.idOption = do.idOption
                    WHERE df.idDevis = :idDevis";

                $stmt = $this->connexion->prepare($sql);
                $stmt->execute(['idDevis' => $idDevis]);

                $devisData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($devisData)) {
                    return null;
                }

                $devis = null;

                foreach ($devisData as $ligne) {

                    if ($devis === null) {

                        $enumStatut = DevisFormStatutEnum::tryFrom($ligne['statut']);
                        $enumType = DevisFormTypeEnum::tryFrom($ligne['type']);

                        if ($enumStatut === null) {
                            throw new DatabaseReadException(
                                "Le statut {$ligne['statut']} est invalide pour le devis id:{$idDevis}."
                            );
                        }

                        if ($enumType === null) {
                            throw new DatabaseReadException(
                                "Le type {$ligne['type']} est invalide pour le devis id:{$idDevis}."
                            );
                        }

                        $devis = new DevisFormEntity(
                            type: $enumType,
                            nomProjet: $ligne['nomProjet'],                                         // nullable
                            descriptionProjet: (string) $ligne['descriptionProjet'],
                            statut: $enumStatut,
                            infoComplementaire: $ligne['informationComplementaire'],                // nullable
                            personne: new PersonneEntity(
                                mail: (string) $ligne['email'],
                                nom: (string) $ligne['nom'],
                                prenom: $ligne['prenom'],                                           // nullable
                                tel: $ligne['tel'],                                                 // nullable
                                estMorale: (bool) $ligne['estMorale'],
                            ),
                            idDevis: (int) $ligne['idDevis'],
                            options: []
                        );
                    }

                    if ($ligne['idOption'] !== null) {
                        $devis->addOption(
                            new OptionEntity(
                                nom: (string) $ligne['nomOption'],
                                nbJourHomme: (float) $ligne['nbJourHomme'],
                                nbAttribut: $ligne['compteur'],                 // nullable
                                idOption: (int) $ligne['idOption']
                            )
                        );
                    }
                }

                return $devis;

            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture DEVIS_FORM (devis id:{$idDevis}) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    "Erreur impossible de lire les données du devis id:{$idDevis}",
                    0,
                    $e
                );
            }
        }

        #endregion

        #region OPTION

        /**
         * Récupère tous les enregistrements de la
         * table OPTION
         * @return OptionEntity[] liste des options (OptionEntity)
         */
        public function findAllOption() : array
        {
            try {
                $sql = "SELECT * FROM `OPTION`";
                $stmt = $this->connexion->prepare($sql);
                $stmt->execute();
                $optionsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $options = [];
                foreach ($optionsData as $option) {
                    $options[] = new OptionEntity(
                        nom: (string) $option['nom'],
                        nbJourHomme: (float) $option['nbJourHomme'],
                        idOption: (int) $option['idOption'],
                    );
                }
                return $options;
            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture OPTION (findAll) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    'Erreur impossible de lire les données des options.',
                    0,
                    $e
                );
            }
        }

        /**
         * Récupère un enregistrement de la table OPTION
         * à partir de son identifiant
         * @param int $idOption identifiant de l'option
         * @return OptionEntity|null Option ou null si pas trouvé
         */
        public function findOptionById(int $idOption) : ?OptionEntity
        {
            try {
                $sql = "SELECT * FROM `OPTION`
                    WHERE idOption = :idOption";
                $stmt = $this->connexion->prepare($sql);
                $stmt->execute(["idOption" => $idOption]);
                $optionData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (empty($optionData)) {
                    return null;
                } else {
                    return new OptionEntity(
                        nom: (string) $optionData['nom'],
                        nbJourHomme: (float) $optionData['nbJourHomme'],
                        idOption: (int) $optionData['idOption'],
                    );
                }
            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture OPTION (option id:{$idOption}) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    "Erreur impossible de lire les données de l'option id:{$idOption}",
                    0,
                    $e
                );
            }
        }

        #endregion

        #region PERSONNE

        /**
         * Récupère tous les enregistrements de la
         * table PERSONNE
         * @return PersonneEntity[] liste des personnes (PersonneEntity)
         */
        public function findAllPersonne() : array
        {
            try {
                $sql = "SELECT * FROM `PERSONNE`";
                $stmt = $this->connexion->prepare($sql);
                $stmt->execute();
                $personnesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $personnes = [];
                foreach ($personnesData as $personne) {
                    $personnes[] = new PersonneEntity(
                        mail: (string) $personne['email'],
                        nom: (string) $personne['nom'],
                        prenom: $personne['prenom'],                // nullable
                        tel: $personne['tel'],                      // nullable
                        estMorale: (bool) $personne['estMorale'],
                    );
                }
                return $personnes;
            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture PERSONNE (findAll) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    'Erreur impossible de lire les données des personnes.',
                    0,
                    $e
                );
            }
        }

        /**
         * Récupère un enregistrement de la table PERSONNE
         * à partir de son email (clé primaire)
         * @param string $mailPersonne identifiant de la personne
         * @return PersonneEntity|null Personne ou null si pas trouvé
         */
        public function findPersonneByEmail(string $mailPersonne) : ?PersonneEntity
        {
            try {
                $sql = "SELECT * FROM `PERSONNE`
                    WHERE email = :mailPersonne";
                $stmt = $this->connexion->prepare($sql);
                $stmt->execute(["mailPersonne" => $mailPersonne]);
                $personneData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (empty($personneData)) {
                    return null;
                } else {
                    return new PersonneEntity(
                        mail: (string) $personneData['email'],
                        nom: (string) $personneData['nom'],
                        prenom: $personneData['prenom'],                    // nullable
                        tel: $personneData['tel'],                          // nullable
                        estMorale: (bool) $personneData['estMorale'],
                    );
                }
            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur lecture PERSONNE (personne mail:{$mailPersonne}) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseReadException(
                    "Erreur impossible de lire les données de la personne mail:{$mailPersonne}",
                    0,
                    $e
                );
            }
        }

        #endregion

        #endregion

    #region INSERT

        #region CONTACT_FORM

        /**
         * Insère un formulaire de contact.
         *
         * @param ContactFormEntity $contactForm formulaire de contact
         * @throws DatabaseWriteException En cas d'erreur d'écriture en base.
         */
        public function insertContactForm(ContactFormEntity $contactForm): void
        {
            try {
                // Une erreur annule les insertions associées.
                $this->connexion->beginTransaction();

                // Crée la personne si elle n'existe pas encore.
                $personne = $contactForm->getPersonne();
                $email = $personne->getMail();
                if ($this->findPersonneByEmail($email) === null) {
                    $this->insertPersonne($personne);
                }

                $sql = "INSERT INTO `CONTACT_FORM` (objet, message, emailPersonne)
                            VALUES (:objet, :message, :emailPersonne)";
                $this->connexion
                    ->prepare($sql)
                    ->execute([
                    'objet' => $contactForm->getObjet(),
                    'message' => $contactForm->getMessage(),
                    'emailPersonne' => $email,
                ]);
                $this->connexion->commit();

            } catch (PDOException $e) {

                if ($this->connexion->inTransaction()) {
                    $this->connexion->rollBack();
                }
                error_log(
                    sprintf(
                        "[%s] Erreur écriture CONTACT_FORM : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseWriteException(
                    "Erreur impossible d'insérer un formulaire de contact",
                    0,
                    $e
                );
            }
        }

        #endregion

        #region DEVIS_FORM

        /**
         * Insert un formulaire de devis
         *
         * @param DevisFormEntity $devisForm formulaire de devis à insérer
         * @throws DatabaseWriteException En cas d'erreur d'écriture en base.
         */
        public function insertDevisForm(DevisFormEntity $devisForm): void
        {
            try {
                // Permet de rollback l'insert de personne si l'insert de
                // contactForm génère une erreur
                $this->connexion->beginTransaction();

                // Crée la personne si elle n'existe pas encore.
                $personne = $devisForm->getPersonne();
                $email = $personne->getMail();
                if ($this->findPersonneByEmail($email) === null) {
                    $this->insertPersonne($personne);
                }

                $sql = "INSERT INTO `DEVIS_FORM` (type, nomProjet, descriptionProjet,
                                        informationComplementaire, statut, emailPersonne)
                                    VALUES (:type,:nomProjet,:descriptionProjet,:informationComplementaire,
                                            :statut, :emailPersonne);";
                $this->connexion
                    ->prepare($sql)
                    ->execute([
                        'type' => $devisForm->getType()->value,
                        'nomProjet' => $devisForm->getNomProjet(),                              // nullable
                        'descriptionProjet' => $devisForm->getDescriptionProjet(),
                        'informationComplementaire' => $devisForm->getInfoComplementaire(),     // nullable
                        'statut' => $devisForm->getStatut()->value,
                        'emailPersonne' => $email,
                    ]);

                $idDevis = (int)$this->connexion->lastInsertId();
                foreach ($devisForm->getOptions() as $option) {
                    $this->insertDevisOption($idDevis,$option);
                }

                $this->connexion->commit();

            } catch (PDOException $e) {
                if ($this->connexion->inTransaction()) {
                    $this->connexion->rollBack();
                }
                error_log(
                    sprintf(
                        "[%s] Erreur écriture DEVIS_FORM : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseWriteException(
                    "Erreur impossible d'insérer le formulaire de devis",
                    0,
                    $e
                );
            }
        }

        #endregion

        #region DEVIS_OPTION

        /**
         * Insert le lien entre un devis et une option
         *
         * @param int $devisFormID id du devis
         * @param OptionEntity $option option
         * @throws DatabaseWriteException En cas d'erreur d'écriture en base.
         */
        public function insertDevisOption(int $devisFormID, OptionEntity $option): void
        {
            try {

                $sql = "INSERT INTO `DEVIS_OPTION` (idDevis, idOption, compteur)
                                        VALUES (:idDevis,:idOption,:compteur);";
                $this->connexion
                    ->prepare($sql)
                    ->execute([
                        'idDevis' => $devisFormID,
                        'idOption' => $option->getIdOption(),
                        'compteur' => $option->getNbAttribut(),     // nullable
                    ]);

            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur écriture DEVIS_OPTION (idDevis:{$devisFormID} / idOption{$option->getIdOption()}) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseWriteException(
                    "Erreur impossible d'insérer le lien entre devis et option",
                    0,
                    $e
                );
            }
        }

        #endregion

        #region OPTION

        /**
         * Insert une option
         *
         * @param OptionEntity $option option à insérer
         * @throws DatabaseWriteException En cas d'erreur d'écriture en base.
         */
        public function insertOption(OptionEntity $option): void
        {
            try {
                $sql = "INSERT INTO `OPTION` (nom,nbJourHomme)
                                    VALUES (:nom,:nbJourHomme);";
                $this->connexion
                    ->prepare($sql)
                    ->execute([
                        'nom' => $option->getNom(),
                        'nbJourHomme' => $option->getNbJourHomme(),
                    ]);

            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur écriture OPTION (option:{$option->getNom()}) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseWriteException(
                    "Erreur impossible d'insérer l'option",
                    0,
                    $e
                );
            }
        }

        #endregion

        #region PERSONNE

        /**
         * Insert une personne
         *
         * @param PersonneEntity $personne personne à insérer
         * @throws DatabaseWriteException En cas d'erreur d'écriture en base.
         */
        public function insertPersonne(PersonneEntity $personne): void
        {
            try {
                $sql = "INSERT INTO `PERSONNE` (email,nom,prenom,tel,estMorale)
                                VALUES (:email,:nom,:prenom,:tel,:estMorale);";
                $this->connexion
                    ->prepare($sql)
                    ->execute([
                    'email' => $personne->getMail(),
                    'nom' => $personne->getNom(),
                    'prenom' => $personne->getPrenom(),         // nullable
                    'tel' => $personne->getTel(),               // nullable
                    'estMorale' => $personne->isEstMorale(),
                ]);

            } catch (PDOException $e) {

                error_log(
                    sprintf(
                        "[%s] Erreur écriture PERSONNE (personne:{$personne->getMail()}) : %s",
                        date(DbManager::DATE_FORMAT),
                        $e->getMessage()
                    )
                );

                throw new DatabaseWriteException(
                    "Erreur impossible d'insérer la personne",
                    0,
                    $e
                );
            }
        }

        #endregion

    #endregion

    #region UPDATE

    #endregion

    #region DELETE

        #endregion
}

