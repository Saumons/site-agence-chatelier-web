<?php

declare(strict_types=1);

namespace Tests\Unit\enums;

use App\enums\OptionTypeEnum;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Tests unitaires de l'énum OptionTypeEnum.
 *
 * Couvre la valeur de chaque case, le contenu et l'ordre de cases(),
 * le comportement de from()/tryFrom() avec des valeurs valides et
 * invalides, ainsi que la sensibilité à la casse des valeurs.
 */
final class OptionTypeEnumTest extends TestCase
{
    public function testWebAppCaseHasExpectedValue(): void
    {
        self::assertSame('webApp', OptionTypeEnum::webApp->value);
    }

    public function testCommerceCaseHasExpectedValue(): void
    {
        self::assertSame('commerce', OptionTypeEnum::commerce->value);
    }

    public function testVitrineCaseHasExpectedValue(): void
    {
        self::assertSame('vitrine', OptionTypeEnum::vitrine->value);
    }

    public function testTransCaseHasExpectedValue(): void
    {
        self::assertSame('transversal', OptionTypeEnum::trans->value);
    }

    public function testCasesReturnsAllExpectedCases(): void
    {
        self::assertSame(
            [
                OptionTypeEnum::webApp,
                OptionTypeEnum::commerce,
                OptionTypeEnum::vitrine,
                OptionTypeEnum::trans,
            ],
            OptionTypeEnum::cases()
        );
    }

    public function testCasesReturnsExactlyFourCases(): void
    {
        self::assertSame(4, count(OptionTypeEnum::cases()));
    }

    public function testCasesAreReturnedInDeclarationOrder(): void
    {
        $cases = OptionTypeEnum::cases();

        self::assertSame(OptionTypeEnum::webApp, $cases[0]);
        self::assertSame(OptionTypeEnum::commerce, $cases[1]);
        self::assertSame(OptionTypeEnum::vitrine, $cases[2]);
        self::assertSame(OptionTypeEnum::trans, $cases[3]);
    }

    public function testFromReturnsWebAppForValidValue(): void
    {
        self::assertSame(OptionTypeEnum::webApp, OptionTypeEnum::from('webApp'));
    }

    public function testFromReturnsCommerceForValidValue(): void
    {
        self::assertSame(OptionTypeEnum::commerce, OptionTypeEnum::from('commerce'));
    }

    public function testFromReturnsVitrineForValidValue(): void
    {
        self::assertSame(OptionTypeEnum::vitrine, OptionTypeEnum::from('vitrine'));
    }

    public function testFromReturnsTransForValidValue(): void
    {
        self::assertSame(OptionTypeEnum::trans, OptionTypeEnum::from('transversal'));
    }

    public function testFromThrowsWhenValueIsInvalid(): void
    {
        $this->expectException(ValueError::class);

        OptionTypeEnum::from('invalide');
    }

    public function testTryFromReturnsWebAppForValidValue(): void
    {
        self::assertSame(OptionTypeEnum::webApp, OptionTypeEnum::tryFrom('webApp'));
    }

    public function testTryFromReturnsTransForValidValue(): void
    {
        self::assertSame(OptionTypeEnum::trans, OptionTypeEnum::tryFrom('transversal'));
    }

    public function testTryFromReturnsNullWhenValueIsInvalid(): void
    {
        self::assertNull(OptionTypeEnum::tryFrom('invalide'));
    }

    public function testTryFromIsCaseSensitiveForWebApp(): void
    {
        self::assertNull(OptionTypeEnum::tryFrom('WebApp'));
    }

    public function testTryFromIsCaseSensitiveForTrans(): void
    {
        self::assertNull(OptionTypeEnum::tryFrom('Transversal'));
    }

    public function testFromIsCaseSensitiveAndThrowsForWrongCase(): void
    {
        $this->expectException(ValueError::class);

        OptionTypeEnum::from('COMMERCE');
    }
}