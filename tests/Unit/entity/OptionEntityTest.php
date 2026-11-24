<?php

declare(strict_types=1);

namespace Tests\Unit\entity;

use App\BDD\entity\OptionEntity;
use App\enums\OptionTypeEnum;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité OptionEntity.
 *
 * Couvre le constructeur, les accesseurs, la validation du nom
 * (varchar(50), non vide), la validation du nombre de jours
 * homme en tant que DECIMAL(15,2) strictement positif, ainsi que
 * la gestion du type d'option (OptionTypeEnum).
 */
final class OptionEntityTest extends TestCase
{
    public function testConstructorCreatesEntityWithExpectedValues(): void
    {
        $option = new OptionEntity(
            nom: 'Hébergement',
            nbJourHomme: 2.5,
            type: OptionTypeEnum::webApp,
            nbAttribut: 3,
            idOption: 10
        );

        self::assertSame('Hébergement', $option->getNom());
        self::assertSame(2.5, $option->getNbJourHomme());
        self::assertSame(OptionTypeEnum::webApp, $option->getType());
        self::assertSame(3, $option->getNbAttribut());
        self::assertSame(10, $option->getIdOption());
    }

    public function testIdOptionAndNbAttributDefaultToNullWhenNotProvided(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        self::assertNull($option->getIdOption());
        self::assertNull($option->getNbAttribut());
    }

    public function testConstructorThrowsWhenNomIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: '', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);
    }

    public function testConstructorThrowsWhenNomIsOnlyWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: '   ', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);
    }

    public function testConstructorAcceptsNomAtMaxLengthBoundary(): void
    {
        $nom50 = str_repeat('a', 50);

        $option = new OptionEntity(nom: $nom50, nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        self::assertSame($nom50, $option->getNom());
    }

    public function testConstructorThrowsWhenNomExceedsMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: str_repeat('a', 51), nbJourHomme: 1.0, type: OptionTypeEnum::commerce);
    }

    public function testConstructorThrowsWhenNbJourHommeIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: 0.0, type: OptionTypeEnum::commerce);
    }

    public function testConstructorThrowsWhenNbJourHommeIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: -1.5, type: OptionTypeEnum::commerce);
    }

    public function testConstructorAcceptsNbJourHommeWithExactlyTwoDecimals(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 12.75, type: OptionTypeEnum::commerce);

        self::assertSame(12.75, $option->getNbJourHomme());
    }

    public function testConstructorThrowsWhenNbJourHommeHasMoreThanTwoDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: 1.234, type: OptionTypeEnum::commerce);
    }

    public function testConstructorThrowsWhenNbJourHommeExceedsThirteenDigitsBeforeDecimal(): void
    {
        // decimal(15,2) : 13 chiffres maximum avant la virgule (NBJOUR_LENGHT - NBJOUR_DECI),
        // soit un maximum de 9 999 999 999 999,99. Cette valeur en a 14 avant la virgule.
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: 99999999999999.0, type: OptionTypeEnum::commerce);
    }

    public function testConstructorAcceptsNbJourHommeCloseToMaxBoundary(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1234567890123.45, type: OptionTypeEnum::commerce);

        self::assertSame(1234567890123.45, $option->getNbJourHomme());
    }

    public function testSetNomUpdatesValueWhenValid(): void
    {
        $option = new OptionEntity(nom: 'Nom initial', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $option->setNom('Nouveau nom');

        self::assertSame('Nouveau nom', $option->getNom());
    }

    public function testSetNomThrowsWhenEmpty(): void
    {
        $option = new OptionEntity(nom: 'Nom initial', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $this->expectException(InvalidArgumentException::class);

        $option->setNom('');
    }

    public function testSetNomThrowsWhenExceedsMaxLength(): void
    {
        $option = new OptionEntity(nom: 'Nom initial', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $this->expectException(InvalidArgumentException::class);

        $option->setNom(str_repeat('a', 51));
    }

    public function testSetNbJourHommeUpdatesValueWhenValid(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $option->setNbJourHomme(5.5);

        self::assertSame(5.5, $option->getNbJourHomme());
    }

    public function testSetNbJourHommeThrowsWhenNotPositive(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $this->expectException(InvalidArgumentException::class);

        $option->setNbJourHomme(0.0);
    }

    public function testSetNbJourHommeThrowsWhenHasMoreThanTwoDecimals(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $this->expectException(InvalidArgumentException::class);

        $option->setNbJourHomme(3.14159);
    }

    public function testSetNbAttributAcceptsNullToClearValue(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::commerce, nbAttribut: 5);

        $option->setNbAttribut(null);

        self::assertNull($option->getNbAttribut());
    }

    public function testSetNbAttributUpdatesValue(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $option->setNbAttribut(7);

        self::assertSame(7, $option->getNbAttribut());
    }

    public function testConstructorCreatesEntityWithTypeRetrievableViaGetType(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::vitrine);

        self::assertSame(OptionTypeEnum::vitrine, $option->getType());
    }

    public function testGetTypeReturnsConstructorValue(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::trans);

        self::assertSame(OptionTypeEnum::trans, $option->getType());
    }

    public function testSetTypeUpdatesValue(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::webApp);

        $option->setType(OptionTypeEnum::commerce);

        self::assertSame(OptionTypeEnum::commerce, $option->getType());
    }

    public function testSetTypeAllowsSwitchingBetweenMultipleTypes(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::webApp);

        $option->setType(OptionTypeEnum::vitrine);
        self::assertSame(OptionTypeEnum::vitrine, $option->getType());

        $option->setType(OptionTypeEnum::trans);
        self::assertSame(OptionTypeEnum::trans, $option->getType());

        $option->setType(OptionTypeEnum::commerce);
        self::assertSame(OptionTypeEnum::commerce, $option->getType());
    }

    public function testTypeValueIsPreservedAfterMultipleOperations(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, type: OptionTypeEnum::commerce);

        $option->setNom('Autre nom');
        $option->setNbJourHomme(3.0);
        $option->setNbAttribut(2);

        self::assertSame(OptionTypeEnum::commerce, $option->getType());
    }
}