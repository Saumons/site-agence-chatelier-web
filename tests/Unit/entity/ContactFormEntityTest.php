<?php

declare(strict_types=1);

namespace Tests\Unit\entity;

use App\BDD\entity\ContactFormEntity;
use App\BDD\entity\PersonneEntity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité ContactFormEntity.
 *
 * Couvre le constructeur (cas valides et invalides), les accesseurs,
 * les bornes de longueur de l'objet (varchar(75), avec caractères
 * multi-octets) et la relation vers PersonneEntity.
 */
final class ContactFormEntityTest extends TestCase
{
    private PersonneEntity $personne;

    protected function setUp(): void
    {
        $this->personne = new PersonneEntity(
            mail: 'jean.dupont@example.com',
            nom: 'Dupont',
            prenom: 'Jean',
            tel: null,
            estMorale: false
        );
    }

    public function testConstructorCreatesEntityWithExpectedValues(): void
    {
        $contact = new ContactFormEntity(
            objet: 'Demande de devis',
            message: 'Bonjour, je souhaite un devis.',
            personne: $this->personne,
            idContact: 42
        );

        self::assertSame('Demande de devis', $contact->getObjet());
        self::assertSame('Bonjour, je souhaite un devis.', $contact->getMessage());
        self::assertSame($this->personne, $contact->getPersonne());
        self::assertSame(42, $contact->getIdContact());
    }

    public function testIdContactDefaultsToNullWhenNotProvided(): void
    {
        $contact = new ContactFormEntity(
            objet: 'Objet',
            message: 'Message',
            personne: $this->personne
        );

        self::assertNull($contact->getIdContact());
    }

    public function testConstructorThrowsWhenObjetIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ContactFormEntity(objet: '', message: 'Message valide', personne: $this->personne);
    }

    public function testConstructorThrowsWhenObjetIsOnlyWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ContactFormEntity(objet: '   ', message: 'Message valide', personne: $this->personne);
    }

    public function testConstructorThrowsWhenMessageIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ContactFormEntity(objet: 'Objet valide', message: '', personne: $this->personne);
    }

    public function testConstructorThrowsWhenMessageIsOnlyWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ContactFormEntity(objet: 'Objet valide', message: "   \n\t", personne: $this->personne);
    }

    public function testConstructorAcceptsObjetAtMaxLengthBoundary(): void
    {
        $objet75 = str_repeat('a', 75);

        $contact = new ContactFormEntity(objet: $objet75, message: 'Message', personne: $this->personne);

        self::assertSame($objet75, $contact->getObjet());
    }

    public function testConstructorThrowsWhenObjetExceedsMaxLength(): void
    {
        $objet76 = str_repeat('a', 76);

        $this->expectException(InvalidArgumentException::class);

        new ContactFormEntity(objet: $objet76, message: 'Message', personne: $this->personne);
    }

    public function testConstructorAcceptsMultiByteObjetAtExactCharacterBoundary(): void
    {
        // 75 caractères accentués : mb_strlen doit compter des caractères, pas des octets
        // (un "é" UTF-8 occupe 2 octets, strlen() aurait rejeté cette chaîne à tort).
        $objetAccentue = str_repeat('é', 75);

        $contact = new ContactFormEntity(objet: $objetAccentue, message: 'Message', personne: $this->personne);

        self::assertSame($objetAccentue, $contact->getObjet());
    }

    public function testSetObjetUpdatesValueWhenValid(): void
    {
        $contact = new ContactFormEntity(objet: 'Objet initial', message: 'Message', personne: $this->personne);

        $contact->setObjet('Nouvel objet');

        self::assertSame('Nouvel objet', $contact->getObjet());
    }

    public function testSetObjetThrowsWhenEmpty(): void
    {
        $contact = new ContactFormEntity(objet: 'Objet initial', message: 'Message', personne: $this->personne);

        $this->expectException(InvalidArgumentException::class);

        $contact->setObjet('');
    }

    public function testSetObjetThrowsWhenExceedsMaxLength(): void
    {
        $contact = new ContactFormEntity(objet: 'Objet initial', message: 'Message', personne: $this->personne);

        $this->expectException(InvalidArgumentException::class);

        $contact->setObjet(str_repeat('a', 76));
    }

    public function testSetMessageUpdatesValueWhenValid(): void
    {
        $contact = new ContactFormEntity(objet: 'Objet', message: 'Message initial', personne: $this->personne);

        $contact->setMessage('Nouveau message');

        self::assertSame('Nouveau message', $contact->getMessage());
    }

    public function testSetMessageThrowsWhenEmpty(): void
    {
        $contact = new ContactFormEntity(objet: 'Objet', message: 'Message initial', personne: $this->personne);

        $this->expectException(InvalidArgumentException::class);

        $contact->setMessage('   ');
    }

    public function testSetPersonneUpdatesRelation(): void
    {
        $contact = new ContactFormEntity(objet: 'Objet', message: 'Message', personne: $this->personne);

        $autrePersonne = new PersonneEntity(
            mail: 'autre.personne@example.com',
            nom: 'Martin',
            prenom: null,
            tel: null,
            estMorale: true
        );

        $contact->setPersonne($autrePersonne);

        self::assertSame($autrePersonne, $contact->getPersonne());
        self::assertNotSame($this->personne, $contact->getPersonne());
    }
}
