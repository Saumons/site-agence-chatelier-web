<?php

declare(strict_types=1);

namespace Tests\unit\enum;

use App\enums\DevisFormTypeEnum;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Tests unitaires de l'enum DevisFormTypeEnum.
 *
 * Vérifie que chaque case expose la bonne valeur backée, que cases()
 * retourne l'ensemble complet et dans l'ordre attendu, et que from()/
 * tryFrom() se comportent correctement sur des valeurs valides et
 * invalides.
 */
final class DevisFormTypeEnumTest extends TestCase
{
    public function testWebAppCaseHasExpectedValue(): void
    {
        self::assertSame('webApp', DevisFormTypeEnum::webApp->value);
    }

    public function testCommerceCaseHasExpectedValue(): void
    {
        self::assertSame('commerce', DevisFormTypeEnum::commerce->value);
    }

    public function testVitrineCaseHasExpectedValue(): void
    {
        self::assertSame('vitrine', DevisFormTypeEnum::vitrine->value);
    }

    public function testCasesReturnsAllThreeCasesInDeclarationOrder(): void
    {
        self::assertSame(
            [
                DevisFormTypeEnum::webApp,
                DevisFormTypeEnum::commerce,
                DevisFormTypeEnum::vitrine,
            ],
            DevisFormTypeEnum::cases()
        );
    }

    public function testFromReturnsMatchingCaseForEachValidValue(): void
    {
        self::assertSame(DevisFormTypeEnum::webApp, DevisFormTypeEnum::from('webApp'));
        self::assertSame(DevisFormTypeEnum::commerce, DevisFormTypeEnum::from('commerce'));
        self::assertSame(DevisFormTypeEnum::vitrine, DevisFormTypeEnum::from('vitrine'));
    }

    public function testFromThrowsValueErrorForInvalidValue(): void
    {
        $this->expectException(ValueError::class);

        DevisFormTypeEnum::from('inexistant');
    }

    public function testTryFromReturnsMatchingCaseForValidValue(): void
    {
        self::assertSame(DevisFormTypeEnum::commerce, DevisFormTypeEnum::tryFrom('commerce'));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        self::assertNull(DevisFormTypeEnum::tryFrom('inexistant'));
    }

    public function testTryFromIsCaseSensitive(): void
    {
        self::assertNull(DevisFormTypeEnum::tryFrom('WebApp'));
    }
}
