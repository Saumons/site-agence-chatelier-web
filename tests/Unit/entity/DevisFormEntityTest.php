<?php

declare(strict_types=1);

namespace Tests\Unit\entity;

use App\BDD\entity\DevisFormEntity;
use App\BDD\entity\OptionEntity;
use App\BDD\entity\PersonneEntity;
use App\enums\DevisFormStatutEnum;
use App\enums\DevisFormTypeEnum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité DevisFormEntity.
 *
 * Couvre le constructeur (y compris la gestion de la collection
 * d'options), les accesseurs, les relations vers PersonneEntity et
 * OptionEntity, la validation de la description du projet (non
 * vide), la validation optionnelle du nom du projet (nullable,
 * varchar(50)), ainsi que addOption() et removeOption().
 */
final class DevisFormEntityTest extends TestCase
{
    private PersonneEntity $personne;

    protected function setUp(): void
    {
        $this->personne = new PersonneEntity(
            mail: 'client@example.com',
            nom: 'Client',
            prenom: 'Test',
            tel: null,
            estMorale: false
        );
    }

    /**
     * Fabrique un DevisFormEntity valide avec des valeurs par défaut
     * surchargeables, pour alléger les tests ci-dessous.
     *
     * @param OptionEntity[]|null $options
     */
    private function createDevis(
        ?string $nomProjet = 'Refonte vitrine',
        string $descriptionProjet = 'Description du projet',
        ?string $infoComplementaire = null,
        ?int $idDevis = null,
        ?array $options = null
    ): DevisFormEntity {
        return new DevisFormEntity(
            type: DevisFormTypeEnum::vitrine,
            nomProjet: $nomProjet,
            descriptionProjet: $descriptionProjet,
            statut: DevisFormStatutEnum::aTraiter,
            infoComplementaire: $infoComplementaire,
            personne: $this->personne,
            idDevis: $idDevis,
            options: $options
        );
    }

    public function testConstructorCreatesEntityWithExpectedValues(): void
    {
        $devis = $this->createDevis(
            nomProjet: 'Site vitrine',
            descriptionProjet: 'Un site vitrine pour une boulangerie',
            infoComplementaire: 'Livraison sous 3 mois',
            idDevis: 7
        );

        self::assertSame(DevisFormTypeEnum::vitrine, $devis->getType());
        self::assertSame('Site vitrine', $devis->getNomProjet());
        self::assertSame('Un site vitrine pour une boulangerie', $devis->getDescriptionProjet());
        self::assertSame(DevisFormStatutEnum::aTraiter, $devis->getStatut());
        self::assertSame('Livraison sous 3 mois', $devis->getInfoComplementaire());
        self::assertSame($this->personne, $devis->getPersonne());
        self::assertSame(7, $devis->getIdDevis());
    }

    public function testIdDevisDefaultsToNullWhenNotProvided(): void
    {
        $devis = $this->createDevis();

        self::assertNull($devis->getIdDevis());
    }

    public function testOptionsDefaultToEmptyArrayWhenNotProvided(): void
    {
        $devis = $this->createDevis();

        self::assertSame([], $devis->getOptions());
    }

    public function testConstructorPopulatesOptionsFromArray(): void
    {
        $option1 = new OptionEntity(nom: 'Hébergement', nbJourHomme: 1.0, idOption: 1);
        $option2 = new OptionEntity(nom: 'Maintenance', nbJourHomme: 2.0, idOption: 2);

        $devis = $this->createDevis(options: [$option1, $option2]);

        self::assertSame([$option1, $option2], $devis->getOptions());
    }

    public function testConstructorAcceptsNullNomProjet(): void
    {
        $devis = $this->createDevis(nomProjet: null);

        self::assertNull($devis->getNomProjet());
    }

    public function testConstructorAcceptsNomProjetAtMaxLengthBoundary(): void
    {
        $nomProjet50 = str_repeat('a', 50);

        $devis = $this->createDevis(nomProjet: $nomProjet50);

        self::assertSame($nomProjet50, $devis->getNomProjet());
    }

    public function testConstructorThrowsWhenNomProjetExceedsMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createDevis(nomProjet: str_repeat('a', 51));
    }

    public function testConstructorAcceptsNullInfoComplementaire(): void
    {
        $devis = $this->createDevis(infoComplementaire: null);

        self::assertNull($devis->getInfoComplementaire());
    }

    public function testConstructorThrowsWhenDescriptionProjetIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createDevis(descriptionProjet: '');
    }

    public function testConstructorThrowsWhenDescriptionProjetIsOnlyWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createDevis(descriptionProjet: "   \n");
    }

    public function testSetTypeUpdatesValue(): void
    {
        $devis = $this->createDevis();

        $devis->setType(DevisFormTypeEnum::commerce);

        self::assertSame(DevisFormTypeEnum::commerce, $devis->getType());
    }

    public function testSetStatutUpdatesValue(): void
    {
        $devis = $this->createDevis();

        $devis->setStatut(DevisFormStatutEnum::accepte);

        self::assertSame(DevisFormStatutEnum::accepte, $devis->getStatut());
    }

    public function testSetNomProjetAcceptsNullToClearValue(): void
    {
        $devis = $this->createDevis(nomProjet: 'Projet initial');

        $devis->setNomProjet(null);

        self::assertNull($devis->getNomProjet());
    }

    public function testSetNomProjetThrowsWhenExceedsMaxLength(): void
    {
        $devis = $this->createDevis();

        $this->expectException(InvalidArgumentException::class);

        $devis->setNomProjet(str_repeat('a', 51));
    }

    public function testSetDescriptionProjetUpdatesValueWhenValid(): void
    {
        $devis = $this->createDevis();

        $devis->setDescriptionProjet('Nouvelle description');

        self::assertSame('Nouvelle description', $devis->getDescriptionProjet());
    }

    public function testSetDescriptionProjetThrowsWhenEmpty(): void
    {
        $devis = $this->createDevis();

        $this->expectException(InvalidArgumentException::class);

        $devis->setDescriptionProjet('   ');
    }

    public function testSetInfoComplementaireAcceptsNullToClearValue(): void
    {
        $devis = $this->createDevis(infoComplementaire: 'Info initiale');

        $devis->setInfoComplementaire(null);

        self::assertNull($devis->getInfoComplementaire());
    }

    public function testSetPersonneUpdatesRelation(): void
    {
        $devis = $this->createDevis();

        $autrePersonne = new PersonneEntity(
            mail: 'autre@example.com',
            nom: 'Autre',
            prenom: null,
            tel: null,
            estMorale: true
        );

        $devis->setPersonne($autrePersonne);

        self::assertSame($autrePersonne, $devis->getPersonne());
    }

    public function testAddOptionAppendsToOptionsList(): void
    {
        $devis = $this->createDevis();
        $option = new OptionEntity(nom: 'Hébergement', nbJourHomme: 1.0, idOption: 1);

        $devis->addOption($option);

        self::assertSame([$option], $devis->getOptions());
    }

    public function testAddOptionCanBeCalledMultipleTimes(): void
    {
        $devis = $this->createDevis();
        $option1 = new OptionEntity(nom: 'Hébergement', nbJourHomme: 1.0, idOption: 1);
        $option2 = new OptionEntity(nom: 'Maintenance', nbJourHomme: 2.0, idOption: 2);

        $devis->addOption($option1);
        $devis->addOption($option2);

        self::assertCount(2, $devis->getOptions());
        self::assertSame([$option1, $option2], $devis->getOptions());
    }

    public function testRemoveOptionRemovesMatchingOptionByIdOption(): void
    {
        $devis = $this->createDevis();
        $option1 = new OptionEntity(nom: 'Hébergement', nbJourHomme: 1.0, idOption: 1);
        $option2 = new OptionEntity(nom: 'Maintenance', nbJourHomme: 2.0, idOption: 2);
        $devis->addOption($option1);
        $devis->addOption($option2);

        $devis->removeOption($option1);

        self::assertSame([$option2], $devis->getOptions());
    }

    public function testRemoveOptionMatchesByIdOptionValueEvenOnADifferentInstance(): void
    {
        // removeOption() compare par getIdOption(), pas par identité d'objet : une
        // instance différente mais partageant le même idOption doit donc matcher.
        $devis = $this->createDevis();
        $original = new OptionEntity(nom: 'Hébergement', nbJourHomme: 1.0, idOption: 1);
        $devis->addOption($original);

        $copieAvecMemeId = new OptionEntity(nom: 'Hébergement (copie)', nbJourHomme: 9.0, idOption: 1);
        $devis->removeOption($copieAvecMemeId);

        self::assertSame([], $devis->getOptions());
    }

    public function testRemoveOptionReindexesRemainingOptions(): void
    {
        $devis = $this->createDevis();
        $option1 = new OptionEntity(nom: 'Option 1', nbJourHomme: 1.0, idOption: 1);
        $option2 = new OptionEntity(nom: 'Option 2', nbJourHomme: 2.0, idOption: 2);
        $option3 = new OptionEntity(nom: 'Option 3', nbJourHomme: 3.0, idOption: 3);
        $devis->addOption($option1);
        $devis->addOption($option2);
        $devis->addOption($option3);

        $devis->removeOption($option2);

        self::assertSame([0 => $option1, 1 => $option3], $devis->getOptions());
    }

    public function testRemoveOptionThrowsWhenOptionDoesNotExist(): void
    {
        $devis = $this->createDevis();
        $option = new OptionEntity(nom: 'Hébergement', nbJourHomme: 1.0, idOption: 1);
        $devis->addOption($option);

        $inconnue = new OptionEntity(nom: 'Inconnue', nbJourHomme: 1.0, idOption: 999);

        $this->expectException(InvalidArgumentException::class);

        $devis->removeOption($inconnue);
    }

    public function testRemoveOptionThrowsWhenOptionsListIsEmpty(): void
    {
        $devis = $this->createDevis();
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, idOption: 1);

        $this->expectException(InvalidArgumentException::class);

        $devis->removeOption($option);
    }

    public function testRemoveOptionWithNullIdOptionRemovesFirstListedOptionRegardlessOfInstancePassed(): void
    {
        // Comportement documenté : deux options non persistées (idOption === null)
        // sont considérées équivalentes par removeOption(), puisque la comparaison se
        // fait sur getIdOption(). La première option de la liste ayant un id "null"
        // correspondant est donc retirée, même si une autre instance a été passée.
        $devis = $this->createDevis();
        $nonPersistee1 = new OptionEntity(nom: 'Option A', nbJourHomme: 1.0);
        $nonPersistee2 = new OptionEntity(nom: 'Option B', nbJourHomme: 2.0);
        $devis->addOption($nonPersistee1);
        $devis->addOption($nonPersistee2);

        $devis->removeOption($nonPersistee2);

        self::assertSame([$nonPersistee2], $devis->getOptions());
    }
}
