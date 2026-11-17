# SA — Site vitrine (squelette de démo)

Squelette fonctionnel de site vitrine (2 pages placeholder) pour une agence web,
en **HTML / SCSS / JS / PHP** (PHP utilisé uniquement pour le formulaire de
contact), avec autoload **PSR-4**.

## Arborescence

```
SA/
├── public/              ← racine web (document root à pointer sur un vhost/Apache)
│   ├── index.php        ← page d'accueil
│   ├── contact.php      ← page + traitement du formulaire de contact
│   ├── .htaccess
│   └── assets/
│       ├── css/style.css   ← généré depuis scss/ (ne pas éditer à la main)
│       ├── js/main.js
│       └── images/
├── src/                 ← code PHP non accessible publiquement
│   ├── BDD/Database.php     ← connexion PDO (prête, non utilisée par défaut)
│   ├── Forms/ContactForm.php ← validation du formulaire
│   └── Env.php              ← chargeur de .env
├── templates/           ← vues PHP (header/footer/home/contact)
├── scss/                ← sources SCSS (partials)
├── vendor/              ← autoload PSR-4 (voir note ci-dessous)
├── tests/               ← tests PHPUnit (namespace Tests\)
├── .env / .env.example
├── composer.json        ← autoload PSR-4 : App\ → src/, Tests\ → tests/
└── package.json         ← compilation SCSS
```

## Installation

### 1. Dépendances JS (compilation SCSS)

```bash
npm install
npm run build:css     # compile scss/style.scss → public/assets/css/style.css
npm run watch:css      # recompile automatiquement pendant le dev
```

### 2. Dépendances PHP (autoload / tests)

```bash
composer install
```

> **Note** : cet environnement de génération n'avait pas accès à Packagist.
> Un `vendor/autoload.php` **autoloader PSR-4 fait main** est donc fourni
> (mapping `App\` → `src/` et `Tests\` → `tests/`), pour que le site
> fonctionne tout de suite sans dépendance externe. Dès que vous lancez
> `composer install` dans un environnement avec accès internet, Composer
> régénère proprement ce fichier et installe `phpunit/phpunit` (listé en
> `require-dev`) — rien ne casse, les mêmes namespaces sont utilisés.

### 3. Lancer le site en local

```bash
php -S 127.0.0.1:8000 -t public
```

Puis ouvrir http://127.0.0.1:8000/index.php et http://127.0.0.1:8000/contact.php

### 4. Lancer les tests

```bash
composer test
# ou directement :
vendor/bin/phpunit
```

## Formulaire de contact

- Validation côté serveur dans `src/Forms/ContactForm.php` (nom, email, sujet,
  message + champ honeypot anti-spam caché en CSS).
- `public/contact.php` traite le POST, réaffiche les erreurs/valeurs saisies,
  et envoie un email via `mail()` en cas de succès.
- En local, `mail()` échoue silencieusement sans serveur SMTP configuré
  (c'est normal) : le message "envoyé avec succès" s'affiche quand même pour
  la démo. En production, configurez `sendmail`/SMTP, ou remplacez l'appel
  `mail()` par un enregistrement en base via `src/BDD/Database.php`.

## À personnaliser avant mise en prod

- Contenu réel des pages (`templates/home.php`, `templates/contact.php`)
- Variables SCSS (`scss/_variables.scss`) : couleurs, typographie
- `.env` : adresses email, identifiants BDD
- Ajout d'un vrai middleware CSRF si besoin (non inclus dans ce squelette)
