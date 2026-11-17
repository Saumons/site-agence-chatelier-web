<?php

namespace App\Forms;

/**
 * Validation + assainissement des données du formulaire de contact.
 */
final class ContactForm
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $post)
    {
        $this->data = [
            'name'    => trim((string) ($post['name'] ?? '')),
            'email'   => trim((string) ($post['email'] ?? '')),
            'subject' => trim((string) ($post['subject'] ?? '')),
            'message' => trim((string) ($post['message'] ?? '')),
            // honeypot anti-spam : doit rester vide
            'website' => trim((string) ($post['website'] ?? '')),
        ];
    }

    public function isValid(): bool
    {
        $this->errors = [];

        if ($this->data['website'] !== '') {
            // Rempli par un bot : on rejette silencieusement.
            $this->errors['spam'] = 'Requête invalide.';
            return false;
        }

        if ($this->data['name'] === '' || mb_strlen($this->data['name']) < 2) {
            $this->errors['name'] = 'Merci d\'indiquer votre nom.';
        }

        if ($this->data['email'] === '' || !filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Merci d\'indiquer une adresse email valide.';
        }

        if ($this->data['subject'] === '') {
            $this->errors['subject'] = 'Merci d\'indiquer un sujet.';
        }

        if ($this->data['message'] === '' || mb_strlen($this->data['message']) < 10) {
            $this->errors['message'] = 'Votre message doit contenir au moins 10 caractères.';
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /** Données assainies pour affichage (echo) sans risque XSS. */
    public function getSafeData(): array
    {
        return array_map(
            static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            $this->data
        );
    }

    public function getRawData(): array
    {
        return $this->data;
    }
}
