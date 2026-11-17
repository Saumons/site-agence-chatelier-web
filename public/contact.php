<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Env;
use App\Forms\ContactForm;

Env::load(__DIR__ . '/../.env');

$pageTitle = 'Contact — SA Agence Web';
$active    = 'contact';

$errors  = [];
$old     = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = new ContactForm($_POST);

    if ($form->isValid()) {
        $data = $form->getRawData();

        $to      = Env::get('CONTACT_EMAIL_TO', 'contact@votre-agence.fr');
        $from    = Env::get('CONTACT_EMAIL_FROM', 'no-reply@votre-agence.fr');
        $subject = '[Contact SA] ' . $data['subject'];
        $body    = "Nom : {$data['name']}\n"
                 . "Email : {$data['email']}\n\n"
                 . "Message :\n{$data['message']}\n";
        $headers = "From: {$from}\r\nReply-To: {$data['email']}\r\nContent-Type: text/plain; charset=UTF-8";

        // NB : mail() nécessite un serveur de mail configuré (ex: en prod).
        // En local, @mail() échouera silencieusement ; le squelette considère
        // malgré tout l'envoi comme "tenté" pour la démo.
        @mail($to, $subject, $body, $headers);

        $success = true;
    } else {
        $errors = $form->getErrors();
        $old    = $form->getSafeData();
    }
}

require __DIR__ . '/../templates/header.php';
require __DIR__ . '/../templates/contact.php';
require __DIR__ . '/../templates/footer.php';
