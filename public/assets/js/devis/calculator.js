
import { LEVEL_THRESHOLDS } from './config.js';
import { getSafeInteger } from './helper.js';

/**
 * Calcule le poids global du projet.
 *
 * @param {string} projectType Type de projet.
 * @param {Object[]} options Options sélectionnées.
 * @param {number} pages Nombre de pages.
 * @param {Object} projectTypes Configuration des projets.
 * @returns {number}
 */
export const calculateProjectWeight = (
    projectType,
    options,
    pages,
    projectTypes
) => {
    const config = projectTypes[projectType];

    if (!config) {
        return 0;
    }

    let totalWeight = config.baseWeight;

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
            totalWeight +=
                Math.ceil(extraPages / config.pages.step) *
                config.pages.weightPerStep;
        }
    }

    options.forEach((option) => {
        const weight = Number(option.weight);
        const quantity = getSafeInteger(option.quantity, 1, 1);

        if (!Number.isFinite(weight) || weight < 0) {
            return;
        }

        totalWeight += weight * quantity;
    });

    return totalWeight;
};

/**
 * Détermine le niveau de complexité du projet.
 *
 * @param {number} totalWeight Poids global.
 * @returns {Object} Niveau correspondant.
 */
export const getLevel = (totalWeight) => {
    const safeWeight = Number.isFinite(totalWeight)
        ? Math.max(0, totalWeight)
        : 0;

    return (
        LEVEL_THRESHOLDS.find(
            (threshold) => safeWeight <= threshold.max
        ) || LEVEL_THRESHOLDS[LEVEL_THRESHOLDS.length - 1]
    );
};

/**
 * Génère les données du résumé du projet.
 *
 * Le délai express est affiché sans modifier le poids technique.
 *
 * @param {string} projectType Type de projet.
 * @param {Object[]} options Options sélectionnées.
 * @param {number} pages Nombre de pages.
 * @param {Object} projectTypes Configuration des projets.
 * @returns {{
 *   totalWeight: number,
 *   level: Object,
 *   lines: string[],
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
            totalWeight: 0,
            level: getLevel(0),
            lines: [],
            hasSelection: false
        };
    }

    const lines = [
        `${config.label} (base)`
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
            lines.push(
                `${safePages} pages au total (+${extraPages})`
            );
        }
    }

    options.forEach((option) => {
        const quantity = getSafeInteger(
            option.quantity,
            1,
            1
        );

        const label = String(option.label);

        lines.push(
            quantity > 1
                ? `${label} ×${quantity}`
                : label
        );
    });


    const totalWeight = calculateProjectWeight(
        projectType,
        options,
        pages,
        projectTypes
    );

    return {
        totalWeight,
        level: getLevel(totalWeight),
        lines,
        hasSelection: options.length > 0 ||
            (config.pages && getSafeInteger(
                pages,
                config.pages.included,
                config.pages.included
            ) > config.pages.included)
    };
};
