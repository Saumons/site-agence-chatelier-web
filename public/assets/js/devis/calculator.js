import { getSafeInteger } from './helper.js';

/**
 * Calcule la charge globale du projet, en jours/homme.
 *
 * @param {string} projectType Type de projet.
 * @param {Object[]} options Options sélectionnées.
 * @param {number} pages Nombre de pages.
 * @param {Object} projectTypes Configuration des projets.
 * @returns {number} Nombre total de jours/homme.
 */
export const calculateProjectDays = (
    projectType,
    options,
    pages,
    projectTypes
) => {
    const config = projectTypes[projectType];

    if (!config) {
        return 0;
    }

    let totalDays = config.baseWeight;

    if (config.pages) {
        const safePages = getSafeInteger(
            pages,
            config.pages.included,
            config.pages.included
        );

        const extraPages = Math.max(
            0,
            safePages - config.pages.included
        );

        if (extraPages > 0) {
            totalDays +=
                Math.ceil(extraPages / config.pages.step) *
                config.pages.weightPerStep;
        }
    }

    options.forEach((option) => {
        const days = Number(option.weight);
        const quantity = getSafeInteger(option.quantity, 1, 1);

        if (!Number.isFinite(days) || days < 0) {
            return;
        }

        totalDays += days * quantity;
    });

    return totalDays;
};

/**
 * Génère les données du résumé du projet : détail en jours/homme
 * par poste, charge totale et prix estimé (charge × TJM du type
 * de projet actif).
 *
 * @param {string} projectType Type de projet.
 * @param {Object[]} options Options sélectionnées.
 * @param {number} pages Nombre de pages.
 * @param {Object} projectTypes Configuration des projets.
 * @returns {{
 *   totalDays: number,
 *   dailyRate: number,
 *   totalPrice: number,
 *   lines: {label: string, days: number}[],
 *   hasSelection: boolean
 * }}
 */
export const calculateProjectSummary = (
    projectType,
    options,
    pages,
    projectTypes
) => {
    const config = projectTypes[projectType];

    if (!config) {
        return {
            totalDays: 0,
            dailyRate: 0,
            totalPrice: 0,
            lines: [],
            hasSelection: false
        };
    }

    const lines = [
        {
            label: `${config.label} (base)`,
            days: config.baseWeight
        }
    ];

    if (config.pages) {
        const safePages = getSafeInteger(
            pages,
            config.pages.included,
            config.pages.included
        );

        const extraPages = Math.max(
            0,
            safePages - config.pages.included
        );

        if (extraPages > 0) {
            const extraDays =
                Math.ceil(extraPages / config.pages.step) *
                config.pages.weightPerStep;

            lines.push({
                label: `${safePages} pages au total (+${extraPages})`,
                days: extraDays
            });
        }
    }

    options.forEach((option) => {
        const quantity = getSafeInteger(
            option.quantity,
            1,
            1
        );

        const days = Number(option.weight) * quantity;
        const label = String(option.label);

        lines.push({
            label: quantity > 1 ? `${label} ×${quantity}` : label,
            days
        });
    });

    const totalDays = calculateProjectDays(
        projectType,
        options,
        pages,
        projectTypes
    );

    const dailyRate = Number(config.dailyRate) || 0;

    return {
        totalDays,
        dailyRate,
        totalPrice: totalDays * dailyRate,
        lines,
        hasSelection: options.length > 0 ||
            (config.pages && getSafeInteger(
                pages,
                config.pages.included,
                config.pages.included
            ) > config.pages.included)
    };
};
