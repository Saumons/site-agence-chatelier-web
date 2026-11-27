<?php

require __DIR__ . '/../config.php';

$pageTitle = "Politique de confidentialité";

require __DIR__ . '/../templates/header.php';

?>


<main class="legal legal--privacy">
    <div class="legal__container">
        <h1 class="legal__title">Politique de confidentialité</h1>
        <p class="legal__updated">Dernière mise à jour : 29 Septembre 2026</p>

        <p class="legal__text legal__text--lead">Chat'elier Web attache une grande importance à la protection de vos données personnelles. La présente politique de confidentialité a pour objectif de vous informer, conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi Informatique et Libertés, sur la manière dont vos données sont collectées, utilisées et protégées lorsque vous naviguez sur notre site.</p>

        <section class="legal__section legal__section--controller">
            <h2 class="legal__heading">1. Responsable du traitement</h2>
            <p class="legal__text">Le responsable du traitement des données collectées sur ce site est :</p>
            <dl class="legal__definitions">
                <dt class="legal__term">Société</dt>
                <dd class="legal__description">Chat'elier Web, SARL au capital de 4 050 euros</dd>
                <dt class="legal__term">Siège social</dt>
                <dd class="legal__description">44 Avenue Albert Einstein, 17000 La Rochelle</dd>
                <dt class="legal__term">Contact</dt>
                <dd class="legal__description"><a class="legal__link" href="mailto:chatelier-web@outlook.fr">chatelier-web@outlook.fr</a></dd>
            </dl>
        </section>

        <section class="legal__section legal__section--data">
            <h2 class="legal__heading">2. Données collectées</h2>
            <p class="legal__text">Selon votre utilisation du site, nous pouvons collecter les données suivantes :</p>
            <ul class="legal__list">
                <li class="legal__item">Nom et prénom, via le formulaire de contact, pour répondre à votre demande.</li>
                <li class="legal__item">Adresse email, via le formulaire de contact, pour vous recontacter.</li>
                <li class="legal__item">Numéro de téléphone, via le formulaire de contact si demandé, pour vous recontacter.</li>
                <li class="legal__item">Message ou demande, via le formulaire de contact, pour traiter votre demande.</li>
                <li class="legal__item">Données de navigation (pages visitées, durée, type d'appareil), via les cookies et outils de mesure d'audience, à des fins de statistiques de fréquentation.</li>
            </ul>
            <p class="legal__text">Nous ne collectons aucune donnée sensible (origine, santé, opinions politiques, etc.) et ne procédons à aucune prise de décision automatisée ou profilage.</p>
        </section>

        <section class="legal__section legal__section--legal-basis">
            <h2 class="legal__heading">3. Base légale du traitement</h2>
            <p class="legal__text">Le traitement de vos données repose selon les cas sur :</p>
            <ul class="legal__list">
                <li class="legal__item">Votre consentement, lorsque vous remplissez un formulaire de contact ou acceptez les cookies non essentiels.</li>
                <li class="legal__item">L'exécution de mesures précontractuelles, lorsque vous nous sollicitez pour un devis ou un projet.</li>
                <li class="legal__item">L'intérêt légitime de Chat'elier Web à améliorer son site via des statistiques anonymisées.</li>
            </ul>
        </section>

        <section class="legal__section legal__section--retention">
            <h2 class="legal__heading">4. Durée de conservation</h2>
            <p class="legal__text">Les données transmises via le formulaire de contact sont conservées 3 ans à compter du dernier échange, sauf entrée en relation commerciale, auquel cas la durée applicable à la relation contractuelle et comptable s'applique.</p>
            <p class="legal__text">Les données de navigation collectées via les cookies sont conservées 13 mois maximum, conformément aux recommandations de la CNIL.</p>
        </section>

        <section class="legal__section legal__section--recipients">
            <h2 class="legal__heading">5. Destinataires des données</h2>
            <p class="legal__text">Vos données sont destinées exclusivement à Chat'elier Web et, le cas échéant, à ses sous-traitants techniques nécessaires au fonctionnement du site (hébergeur, outil de mesure d'audience). Elles ne sont ni vendues, ni louées, ni cédées à des tiers à des fins commerciales.</p>
            <p class="legal__text">Sous-traitants et outils tiers utilisés : <span class="legal__todo">[à compléter : hébergeur, Google Analytics, outil d'emailing, CRM, en précisant s'ils sont situés hors Union européenne]</span></p>
        </section>

        <section class="legal__section legal__section--cookies">
            <h2 class="legal__heading">6. Cookies</h2>
            <p class="legal__text">Le site utilise des cookies, notamment :</p>
            <ul class="legal__list">
                <li class="legal__item">Des cookies strictement nécessaires au fonctionnement du site, qui ne nécessitent pas de consentement.</li>
                <li class="legal__item">Des cookies de mesure d'audience <span class="legal__todo">[à compléter : préciser ceux utilisés]</span>, déposés uniquement après votre consentement.</li>
            </ul>
            <p class="legal__text">Vous pouvez à tout moment modifier vos préférences via le bandeau de gestion des cookies ou les paramètres de votre navigateur.</p>
        </section>

        <section class="legal__section legal__section--rights">
            <h2 class="legal__heading">7. Vos droits</h2>
            <p class="legal__text">Conformément au RGPD, vous disposez des droits suivants sur vos données personnelles :</p>
            <ul class="legal__list">
                <li class="legal__item">Droit d'accès : obtenir une copie des données que nous détenons sur vous.</li>
                <li class="legal__item">Droit de rectification : corriger des données inexactes ou incomplètes.</li>
                <li class="legal__item">Droit à l'effacement : demander la suppression de vos données.</li>
                <li class="legal__item">Droit à la limitation du traitement.</li>
                <li class="legal__item">Droit d'opposition, notamment à des fins de prospection.</li>
                <li class="legal__item">Droit à la portabilité de vos données.</li>
            </ul>
            <p class="legal__text">Pour exercer ces droits, vous pouvez nous contacter à l'adresse suivante : <a class="legal__link" href="mailto:chatelier-web@outlook.fr">chatelier-web@outlook.fr</a>, en précisant votre nom, prénom et l'objet de votre demande. Une réponse vous sera apportée dans un délai maximum d'un mois.</p>
            <p class="legal__text">Vous disposez également du droit d'introduire une réclamation auprès de la CNIL (Commission Nationale de l'Informatique et des Libertés), sur <a class="legal__link" href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>.</p>
        </section>

        <section class="legal__section legal__section--security">
            <h2 class="legal__heading">8. Sécurité des données</h2>
            <p class="legal__text">Chat'elier Web met en œuvre les mesures techniques et organisationnelles appropriées afin de garantir la sécurité et la confidentialité de vos données personnelles, et d'empêcher qu'elles ne soient déformées, endommagées ou communiquées à des tiers non autorisés.</p>
        </section>

        <section class="legal__section legal__section--changes">
            <h2 class="legal__heading">9. Modification de la politique de confidentialité</h2>
            <p class="legal__text">Chat'elier Web se réserve le droit de modifier la présente politique de confidentialité à tout moment, notamment pour se conformer à toute évolution réglementaire, jurisprudentielle ou technique. Nous vous invitons à la consulter régulièrement.</p>
        </section>

        <section class="legal__section legal__section--contact">
            <h2 class="legal__heading">10. Contact</h2>
            <p class="legal__text">Pour toute question relative à cette politique de confidentialité ou à l'exercice de vos droits :</p>
            <ul class="legal__list">
                <li class="legal__item">Par email : <a class="legal__link" href="mailto:chatelier-web@outlook.fr">chatelier-web@outlook.fr</a></li>
                <li class="legal__item">Par courrier : Chat'elier Web, 44 Avenue Albert Einstein, 17000 La Rochelle</li>
            </ul>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
