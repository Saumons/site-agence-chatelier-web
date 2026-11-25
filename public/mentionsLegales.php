<?php

require __DIR__ . '/../config.php';

$pageTitle = "Mentions légales - Chat'elier Web";

require __DIR__ . '/../templates/header.php';

?>
    <main class="legal legal--mentions">
        <div class="legal__container">
            <h1 class="legal__title">Mentions légales</h1>

            <section class="legal__section legal__section--publisher">
                <h2 class="legal__heading">1. Éditeur du site</h2>
                <p class="legal__text">Le présent site est édité par la société Chat'elier Web, Société à Responsabilité Limitée (SARL) au capital social de 4 050 €, entièrement constitué d'apports en nature.</p>
                <dl class="legal__definitions">
                    <dt class="legal__term">Raison sociale</dt>
                    <dd class="legal__description">Chat'elier Web</dd>
                    <dt class="legal__term">Forme juridique</dt>
                    <dd class="legal__description">SARL</dd>
                    <dt class="legal__term">Capital social</dt>
                    <dd class="legal__description">4 050 €</dd>
                    <dt class="legal__term">Siège social</dt>
                    <dd class="legal__description">44 Avenue Albert Einstein, 17000 La Rochelle</dd>
                    <dt class="legal__term">RCS / SIREN</dt>
                    <dd class="legal__description">Aucun (agence fictive)</dd>
                    <dt class="legal__term">SIRET</dt>
                    <dd class="legal__description">Aucun (agence fictive)</dd>
                    <dt class="legal__term">N° TVA intracommunautaire</dt>
                    <dd class="legal__description">Aucun (agence fictive)</dd>
                    <dt class="legal__term">Gérance</dt>
                    <dd class="legal__description">Dorian Adam, Clovis Jouanneau, Eydan Gannot et Raphaël Girard, associés cogérants</dd>
                    <dt class="legal__term">Activités</dt>
                    <dd class="legal__description">développement d'applications sur-mesure et conseil en stratégie digitale</dd>
                    <dt class="legal__term">Email</dt>
                    <dd class="legal__description"><a class="legal__link" href="mailto:chatelier-web@outlook.fr">chatelier-web@outlook.fr</a></dd>
                </dl>
            </section>

            <section class="legal__section legal__section--director">
                <h2 class="legal__heading">2. Directeur de la publication</h2>
                <p class="legal__text">Dorian ADAM, en qualité de gérant de la société Chat'elier Web.</p>
            </section>

            <section class="legal__section legal__section--hosting">
                <h2 class="legal__heading">3. Hébergement du site</h2>
                <p class="legal__text">Le site est hébergé par :</p>
                <dl class="legal__definitions">
                    <dt class="legal__term">Nom de l'hébergeur</dt>
                    <dd class="legal__description">LP-MIAW</dd>
                    <dt class="legal__term">Adresse</dt>
                    <dd class="legal__description">44 avenue Albert Einstein</dd>
                </dl>
            </section>

            <section class="legal__section legal__section--ip">
                <h2 class="legal__heading">4. Propriété intellectuelle</h2>
                <p class="legal__text">L'ensemble des contenus présents sur ce site (textes, images, graphismes, logo, icônes, structure) est la propriété exclusive de Chat'elier Web, sauf mention contraire, et est protégé par les dispositions du Code de la propriété intellectuelle. Toute reproduction, représentation, modification, publication ou adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite sans autorisation écrite préalable.</p>
            </section>

            <section class="legal__section legal__section--data">
                <h2 class="legal__heading">5. Protection des données personnelles (RGPD)</h2>
                <p class="legal__text">Conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi Informatique et Libertés du 6 janvier 1978 modifiée, vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation, d'opposition et de portabilité concernant vos données personnelles.</p>
                <p class="legal__text">Les informations collectées via ce site (notamment via le formulaire de contact) sont destinées exclusivement à Chat'elier Web et ne sont ni cédées ni vendues à des tiers. Elles sont conservées pendant une durée n'excédant pas celle nécessaire aux finalités pour lesquelles elles ont été collectées.</p>
                <p class="legal__text">Pour exercer vos droits, vous pouvez nous contacter à l'adresse : <a class="legal__link" href="mailto:chatelier-web@outlook.fr">chatelier-web@outlook.fr</a>.</p>
            </section>

            <section class="legal__section legal__section--cookies">
                <h2 class="legal__heading">6. Cookies</h2>
                <p class="legal__text">Le site peut utiliser des cookies nécessaires à son bon fonctionnement et, le cas échéant, des cookies de mesure d'audience. Vous pouvez configurer votre navigateur pour refuser l'installation de cookies. <span class="legal__todo">[à compléter : donner les outils utilisés]</span></p>
            </section>

            <section class="legal__section legal__section--liability">
                <h2 class="legal__heading">7. Limitation de responsabilité</h2>
                <p class="legal__text">Chat'elier Web s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site, mais ne peut garantir l'exhaustivité ou l'absence d'erreurs. Chat'elier Web ne saurait être tenue responsable des dommages directs ou indirects résultant de l'accès ou de l'utilisation du site.</p>
            </section>

            <section class="legal__section legal__section--law">
                <h2 class="legal__heading">8. Droit applicable</h2>
                <p class="legal__text">Les présentes mentions légales sont soumises au droit français. Tout litige relatif à leur interprétation ou leur exécution relève de la compétence des tribunaux du ressort de La Rochelle.</p>
            </section>

            <section class="legal__section legal__section--contact">
                <h2 class="legal__heading">9. Contact</h2>
                <p class="legal__text">Pour toute question relative aux présentes mentions légales, vous pouvez nous contacter :</p>
                <ul class="legal__list">
                    <li class="legal__item">Par email : <a class="legal__link" href="mailto:chatelier-web@outlook.fr">chatelier-web@outlook.fr</a></li>
                    <li class="legal__item">Par courrier : Chat'elier Web, 44 Avenue Albert Einstein, 17000 La Rochelle</li>
                </ul>
            </section>
        </div>
    </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>