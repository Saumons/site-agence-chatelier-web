
import {
    PROJECT_TYPES,
    COMMON_OPTIONS,
    CONTACT_TYPES
} from './devis/config.js';

import {
    dom,
    getPanel
} from './devis/dom.js';

import {
    renderCommonOptions,
    renderSpecificOptions,
    renderTabs,
    renderSummary,
    renderContactType
} from './devis/renderer.js';

import { attachEvents } from './devis/event.js';

import {
    calculateProjectSummary
} from './devis/calculator.js';

/**
 * Initialise le simulateur de devis.
 *
 * @returns {void}
 */
const initQuoteSimulator = () => {
    if (!dom.form) {
        return;
    }

    let currentType = PROJECT_TYPES[dom.projectInput?.value]
        ? dom.projectInput.value
        : 'vitrine';

    // --- Ajout : état du type de demandeur ---
    let currentContactType = 'particulier';

    /**
     * Récupère les données actuellement présentes dans le formulaire.
     *
     * Les options sont recherchées exclusivement dans le panneau
     * actif et dans le conteneur des options communes.
     *
     * Les poids et les libellés proviennent de la configuration,
     * jamais des attributs HTML modifiables.
     *
     * @returns {Object}
     */
    const getCurrentProjectData = () => {
        const config = PROJECT_TYPES[currentType];

        const activePanel = getPanel(currentType);

        const checkedOptions = [];

        const optionConfigurations = [
            ...config.options,
            ...COMMON_OPTIONS
        ];

        const checkboxes = [
            ...(activePanel
                ? activePanel.querySelectorAll(
                    'input[type="checkbox"][data-id]:checked'
                )
                : []),

            ...(dom.commonOptions
                ? dom.commonOptions.querySelectorAll(
                    'input[type="checkbox"][data-id]:checked'
                )
                : [])
        ];

        checkboxes.forEach((checkbox) => {
            const option = optionConfigurations.find(
                (item) => item.id === checkbox.value
            );

            if (!option) {
                return;
            }

            const quantityInput = dom.form.querySelector(
                `[data-qty-for="${CSS.escape(option.id)}"]`
            );

            checkedOptions.push({
                id: option.id,
                label: option.label,
                weight: option.weight,
                quantity: option.qty
                    ? quantityInput?.value
                    : 1
            });
        });

        const pagesInput = config.pages
            ? activePanel?.querySelector(
                `input[name="${config.pages.name}"]`
            )
            : null;

        return {
            options: checkedOptions,
            pages: pagesInput?.value ?? config.pages?.included ?? 0,
            urgent: Boolean(dom.urgentInput?.checked)
        };
    };

    /**
     * Calcule et affiche le résumé courant.
     *
     * @returns {void}
     */
    const updateSummary = () => {
        const data = getCurrentProjectData();

        const summary = calculateProjectSummary(
            currentType,
            data.options,
            data.pages,
            PROJECT_TYPES
        );

        renderSummary(summary);
    };

    /**
     * Change le type de projet actif.
     *
     * @param {string} type Nouveau type de projet.
     * @returns {void}
     */
    const changeProjectType = (type) => {
        if (!PROJECT_TYPES[type]) {
            type = 'vitrine';
        }

        currentType = type;

        renderTabs(currentType);

        // Conserve le partage du type de projet dans l'URL.
        const url = new URL(window.location.href);

        url.searchParams.set('tab', currentType);

        window.history.replaceState({}, '', url);

        updateSummary();
    };

    /**
     * Change le type de demandeur actif (particulier / entreprise).
     *
     * @param {string} type Nouveau type de demandeur.
     * @returns {void}
     */
    const changeContactType = (type) => {
        if (!CONTACT_TYPES[type]) {
            type = 'particulier';
        }

        currentContactType = type;
        renderContactType(currentContactType);
    };

    // Génération initiale des options.
    Object.entries(PROJECT_TYPES).forEach(([type, config]) => {
        renderSpecificOptions(type, config);
    });

    renderCommonOptions(COMMON_OPTIONS);

    // Branchement unique des événements.
    attachEvents(
        updateSummary,
        changeProjectType,
        changeContactType
    );

    // Rendu initial.
    changeProjectType(currentType);
    changeContactType(currentContactType);
};

/**
 * Lance l'initialisation lorsque le DOM est disponible.
 *
 * @returns {void}
 */
if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initQuoteSimulator,
        { once: true }
    );
} else {
    initQuoteSimulator();
}
