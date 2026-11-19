<?php

declare(strict_types=1);

namespace enums;

use App\enums\DevisFormStatutEnum;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Tests unitaires de l'enum DevisFormStatutEnum.
 *
 * Vérifie que chaque case expose la bonne valeur backée, que cases()
 * retourne l'ensemble complet et dans l'ordre attendu, et que from()/
 * tryFrom() se comportent correctement sur des valeurs valides et
 * invalides.
 */
final class DevisFormStatutEnumTest extends TestCase
{
    public function testATraiterCaseHasExpectedValue(): void
    {
        self::assertSame('À traiter', DevisFormStatutEnum::aTraiter->value);
    }

    public function testEnCoursCaseHasExpectedValue(): void
    {
        self::assertSame('En cours', DevisFormStatutEnum::enCours->value);
    }

    public function testAccepteCaseHasExpectedValue(): void
    {
        self::assertSame('Accepté', DevisFormStatutEnum::accepte->value);
    }

    public function testAnnuleCaseHasExpectedValue(): void
    {
        self::assertSame('Annulé', DevisFormStatutEnum::annule->value);
    }

    public function testCasesReturnsAllFourCasesInDeclarationOrder(): void
    {
        self::assertSame(
            [
                DevisFormStatutEnum::aTraiter,
                DevisFormStatutEnum::enCours,
                DevisFormStatutEnum::accepte,
                DevisFormStatutEnum::annule,
            ],
            DevisFormStatutEnum::cases()
        );
    }

    public function testFromReturnsMatchingCaseForEachValidValue(): void
    {
        self::assertSame(DevisFormStatutEnum::aTraiter, DevisFormStatutEnum::from('À traiter'));
        self::assertSame(DevisFormStatutEnum::enCours, DevisFormStatutEnum::from('En cours'));
        self::assertSame(DevisFormStatutEnum::accepte, DevisFormStatutEnum::from('Accepté'));
        self::assertSame(DevisFormStatutEnum::annule, DevisFormStatutEnum::from('Annulé'));
    }

    public function testFromThrowsValueErrorForInvalidValue(): void
    {
        $this->expectException(ValueError::class);

        DevisFormStatutEnum::from('Statut inexistant');
    }

    public function testTryFromReturnsMatchingCaseForValidValue(): void
    {
        self::assertSame(DevisFormStatutEnum::enCours, DevisFormStatutEnum::tryFrom('En cours'));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        self::assertNull(DevisFormStatutEnum::tryFrom('Statut inexistant'));
    }

    public function testTryFromIsCaseSensitive(): void
    {
        // La casse de "à traiter" (minuscule) ne correspond à aucune valeur backée.
        self::assertNull(DevisFormStatutEnum::tryFrom('à traiter'));
    }
}
