
/**
 * Convertit une valeur en entier positif ou nul.
 *
 * Les valeurs vides, NaN, infinies et négatives
 * sont remplacées par la valeur de secours.
 *
 * @param {*} value Valeur à convertir.
 * @param {number} fallback Valeur de secours.
 * @param {number} minimum Valeur minimale autorisée.
 * @returns {number}
 */
export const getSafeInteger = (
    value,
    fallback = 0,
    minimum = 0
) => {
    const parsed = Number.parseInt(value, 10);

    if (Number.isNaN(parsed) || !Number.isFinite(parsed)) {
        return fallback;
    }

    return Math.max(minimum, parsed);
};

/**
 * Crée un élément HTML avec des attributs sécurisés.
 *
 * Les propriétés textuelles sont affectées via textContent.
 * Les attributs ne sont jamais interprétés comme du HTML.
 *
 * @param {string} tagName Nom de la balise.
 * @param {Object} attributes Attributs et propriétés.
 * @param {string|null} text Contenu textuel facultatif.
 * @returns {HTMLElement}
 */
export const createElement = (
    tagName,
    attributes = {},
    text = null
) => {
    const element = document.createElement(tagName);

    Object.entries(attributes).forEach(([key, value]) => {
        if (value === null || value === undefined) {
            return;
        }

        if (key === 'className') {
            element.className = value;
            return;
        }

        if (key === 'dataset') {
            Object.entries(value).forEach(([dataKey, dataValue]) => {
                element.dataset[dataKey] = String(dataValue);
            });
            return;
        }

        if (key === 'checked' || key === 'disabled' || key === 'hidden') {
            element[key] = Boolean(value);
            return;
        }

        if (key in element && !key.startsWith('aria-')) {
            element[key] = value;
            return;
        }

        element.setAttribute(key, String(value));
    });

    if (text !== null) {
        element.textContent = String(text);
    }

    return element;
};

/**
 * Applique l'état visible/requis/actif d'un champ de formulaire.
 *
 * Un champ masqué est systématiquement désactivé : c'est le seul
 * moyen fiable d'empêcher un champ invisible de bloquer la
 * validation HTML5 ou d'être envoyé au serveur (l'attribut `hidden`
 * seul ne suffit pas, la spécification HTML ne l'exclut pas de la
 * validation de contrainte).
 *
 * @param {HTMLElement} wrapper Conteneur du champ (ex. .quote__field).
 * @param {HTMLInputElement|null} input Champ de saisie associé.
 * @param {{visible: boolean, required?: boolean}} state État souhaité.
 * @returns {void}
 */
export const setFieldState = (wrapper, input, { visible, required = false }) => {
    if (!wrapper) {
        return;
    }

    wrapper.hidden = !visible;

    if (!input) {
        return;
    }

    input.disabled = !visible;
    input.required = visible && required;
};
