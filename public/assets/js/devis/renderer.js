
import {
    dom,
    getPanel
} from './dom.js';

import {
    createElement,
    getSafeInteger,
    setFieldState
} from './helper.js';

import { CONTACT_TYPES } from './config.js';

/**
 * Construit le HTML d'une option sous forme d'éléments DOM.
 *
 * @param {Object} option Configuration de l'option.
 * @returns {HTMLDivElement}
 */
const renderOption = (option) => {
    const row = createElement('div', {
        className: 'quote__option'
    });

    const label = createElement('label', {
        className: 'quote__option__label'
    });

    const checkbox = createElement('input', {
        type: 'checkbox',
        name: 'options[]',
        value: option.id,
        dataset: {
            id: option.id,
            weight: option.weight
        }
    });

    label.append(
        checkbox,
        document.createTextNode(option.label)
    );

    row.append(label);

    if (option.qty) {
        const quantityContainer = createElement('span', {
            className: 'quote__option__qty'
        });

        const quantityInput = createElement('input', {
            type: 'number',
            min: 1,
            value: 1,
            name: `options_qty[${option.id}]`,
            dataset: {
                qtyFor: option.id
            },
            disabled: true
        });

        const unit = createElement(
            'span',
            {},
            option.unit || 'unité(s)'
        );

        quantityContainer.append(
            quantityInput,
            unit
        );

        row.append(quantityContainer);
    }

    return row;
};

/**
 * Génère les options communes.
 *
 * @param {Object[]} commonOptions Configuration des options communes.
 * @returns {void}
 */
export const renderCommonOptions = (commonOptions) => {
    if (!dom.commonOptions) {
        return;
    }

    const fragment = document.createDocumentFragment();

    commonOptions.forEach((option) => {
        fragment.append(renderOption(option));
    });

    dom.commonOptions.replaceChildren(fragment);
};

/**
 * Génère les options spécifiques à un projet.
 *
 * @param {string} type Type de projet.
 * @param {Object} config Configuration du projet.
 * @returns {void}
 */
export const renderSpecificOptions = (type, config) => {
    const panel = getPanel(type);

    if (!panel || !config) {
        return;
    }

    const fragment = document.createDocumentFragment();

    if (config.pages) {
        const row = createElement('div', {
            className: 'quote__option'
        });

        const label = createElement(
            'label',
            {
                className: 'quote__option__label',
                htmlFor: `pages-count-${type}`
            },
            'Nombre de pages du site '
        );

        const quantityContainer = createElement('span', {
            className: 'quote__option__qty'
        });

        const pagesInput = createElement('input', {
            type: 'number',
            id: `pages-count-${type}`,
            name: config.pages.name,
            min: config.pages.included,
            value: config.pages.included
        });

        const unit = createElement(
            'span',
            {},
            `pages (${config.pages.included} incluses)`
        );

        quantityContainer.append(
            pagesInput,
            unit
        );

        row.append(label, quantityContainer);
        fragment.append(row);
    }

    config.options.forEach((option) => {
        fragment.append(renderOption(option));
    });

    panel.replaceChildren(fragment);
};

/**
 * Active un type de projet dans l'interface.
 *
 * @param {string} type Type de projet actif.
 * @returns {void}
 */
export const renderTabs = (type) => {
    dom.tabs.forEach((tab) => {
        const isActive = tab.dataset.type === type;

        tab.classList.toggle(
            'quote__tab--active',
            isActive
        );

        tab.setAttribute(
            'aria-selected',
            String(isActive)
        );

        tab.setAttribute(
            'tabindex',
            isActive ? '0' : '-1'
        );
    });

    Object.entries(dom.panels).forEach(([panelType, panel]) => {
        if (panel) {
            panel.hidden = panelType !== type;
        }
    });

    if (dom.projectInput) {
        dom.projectInput.value = type;
    }
};

/**
 * Affiche le résumé du projet.
 *
 * @param {Object} summary Données calculées du résumé.
 * @returns {void}
 */
export const renderSummary = (summary) => {
    if (dom.summaryLevel) {
        dom.summaryLevel.textContent = summary.level.label;

        dom.summaryLevel.dataset.level = summary.level.key;
    }

    if (!dom.summaryList) {
        return;
    }

    if (!summary.hasSelection) {
        const emptyItem = createElement(
            'li',
            {
                className: 'quote__summary-empty'
            },
            'Aucune option sélectionnée pour le moment'
        );

        dom.summaryList.replaceChildren(emptyItem);

        return;
    }

    const fragment = document.createDocumentFragment();

    summary.lines.forEach((line) => {
        fragment.append(
            createElement('li', {}, line)
        );
    });

    dom.summaryList.replaceChildren(fragment);
};

/**
 * Affiche les champs de coordonnées correspondant au type de demandeur actif
 * et met à jour le champ caché `est_morale` consommé par le backend.
 *
 * @param {string} type Type de demandeur actif ('particulier' | 'entreprise').
 * @returns {void}
 */
export const renderContactType = (type) => {
    const config = CONTACT_TYPES[type] || CONTACT_TYPES.particulier;

    dom.contactFieldWrappers.forEach((wrapper) => {
        const group = wrapper.dataset.contactGroup;
        const isVisible = !group || group === config.value;
        const input = wrapper.querySelector('input, textarea');

        setFieldState(wrapper, input, {
            visible: isVisible,
            required: Boolean(input && config.requiredFieldIds.includes(input.id))
        });
    });

    if (dom.estMoraleInput) {
        dom.estMoraleInput.value = config.estMorale ? '1' : '0';
    }

    dom.contactTypeInputs.forEach((radio) => {
        radio.checked = radio.value === config.value;
    });
};
