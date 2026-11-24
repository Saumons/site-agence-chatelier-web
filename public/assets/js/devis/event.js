
import {
    dom,
    getQuantityInput
} from './dom.js';

/**
 * Éléments ayant déjà reçu leurs écouteurs.
 *
 * @type {WeakSet<HTMLElement>}
 */
const initializedElements = new WeakSet();

/**
 * Branche les écouteurs du simulateur.
 *
 * @param {Function} onUpdate Callback de mise à jour du résumé.
 * @param {Function} onTypeChange Callback de changement de projet.
 * @returns {void}
 */
export const attachEvents = (
    onUpdate,
    onTypeChange,
    onContactTypeChange   // <-- nouveau paramètre
) => {
    if (!dom.form || initializedElements.has(dom.form)) {
        return;
    }

    initializedElements.add(dom.form);

    const synchronizeQuantity = (checkbox) => {
        const quantityInput = getQuantityInput(checkbox.value);
        if (quantityInput) {
            quantityInput.disabled = !checkbox.checked;
        }
    };

    dom.form.addEventListener('change', (event) => {
        const target = event.target;

        if (
            target instanceof HTMLInputElement &&
            target.type === 'checkbox' &&
            target.hasAttribute('data-id')
        ) {
            synchronizeQuantity(target);
        }

        // --- Ajout : changement de type de demandeur ---
        if (
            target instanceof HTMLInputElement &&
            target.type === 'radio' &&
            target.name === 'contact_type'
        ) {
            onContactTypeChange(target.value);
            return; // le type de contact n'influe pas sur le résumé de devis
        }

        onUpdate();
    });

    dom.form.addEventListener('input', (event) => {
        const target = event.target;

        if (
            target instanceof HTMLInputElement &&
            (target.type === 'number' || target.hasAttribute('data-qty-for'))
        ) {
            onUpdate();
        }
    });

    const tabsContainer = dom.tabs[0]?.parentElement;

    if (tabsContainer && !initializedElements.has(tabsContainer)) {
        initializedElements.add(tabsContainer);

        tabsContainer.addEventListener('click', (event) => {
            const tab = event.target.closest('.quote__tab');

            if (!tab || !tabsContainer.contains(tab) || !tab.dataset.type) {
                return;
            }

            event.preventDefault();
            onTypeChange(tab.dataset.type);
        });
    }
};
