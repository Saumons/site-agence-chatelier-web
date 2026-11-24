<?php
// require et paramétrages
use App\BDD\DbConnection;
use App\BDD\DbManager;

require __DIR__ . '/../config.php';
require __DIR__ . '/../src/BDD/dbManager.php';
$pageTitle = "Formulaire de devis";
require __DIR__ . '/../templates/header.php';

// Types de projets proposés : utilisés pour l'affichage sans JS
// (progressive enhancement) et pour la validation du paramètre ?tab=.
// Les clés sont alignées avec devis.js et _devis.scss (BEM: quote__tab--<type>).
$projectTypes = ['vitrine', 'ecommerce', 'webapp'];

$projectLabels = [
        'vitrine' => 'Site vitrine',
        'ecommerce' => 'Site e-commerce',
        'webapp' => 'Application web',
];

$activeType = isset($_GET['tab']) && in_array($_GET['tab'], $projectTypes, true)
        ? $_GET['tab']
        : 'vitrine';
?>
<img class="page-background page-background--devis" src="./assets/images/svg/fonds/nosServices.svg" alt="">

<main class="main">
    <header class="main__header">
        <h1 class="main__title">Demande de devis</h1>
        <p class="main__intro">
            Décrivez votre projet en quelques clics&nbsp;: choisissez le type de site, cochez les options qui vous
            intéressent, et obtenez immédiatement une première estimation de sa complexité. Notre équipe affine
            ensuite ce chiffrage et revient vers vous sous 72h ouvrées.
        </p>
    </header>

    <section class="main__section">
        <form class="quote" id="quote-form" action="../src/Forms/devisForm.php" method="post" novalidate>
            <div class="quote__layout">

                <div class="quote__form">

                    <!-- Étape 1 : type de projet -->
                    <fieldset class="quote__step" id="step-type">
                        <legend class="quote__step-title">
                            <span class="quote__step-number" aria-hidden="true">1</span>
                            Quel type de projet souhaitez-vous&nbsp;?
                        </legend>

                        <div class="quote__tabs" role="tablist" aria-label="Type de projet">
                            <?php foreach ($projectTypes as $type) : ?>
                                <button
                                        type="button"
                                        class="quote__tab quote__tab--<?php echo htmlspecialchars($type); ?><?php echo $activeType === $type ? ' quote__tab--active' : ''; ?>"
                                        data-type="<?php echo htmlspecialchars($type); ?>"
                                        role="tab"
                                        id="tab-btn-<?php echo htmlspecialchars($type); ?>"
                                        aria-controls="panel-<?php echo htmlspecialchars($type); ?>"
                                        aria-selected="<?php echo $activeType === $type ? 'true' : 'false'; ?>"
                                >
                                    <?php echo htmlspecialchars($projectLabels[$type]); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Valeur réellement soumise au backend -->
                        <input type="hidden" name="project_type" id="project-type-input"
                               value="<?php echo htmlspecialchars($activeType); ?>">
                    </fieldset>

                    <!-- Étape 2 : options spécifiques au type de projet -->
                    <fieldset class="quote__step" id="step-specific">
                        <legend class="quote__step-title">
                            <span class="quote__step-number" aria-hidden="true">2</span>
                            Options spécifiques à votre projet
                        </legend>

                        <?php foreach ($projectTypes as $type) : ?>
                            <div
                                    class="quote__panel"
                                    id="panel-<?php echo htmlspecialchars($type); ?>"
                                    role="tabpanel"
                                    aria-labelledby="tab-btn-<?php echo htmlspecialchars($type); ?>"
                                    <?php echo $activeType === $type ? '' : 'hidden'; ?>
                            >
                                <!-- Contenu injecté par devis.js. Fallback si JS désactivé : -->
                                <noscript>
                                    <p class="quote__noscript-note">
                                        JavaScript est désactivé&nbsp;: merci de préciser vos besoins pour
                                        « <?php echo htmlspecialchars($projectLabels[$type]); ?> » directement dans le
                                        champ « Informations complémentaires » ci-dessous.
                                    </p>
                                </noscript>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>

                    <!-- Étape 3 : options transversales -->
                    <fieldset class="quote__step" id="step-common">
                        <legend class="quote__step-title">
                            <span class="quote__step-number" aria-hidden="true">3</span>
                            Options transversales
                        </legend>
                        <div class="quote__options" id="common-options">
                            <!-- Injecté par devis.js -->
                        </div>
                    </fieldset>

                    <!-- Étape 4 : coordonnées -->
                    <fieldset class="quote__step" id="step-contact">
                        <legend class="quote__step-title">
                            <span class="quote__step-number" aria-hidden="true">4</span>
                            Vos coordonnées
                        </legend>

                        <!-- Sélecteur du type de demandeur -->
                        <fieldset class="quote__contact-type">
                            <legend class="quote__contact-type-legend">
                                Je fais cette demande en tant que&nbsp;:
                            </legend>

                            <!-- Sans JS, le formulaire reste utilisable en mode "Particulier" (valeur par défaut).
                                 Pour une demande "Entreprise", merci de préciser votre structure dans le champ
                                 "Informations complémentaires" ci-dessous. -->
                            <noscript>
                                <p class="quote__noscript-note">
                                    JavaScript est désactivé&nbsp;: le formulaire reste configuré pour une demande
                                    « Particulier ». Si vous représentez une entreprise ou une organisation, merci de
                                    l'indiquer dans le champ « Informations complémentaires ».
                                </p>
                            </noscript>

                            <div class="quote__contact-type-options">
                                <label class="quote__contact-type-option" for="contact-type-particulier">
                                    <input
                                            type="radio"
                                            id="contact-type-particulier"
                                            name="contact_type"
                                            value="particulier"
                                            class="quote__contact-type-input"
                                            checked
                                    >
                                    <span class="quote__contact-type-content">
                    <span class="quote__contact-type-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
                             stroke-width="1.8">
                            <circle cx="12" cy="8" r="3.5"/>
                            <path d="M4.5 20c1.2-4 4-6 7.5-6s6.3 2 7.5 6"/>
                        </svg>
                    </span>
                    <span class="quote__contact-type-text">
                        <span class="quote__contact-type-title">Particulier</span>
                        <span class="quote__contact-type-desc">Vous faites cette demande en votre nom propre</span>
                    </span>
                </span>
                                </label>

                                <label class="quote__contact-type-option" for="contact-type-entreprise">
                                    <input
                                            type="radio"
                                            id="contact-type-entreprise"
                                            name="contact_type"
                                            value="entreprise"
                                            class="quote__contact-type-input"
                                    >
                                    <span class="quote__contact-type-content">
                    <span class="quote__contact-type-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor"
                             stroke-width="1.8">
                            <rect x="4" y="3" width="11" height="18" rx="1"/>
                            <path d="M8 7h3M8 11h3M8 15h3M15 10h5v11h-5z"/>
                        </svg>
                    </span>
                    <span class="quote__contact-type-text">
                        <span class="quote__contact-type-title">Entreprise / Organisation</span>
                        <span class="quote__contact-type-desc">Société, association, administration…</span>
                    </span>
                </span>
                                </label>
                            </div>
                        </fieldset>

                        <!-- Valeur simple à exploiter côté PHP : '1' = personne morale, '0' = personne physique.
                             Recalculée et revalidée côté serveur à partir de contact_type : voir devisForm.php. -->
                        <input type="hidden" name="est_morale" id="est-morale-input" value="0">

                        <div class="quote__field-grid">
                            <!-- Particulier -->
                            <div class="quote__field" data-contact-field data-contact-group="particulier">
                                <label for="lastname">Nom de famille <abbr title="champ requis">*</abbr></label>
                                <input type="text" id="lastname" name="lastname" autocomplete="family-name" required>
                            </div>

                            <div class="quote__field" data-contact-field data-contact-group="particulier">
                                <label for="firstname">Prénom <abbr title="champ requis">*</abbr></label>
                                <input type="text" id="firstname" name="firstname" autocomplete="given-name" required>
                            </div>

                            <!-- Entreprise / Organisation (masqué par défaut : "particulier" est le type initial) -->
                            <div class="quote__field" data-contact-field data-contact-group="entreprise" hidden>
                                <label for="company_name">Nom de l'entreprise ou de l'organisation <abbr
                                            title="champ requis">*</abbr></label>
                                <input type="text" id="company_name" name="company_name" autocomplete="organization"
                                       disabled>
                            </div>

                            <!-- Commun aux deux types -->
                            <div class="quote__field" data-contact-field>
                                <label for="email">Adresse e-mail <abbr title="champ requis">*</abbr></label>
                                <input type="email" id="email" name="email" autocomplete="email" required>
                            </div>

                            <div class="quote__field" data-contact-field>
                                <label for="phone">Téléphone</label>
                                <input type="tel" id="phone" name="phone" autocomplete="tel">
                            </div>

                            <!-- Particulier uniquement (un particulier peut vouloir mentionner une société) -->
                            <div class="quote__field" data-contact-field data-contact-group="particulier">
                                <label for="company">Société</label>
                                <input type="text" id="company" name="company" autocomplete="organization">
                            </div>
                        </div>

                        <div class="quote__field quote__field--full">
                            <label for="notes">Informations complémentaires</label>
                            <textarea
                                    id="notes"
                                    name="notes"
                                    rows="4"
                                    placeholder="Contenu déjà rédigé, délai souhaité, outils existants, contraintes particulières…"
                            ></textarea>
                        </div>
                    </fieldset>

                    <button type="submit" class="quote__submit">Envoyer ma demande de devis</button>
                </div>

                <!-- Résumé dynamique -->
                <aside class="quote__summary" id="quote-summary" aria-live="polite">
                    <h2 class="quote__summary-title">Votre projet en un coup d'œil</h2>

                    <p class="quote__summary-level">
                        Niveau estimé&nbsp;:
                        <strong id="summary-level" data-level="simple">Simple</strong>
                    </p>

                    <ul class="quote__summary-list" id="summary-list">
                        <li class="quote__summary-empty">Aucune option sélectionnée pour le moment</li>
                    </ul>

                    <p class="quote__summary-note">
                        Cette estimation est indicative et destinée à vous donner une vision claire de votre besoin.
                        Le chiffrage définitif vous sera communiqué par notre équipe après étude de votre demande.
                    </p>
                </aside>

            </div>
        </form>
    </section>

</main>

<?php require __DIR__ . '/../templates/footer.php'; ?>

