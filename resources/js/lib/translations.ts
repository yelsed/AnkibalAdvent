import { page } from '@inertiajs/svelte';
import { get } from 'svelte/store';

type TranslationKey = string;
type TranslationParams = Record<string, string | number>;

interface Translations {
    common: Record<string, string>;
    calendar: Record<string, string>;
    auth: Record<string, string>;
    dashboard: Record<string, string>;
    admin: Record<string, string>;
}

// Global cache for translations
let translationsCache: Translations | undefined = undefined;

// Initialize translations from page props
export function initTranslations(translations: Translations): void {
    translationsCache = translations;
}

// Get translations - tries cache first, then page store using get()
function getTranslations(): Translations | undefined {
    // Return cached translations if available
    if (translationsCache) {
        return translationsCache;
    }

    // Try to get from page store using get() - this works outside reactive contexts
    try {
        const pageData = get(page);
        const translations = pageData?.props?.translations as Translations | undefined;
        if (translations) {
            translationsCache = translations;
            return translations;
        }
    } catch (e) {
        // Ignore errors - page store might not be initialized yet
    }

    return translationsCache;
}

export function t(key: TranslationKey, params?: TranslationParams): string {
    const translations = getTranslations();

    if (!translations) {
        // Return key if translations not available yet
        return key;
    }

    // Split key by dot (e.g., 'calendar.my_advent_calendars')
    const [namespace, ...keyParts] = key.split('.');
    const translationNamespace = translations[namespace as keyof Translations];

    if (!translationNamespace) {
        return key;
    }

    const translationKey = keyParts.join('.');
    let translation = translationNamespace[translationKey] || key;

    // Replace parameters in translation
    if (params) {
        Object.entries(params).forEach(([paramKey, paramValue]) => {
            translation = translation.replace(`:${paramKey}`, String(paramValue));
        });
    }

    return translation;
}
