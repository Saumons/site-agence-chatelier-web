<?php

declare(strict_types=1);

namespace Tests\unit\entity;

use App\BDD\entity\PersonneEntity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité PersonneEntity.
 *
 * Couvre le constructeur, les accesseurs, la validation de l'email,
 * du nom, du prénom (optionnel, longueur seulement), la
 * normalisation/validation du numéro de téléphone (optionnel,
 * converti au format +33 en interne puis reformaté à l'affichage)
 * et le booléen estMorale.
 */
final class PersonneEntityTest extends TestCase
{
    public function testConstructorCreatesEntityWithExpectedValues(): void
    {
        $personne = new PersonneEntity(
            mail: 'jean.dupont@example.com',
            nom: 'Dupont',
            prenom: 'Jean',
            tel: '0612345678',
            estMorale: false
        );

        self::assertSame('jean.dupont@example.com', $personne->getMail());
        self::assertSame('Dupont', $personne->getNom());
        self::assertSame('Jean', $personne->getPrenom());
        self::assertSame('06 12 34 56 78', $personne->getFormattedTel());
        self::assertSame('+33612345678', $personne->getTel());
        self::assertFalse($personne->isEstMorale());
    }

    public function testConstructorAllowsNullPrenomAndNullTel(): void
    {
        $personne = new PersonneEntity(
            mail: 'societe@example.com',
            nom: 'ACME',
            prenom: null,
            tel: null,
            estMorale: true
        );

        self::assertNull($personne->getPrenom());
        self::assertNull($personne->getTel());
        self::assertTrue($personne->isEstMorale());
    }

    public function testConstructorThrowsWhenMailIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: 'pas-un-email', nom: 'Dupont', prenom: null, tel: null, estMorale: false);
    }

    public function testConstructorThrowsWhenMailExceedsMaxLength(): void
    {
        // Email syntaxiquement valide (local part <= 64 car., label de domaine <= 63 car.)
        // mais dont la longueur totale dépasse les 100 caractères autorisés.
        $local = str_repeat('a', 60);
        $domaine = str_repeat('b', 45) . '.com';
        $mail = "{$local}@{$domaine}";

        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: $mail, nom: 'Dupont', prenom: null, tel: null, estMorale: false);
    }

    public function testConstructorThrowsWhenNomIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: 'jean@example.com', nom: '', prenom: null, tel: null, estMorale: false);
    }

    public function testConstructorThrowsWhenNomIsOnlyWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: 'jean@example.com', nom: '   ', prenom: null, tel: null, estMorale: false);
    }

    public function testConstructorAcceptsNomAtMaxLengthBoundary(): void
    {
        $nom50 = str_repeat('a', 50);

        $personne = new PersonneEntity(mail: 'jean@example.com', nom: $nom50, prenom: null, tel: null, estMorale: false);

        self::assertSame($nom50, $personne->getNom());
    }

    public function testConstructorThrowsWhenNomExceedsMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: 'jean@example.com', nom: str_repeat('a', 51), prenom: null, tel: null, estMorale: false);
    }

    public function testConstructorAcceptsEmptyStringPrenom(): void
    {
        // assertPrenomIsValid ne vérifie que la longueur (pas la vacuité, contrairement à
        // nom/objet/message) : une chaîne vide est donc une valeur valide pour prenom.
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: '', tel: null, estMorale: false);

        self::assertSame('', $personne->getPrenom());
    }

    public function testConstructorAcceptsPrenomAtMaxLengthBoundary(): void
    {
        $prenom50 = str_repeat('a', 50);

        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: $prenom50, tel: null, estMorale: false);

        self::assertSame($prenom50, $personne->getPrenom());
    }

    public function testConstructorThrowsWhenPrenomExceedsMaxLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: str_repeat('a', 51), tel: null, estMorale: false);
    }

    public function testConstructorNormalizesLocalPhoneNumberToPlus33Format(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: '0612345678', estMorale: false);

        self::assertSame('+33612345678', $personne->getTel());
        self::assertSame('06 12 34 56 78', $personne->getFormattedTel());
    }

    public function testConstructorAcceptsPhoneNumberAlreadyInPlus33Format(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: '+33612345678', estMorale: false);

        self::assertSame('06 12 34 56 78', $personne->getFormattedTel());
        self::assertSame('+33612345678', $personne->getTel());
    }

    public function testConstructorStripsSpacesFromPhoneNumberBeforeValidating(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: '06 12 34 56 78', estMorale: false);

        self::assertSame('06 12 34 56 78', $personne->getFormattedTel());
        self::assertSame('+33612345678', $personne->getTel());
    }

    public function testConstructorThrowsWhenPhoneNumberHasInvalidFormat(): void
    {
        // Le 2e chiffre doit être dans [1-9] : "00..." est donc rejeté par la regex.
        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: '0012345678', estMorale: false);
    }

    public function testConstructorThrowsWhenPhoneNumberIsTooShort(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: '0612345', estMorale: false);
    }

    public function testSetMailUpdatesValueWhenValid(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: null, estMorale: false);

        $personne->setMail('nouveau@example.com');

        self::assertSame('nouveau@example.com', $personne->getMail());
    }

    public function testSetMailThrowsWhenInvalid(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: null, estMorale: false);

        $this->expectException(InvalidArgumentException::class);

        $personne->setMail('invalide');
    }

    public function testSetNomUpdatesValueWhenValid(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: null, estMorale: false);

        $personne->setNom('Durand');

        self::assertSame('Durand', $personne->getNom());
    }

    public function testSetNomThrowsWhenEmpty(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: null, estMorale: false);

        $this->expectException(InvalidArgumentException::class);

        $personne->setNom('   ');
    }

    public function testSetPrenomAcceptsNullToClearValue(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: 'Jean', tel: null, estMorale: false);

        $personne->setPrenom(null);

        self::assertNull($personne->getPrenom());
    }

    public function testSetPrenomThrowsWhenExceedsMaxLength(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: 'Jean', tel: null, estMorale: false);

        $this->expectException(InvalidArgumentException::class);

        $personne->setPrenom(str_repeat('a', 51));
    }

    public function testSetTelAcceptsNullToClearValue(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: '0612345678', estMorale: false);

        $personne->setTel(null);

        self::assertNull($personne->getTel());
    }

    public function testSetTelNormalizesAndUpdatesValue(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: null, estMorale: false);

        $personne->setTel('07 98 76 54 32');

        self::assertSame('07 98 76 54 32', $personne->getFormattedTel());
        self::assertSame('+33798765432', $personne->getTel());
    }

    public function testSetTelThrowsWhenInvalid(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: null, estMorale: false);

        $this->expectException(InvalidArgumentException::class);

        $personne->setTel('abcdefghij');
    }

    public function testSetEstMoraleUpdatesValue(): void
    {
        $personne = new PersonneEntity(mail: 'jean@example.com', nom: 'Dupont', prenom: null, tel: null, estMorale: false);

        $personne->setEstMorale(true);

        self::assertTrue($personne->isEstMorale());
    }
}
