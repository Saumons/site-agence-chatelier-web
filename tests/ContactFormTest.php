<?php

namespace Tests;

use App\Forms\ContactForm;
use PHPUnit\Framework\TestCase;

final class ContactFormTest extends TestCase
{
    public function testValidDataPassesValidation(): void
    {
        $form = new ContactForm([
            'name'    => 'Jean Dupont',
            'email'   => 'jean.dupont@example.com',
            'subject' => 'Demande de devis',
            'message' => 'Bonjour, je souhaite un devis pour un site vitrine.',
        ]);

        $this->assertTrue($form->isValid());
        $this->assertEmpty($form->getErrors());
    }

    public function testInvalidEmailFailsValidation(): void
    {
        $form = new ContactForm([
            'name'    => 'Jean Dupont',
            'email'   => 'pas-un-email',
            'subject' => 'Demande de devis',
            'message' => 'Bonjour, je souhaite un devis pour un site vitrine.',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertArrayHasKey('email', $form->getErrors());
    }

    public function testHoneypotFilledIsRejectedAsSpam(): void
    {
        $form = new ContactForm([
            'name'    => 'Bot',
            'email'   => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Ceci est un message de spam automatisé.',
            'website' => 'http://spam.example.com', // honeypot rempli
        ]);

        $this->assertFalse($form->isValid());
        $this->assertArrayHasKey('spam', $form->getErrors());
    }

    public function testEmptyMessageFailsValidation(): void
    {
        $form = new ContactForm([
            'name'    => 'Jean Dupont',
            'email'   => 'jean.dupont@example.com',
            'subject' => 'Demande de devis',
            'message' => 'Trop court',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertArrayHasKey('message', $form->getErrors());
    }
}
