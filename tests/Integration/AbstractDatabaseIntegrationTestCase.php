<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\BDD\DbConnection;
use App\BDD\DbManager;
use App\enums\DevisFormStatutEnum;
use App\enums\DevisFormTypeEnum;
use PDO;
use PHPUnit\Framework\TestCase;
require __DIR__ . '/configIntegration.php';

/**
 * Classe de base pour tous les tests d'intégration de DbManager.
 *
 * CONTRAINTES PRISES EN COMPTE
 * -----------------------------------------------------------------
 * - L'utilisateur SQL utilisé pour les tests ne dispose QUE des droits
 *   SELECT, INSERT, UPDATE, DELETE. Aucune opération DDL (CREATE, DROP,
 *   ALTER, TRUNCATE) n'est donc utilisée nulle part dans cette suite.
 * - La base cible contient déjà un jeu de données de démonstration
 *   (dump chatelier-web_v3.sql : ~80 lignes CONTACT_FORM, ~150 DEVIS_FORM,
 *   26 OPTION, de nombreuses PERSONNE). Ce jeu de données ne doit
 *   JAMAIS être altéré ou purgé par les tests.
 *
 * STRATÉGIE D'ISOLATION
 * -----------------------------------------------------------------
 * - Chaque test crée ses propres données, identifiées par un suffixe
 *   aléatoire unique (uniqueEmail()/uniqueOptionNom()), pour ne jamais
 *   entrer en collision avec les données existantes ni avec les
 *   données d'un autre test exécuté en parallèle.
 * - L'arrangement ("Given") d'un test passe par des helpers SQL "bruts"
 *   (insertRaw*) qui n'utilisent PAS DbManager : on ne fait jamais
 *   dépendre la mise en place d'un test de la méthode réellement
 *   testée par un AUTRE test.
 * - Chaque ligne créée (via un helper insertRaw* ou directement via
 *   DbManager dans le test) est enregistrée via les méthodes track*()
 *   afin que tearDown() supprime précisément - et seulement - ce que le
 *   test a créé, dans l'ordre imposé par les contraintes de clé
 *   étrangère.
 */
abstract class AbstractDatabaseIntegrationTestCase extends TestCase
{
    protected PDO $pdo;
    protected DbManager $dbManager;

    /** @var int[] */
    private array $trackedOptionIds = [];

