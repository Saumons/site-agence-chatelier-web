
import { PROJECT_TYPES } from './config.js';

/**
 * Références DOM principales du simulateur.
 *
 * @type {Object}
 */
export const dom = {
    form: document.getElementById('quote-form'),
    tabs: document.querySelectorAll('.quote__tab'),
    projectInput: document.getElementById('project-type-input'),
    commonOptions: document.getElementById('common-options'),
    urgentInput: document.getElementById('urgent'),
    summaryLevel: document.getElementById('summary-level'),
    summaryList: document.getElementById('summary-list'),

    // --- Ajout : type de demandeur ---
    contactTypeInputs: document.querySelectorAll('input[name="contact_type"]'),
    estMoraleInput: document.getElementById('est-morale-input'),
    contactFieldWrappers: document.querySelectorAll('[data-contact-field]'),

    panels: Object.fromEntries(
        Object.keys(PROJECT_TYPES).map((type) => [
            type,
            document.getElementById(`panel-${type}`)
        ])
    )
};

/**
 * Récupère un panneau de projet.
 *
 * @param {string} type Type de projet.
 * @returns {HTMLElement|null}
 */
export const getPanel = (type) => {
    return dom.panels[type] || null;
};

/**
 * Récupère un champ de quantité associé à une option.
 *
 * @param {string} optionId Identifiant de l'option.
 * @returns {HTMLInputElement|null}
 */
export const getQuantityInput = (optionId) => {
    if (!dom.form) {
        return null;
    }

    return dom.form.querySelector(
        `[data-qty-for="${CSS.escape(optionId)}"]`
    );
};

/**
 * Récupère le champ de pagination du projet actif.
 *
 * @param {string} type Type de projet.
 * @returns {HTMLInputElement|null}
 */
export const getPagesInput = (type) => {
    return getPanel(type)?.querySelector(
        'input[name="pages_count"]'
    ) || null;
};

/**
 * Récupère les cases cochées appartenant au projet actif
 * et les options communes.
 *
 * Les panneaux masqués ne sont jamais parcourus.
 *
 * @param {string} type Type de projet actif.
 * @returns {HTMLInputElement[]}
 */
export const getActiveCheckedOptions = (type) => {
    const panel = getPanel(type);

    const specificOptions = panel
        ? Array.from(
            panel.querySelectorAll(
                'input[type="checkbox"][data-id]:checked'
            )
        )
        : [];

    const commonOptions = dom.commonOptions
        ? Array.from(
            dom.commonOptions.querySelectorAll(
                'input[type="checkbox"][data-id]:checked'
            )
        )
        : [];

    return [...specificOptions, ...commonOptions];
};

/**
 * Vérifie qu'un élément appartient à une zone active.
 *
 * @param {HTMLElement} element Élément à vérifier.
 * @returns {boolean}
 */
export const isElementActive = (element) => {
    return Boolean(
        element &&
        !element.closest('[hidden]') &&
        dom.form?.contains(element)
    );
};
