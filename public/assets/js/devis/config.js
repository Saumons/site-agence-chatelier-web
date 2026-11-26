/**
 * Configuration des types de projets.
 *
 * Chaque type définit son poids de base (exprimé en jours/homme),
 * son taux journalier moyen (TJM), ses éventuelles règles de
 * pagination et ses options spécifiques.
 *
 * @type {Object}
 */
export const PROJECT_TYPES = {
    vitrine: {
        label: 'Site vitrine',
        baseWeight: 2,
        dailyRate: 300,
        pages: {
            name: 'pages_count',
            included: 3,
            weightPerStep: 1,
            step: 3
        },
        options: [
            {
                id: 'form_advanced',
                label: 'Formulaire avancé (devis multi-étapes)',
                weight: 1
            },
            {
                id: 'blog',
                label: 'Blog / actualités',
                weight: 1
            },
            {
                id: 'seo_local',
                label: 'SEO local avancé',
                weight: 2
            }
        ]
    },

    ecommerce: {
        label: 'Site e-commerce',
        baseWeight: 5,
        dailyRate: 320,
        options: [
            {
                id: 'payment_method',
                label: 'Moyen de paiement supplémentaire',
                weight: 1,
                qty: true
            },
            {
                id: 'shipping_advanced',
                label: 'Livraison avancée (transporteurs, zones)',
                weight: 2
            },
            {
                id: 'stock_sync',
                label: 'Synchronisation stock / ERP',
                weight: 3,
                qty: true
            },
            {
                id: 'seo_ecommerce',
                label: 'SEO e-commerce',
                weight: 2
            }
        ]
    },

    webapp: {
        label: 'Application web',
        baseWeight: 8,
        dailyRate: 340,
        options: [
            {
                id: 'business_module',
                label: 'Module métier supplémentaire',
                weight: 3,
                qty: true
            },
            {
                id: 'extra_role',
                label: 'Rôle supplémentaire',
                weight: 1,
                qty: true
            },
            {
                id: 'rest_api',
                label: 'API REST documentée exposée',
                weight: 3
            },
            {
                id: 'sso',
                label: 'SSO (OAuth / SAML / Azure AD)',
                weight: 3
            },
            {
                id: 'gdpr',
                label: 'Conformité RGPD',
                weight: 2
            }
        ]
    }
};

/**
 * Options communes à tous les types de projets.
 *
 * @type {Object[]}
 */
export const COMMON_OPTIONS = [
    {
        id: 'group_requirements',
        label: 'Exigences groupe (charte, DSI, conformité)',
        weight: 2
    },
    {
        id: 'accessibility',
        label: 'Accessibilité RGAA de base',
        weight: 2
    },
    {
        id: 'eco_design',
        label: 'Écoconception avancée (audit EcoIndex)',
        weight: 2
    },
    {
        id: 'third_party_tool',
        label: 'Outil tiers (CRM, newsletter, agenda)',
        weight: 1,
        qty: true
    },
    {
        id: 'seo_writing',
        label: 'Rédaction SEO',
        weight: 1,
        qty: true,
        unit: 'page(s)'
    },
    {
        id: 'extra_training',
        label: 'Formation supplémentaire (demi-journée)',
        weight: 1
    },
    {
        id: 'user_doc',
        label: 'Documentation utilisateur illustrée',
        weight: 1
    }
];

/**
 * Configuration des types de demandeur (étape "Vos coordonnées").
 *
 * `requiredFieldIds` liste les champs obligatoires pour ce type.
 * Tout champ du groupe qui n'y figure pas reste facultatif mais
 * redevient actif (non masqué, non disabled) lorsque ce type est actif.
 *
 * @type {Object}
 */
export const CONTACT_TYPES = {
    particulier: {
        value: 'particulier',
        estMorale: false,
        label: 'Particulier',
        requiredFieldIds: ['lastname', 'firstname', 'email']
    },

    entreprise: {
        value: 'entreprise',
        estMorale: true,
        label: 'Entreprise / Organisation',
        requiredFieldIds: ['company_name', 'email']
    }
};