    /** @var string[] */
    private array $trackedPersonneEmails = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = DbConnection::create();
        $this->dbManager = new DbManager($this->pdo);
    }

    protected function tearDown(): void
    {
        // Filet de sécurité : si un test a volontairement laissé une
        // transaction ouverte pour observer un comportement, on ne doit
        // jamais laisser cet état fuiter vers les tests suivants.
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        $this->cleanupTrackedRows();
        parent::tearDown();
    }

    #region Génération d'identifiants uniques

    protected function uniqueEmail(string $prefix = 'it'): string
    {
        // Reste < 100 caractères (contrainte PERSONNE.email varchar(100)).
        return sprintf('%s.%s@integration.test', $prefix, bin2hex(random_bytes(6)));
    }

    protected function uniqueOptionNom(string $prefix = 'IT_OPT'): string
    {
        // Reste < 50 caractères (contrainte OPTION.nom varchar(50)).
        return sprintf('%s_%s', $prefix, bin2hex(random_bytes(6)));
    }

    /**
     * Calcule un identifiant garanti inexistant pour une table donnée,
     * sans dépendre d'une valeur codée en dur (le jeu de données de
     * démonstration évolue). Plafonné à 32767 pour rester un smallint
     * valide (utilisé notamment pour idDevis / idOption).
     */
    protected function nextNonExistentId(string $table, string $idColumn): int
    {
        $stmt = $this->pdo->query(
            sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1000 FROM `%s`', $idColumn, $table)
        );

        return min(32767, (int) $stmt->fetchColumn());
    }

    #endregion

    #region Fixtures SQL "brutes" (ne passent pas par DbManager)

    protected function insertRawPersonne(
        string $email,
        string $nom = 'Nom Test',
        ?string $prenom = 'Prenom',
        ?string $tel = '+33612345678',
        bool $estMorale = false
    ): string {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `PERSONNE` (email, nom, prenom, tel, estMorale)
             VALUES (:email, :nom, :prenom, :tel, :estMorale)'
        );
        $stmt->execute([
            'email' => $email,
            'nom' => $nom,
            'prenom' => $prenom,
            'tel' => $tel,
            'estMorale' => $estMorale,
        ]);

        $this->trackedPersonneEmails[] = $email;

        return $email;
    }

    protected function insertRawOption(string $nom, float $nbJourHomme = 1.0): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `OPTION` (nom, nbJourHomme) VALUES (:nom, :nbJourHomme)'
        );
        $stmt->execute([
            'nom' => $nom,
            'nbJourHomme' => $nbJourHomme,
        ]);

        $idOption = (int) $this->pdo->lastInsertId();
        $this->trackedOptionIds[] = $idOption;

        return $idOption;
    }

    protected function insertRawContactForm(
        string $emailPersonne,
        string $objet = 'Objet test',
        string $message = 'Message test'
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `CONTACT_FORM` (objet, message, emailPersonne)
             VALUES (:objet, :message, :emailPersonne)'
        );
        $stmt->execute([
            'objet' => $objet,
            'message' => $message,
            'emailPersonne' => $emailPersonne,
        ]);

        return (int) $this->pdo->lastInsertId();;
    }

    protected function insertRawDevisForm(
        string $emailPersonne,
        DevisFormTypeEnum $type = DevisFormTypeEnum::vitrine,
        ?string $nomProjet = 'Projet test',
        string $descriptionProjet = 'Description test',
        DevisFormStatutEnum $statut = DevisFormStatutEnum::aTraiter,
        ?string $infoComplementaire = null
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `DEVIS_FORM`
                (type, nomProjet, descriptionProjet, informationComplementaire, statut, emailPersonne)
             VALUES
                (:type, :nomProjet, :descriptionProjet, :info, :statut, :emailPersonne)'
        );
        $stmt->execute([
            'type' => $type->value,
            'nomProjet' => $nomProjet,
            'descriptionProjet' => $descriptionProjet,
            'info' => $infoComplementaire,
            'statut' => $statut->value,
            'emailPersonne' => $emailPersonne,
        ]);

        return (int) $this->pdo->lastInsertId();;
    }

    protected function insertRawDevisOption(int $idDevis, int $idOption, ?int $compteur = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `DEVIS_OPTION` (idDevis, idOption, compteur)
             VALUES (:idDevis, :idOption, :compteur)'
        );
        $stmt->execute([
            'idDevis' => $idDevis,
            'idOption' => $idOption,
            'compteur' => $compteur,
        ]);
    }

    #endregion

    #region Tracking manuel (pour les lignes créées via DbManager, le SUT)

    protected function trackPersonneEmail(string $email): void
    {
        $this->trackedPersonneEmails[] = $email;
    }

    protected function trackOptionId(int $idOption): void
    {
        $this->trackedOptionIds[] = $idOption;
    }

    #endregion

    #region Lecture directe (assertions "boîte blanche" sur l'état réel de la base)

    protected function fetchPersonneRow(string $email): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM `PERSONNE` WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function fetchOptionRowByNom(string $nom): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM `OPTION` WHERE nom = :nom');
        $stmt->execute(['nom' => $nom]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function countRows(string $table, string $whereSql, array $params): int
    {
        $stmt = $this->pdo->prepare(sprintf('SELECT COUNT(*) FROM `%s` WHERE %s', $table, $whereSql));
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    #endregion

    private function cleanupRowsByEmail(string $email): void
    {
        // CONTACT_FORM
        $this->pdo
            ->prepare(
                'DELETE FROM `CONTACT_FORM`
             WHERE emailPersonne = :email'
            )
            ->execute([
                'email' => $email
            ]);

        // DEVIS_OPTION via les devis de la personne
        $this->pdo
            ->prepare(
                'DELETE do
             FROM `DEVIS_OPTION` do
             INNER JOIN `DEVIS_FORM` df
                ON do.idDevis = df.idDevis
             WHERE df.emailPersonne = :email'
            )
            ->execute([
                'email' => $email
            ]);

        // DEVIS_FORM
        $this->pdo
            ->prepare(
                'DELETE FROM `DEVIS_FORM`
             WHERE emailPersonne = :email'
            )
            ->execute([
                'email' => $email
            ]);

        // PERSONNE
        $this->pdo
            ->prepare(
                'DELETE FROM `PERSONNE`
             WHERE email = :email'
            )
            ->execute([
                'email' => $email
            ]);
    }

    private function cleanupTrackedRows(): void
    {
        // Nettoyage des données liées aux personnes créées par les tests
        foreach (array_unique($this->trackedPersonneEmails) as $email) {
            $this->cleanupRowsByEmail($email);
        }

        // OPTION.
        foreach ($this->trackedOptionIds as $idOption) {
            $this->pdo->prepare('DELETE FROM `OPTION` WHERE idOption = :id')->execute(['id' => $idOption]);
        }

        $this->trackedPersonneEmails = [];
    }
}
