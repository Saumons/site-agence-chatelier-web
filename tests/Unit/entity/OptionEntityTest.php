<?php

declare(strict_types=1);

namespace Tests\Unit\entity;

use App\BDD\entity\OptionEntity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité OptionEntity.
 *
 * Couvre le constructeur, les accesseurs, la validation du nom
 * (varchar(50), non vide) et la validation du nombre de jours
 * homme en tant que DECIMAL(15,2) strictement positif.
 */
final class OptionEntityTest extends TestCase
{
    public function testConstructorCreatesEntityWithExpectedValues(): void
    {
        $option = new OptionEntity(
            nom: 'Hébergement',
            nbJourHomme: 2.5,
            nbAttribut: 3,
            idOption: 10
        );

        self::assertSame('Hébergement', $option->getNom());
        self::assertSame(2.5, $option->getNbJourHomme());
        self::assertSame(3, $option->getNbAttribut());
        self::assertSame(10, $option->getIdOption());
    }

    public function testIdOptionAndNbAttributDefaultToNullWhenNotProvided(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0);

        self::assertNull($option->getIdOption());
        self::assertNull($option->getNbAttribut());
    }

    public function testConstructorThrowsWhenNomIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: '', nbJourHomme: 1.0);
    }

    public function testConstructorThrowsWhenNomIsOnlyWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: '   ', nbJourHomme: 1.0);
    }

    public function testConstructorAcceptsNomAtMaxLengthBoundary(): void
    {
        $nom50 = str_repeat('a', 50);

        $option = new OptionEntity(nom: $nom50, nbJourHomme: 1.0);

        self::assertSame($nom50, $option->getNom());
    }

    public function testConstructorThrowsWhenNomExceedsMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: str_repeat('a', 51), nbJourHomme: 1.0);
    }

    public function testConstructorThrowsWhenNbJourHommeIsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: 0.0);
    }

    public function testConstructorThrowsWhenNbJourHommeIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: -1.5);
    }

    public function testConstructorAcceptsNbJourHommeWithExactlyTwoDecimals(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 12.75);

        self::assertSame(12.75, $option->getNbJourHomme());
    }

    public function testConstructorThrowsWhenNbJourHommeHasMoreThanTwoDecimals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: 1.234);
    }

    public function testConstructorThrowsWhenNbJourHommeExceedsThirteenDigitsBeforeDecimal(): void
    {
        // decimal(15,2) : 13 chiffres maximum avant la virgule (NBJOUR_LENGHT - NBJOUR_DECI),
        // soit un maximum de 9 999 999 999 999,99. Cette valeur en a 14 avant la virgule.
        $this->expectException(InvalidArgumentException::class);

        new OptionEntity(nom: 'Option', nbJourHomme: 99999999999999.0);
    }

    public function testConstructorAcceptsNbJourHommeCloseToMaxBoundary(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1234567890123.45);

        self::assertSame(1234567890123.45, $option->getNbJourHomme());
    }

    public function testSetNomUpdatesValueWhenValid(): void
    {
        $option = new OptionEntity(nom: 'Nom initial', nbJourHomme: 1.0);

        $option->setNom('Nouveau nom');

        self::assertSame('Nouveau nom', $option->getNom());
    }

    public function testSetNomThrowsWhenEmpty(): void
    {
        $option = new OptionEntity(nom: 'Nom initial', nbJourHomme: 1.0);

        $this->expectException(InvalidArgumentException::class);

        $option->setNom('');
    }

    public function testSetNomThrowsWhenExceedsMaxLength(): void
    {
        $option = new OptionEntity(nom: 'Nom initial', nbJourHomme: 1.0);

        $this->expectException(InvalidArgumentException::class);

        $option->setNom(str_repeat('a', 51));
    }

    public function testSetNbJourHommeUpdatesValueWhenValid(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0);

        $option->setNbJourHomme(5.5);

        self::assertSame(5.5, $option->getNbJourHomme());
    }

    public function testSetNbJourHommeThrowsWhenNotPositive(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0);

        $this->expectException(InvalidArgumentException::class);

        $option->setNbJourHomme(0.0);
    }

    public function testSetNbJourHommeThrowsWhenHasMoreThanTwoDecimals(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0);

        $this->expectException(InvalidArgumentException::class);

        $option->setNbJourHomme(3.14159);
    }

    public function testSetNbAttributAcceptsNullToClearValue(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0, nbAttribut: 5);

        $option->setNbAttribut(null);

        self::assertNull($option->getNbAttribut());
    }

    public function testSetNbAttributUpdatesValue(): void
    {
        $option = new OptionEntity(nom: 'Option', nbJourHomme: 1.0);

        $option->setNbAttribut(7);

        self::assertSame(7, $option->getNbAttribut());
    }
}
