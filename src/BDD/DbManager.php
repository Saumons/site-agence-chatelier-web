<?php

namespace App\BDD;

#region Dépendances
use App\BDD\entity\ContactFormEntity;
use App\BDD\entity\DevisFormEntity;
use App\BDD\entity\PersonneEntity;
use App\BDD\entity\OptionEntity;
use App\BDD\exception\DatabaseReadException;
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
        public function getAllContactForm(): array
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
                        "[%s] Erreur lecture CONTACT_FORM (getAll) : %s",
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
        public function getContactFormById(int $idContact): ?ContactFormEntity
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

                if (!$contact) {
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
        public function getAllDevisForm() : array
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
                            throw new DatabaseReadException(
                                "Le statut {$ligne['statut']} est invalide. pour le devis id:{$idDevis}."
                            );
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
                        "[%s] Erreur lecture DEVIS_FORM (getAll) : %s",
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
        public function getDevisFormById(int $idDevis): ?DevisFormEntity
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
        public function getAllOption() : array
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
                        "[%s] Erreur lecture OPTION (getAll) : %s",
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
        public function getOptionById(int $idOption) : ?OptionEntity
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
        public function getAllPersonne() : array
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
                        "[%s] Erreur lecture PERSONNE (getAll) : %s",
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
        public function getPersonneByEmail(string $mailPersonne) : ?PersonneEntity
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

        #endregion

        #region UPDATE

        #endregion

        #region DELETE

        #endregion
}

