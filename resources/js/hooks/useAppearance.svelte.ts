import { onMount } from 'svelte';

export type Appearance = 'light' | 'dark' | 'system';

// Define mediaQuery as a function to safely access window
const getMediaQuery = () => (typeof window !== 'undefined' ? window.matchMedia('(prefers-color-scheme: dark)') : null);

// Initialize theme only in browser environments
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
    const appearance = savedAppearance || 'light';

    if (appearance === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        // Explicitly set dark class based on appearance value
        if (appearance === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}

export function updateTheme(value: Appearance) {
    // Only run in browser environment
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    if (value === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        // Explicitly set dark class based on appearance value
        if (value === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}

const handleSystemThemeChange = () => {
    // Only run in browser environment
    if (typeof window === 'undefined') return;

    const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
    updateTheme(currentAppearance || 'light');
};

export function initializeTheme() {
    // Only run in browser environment
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
    updateTheme(savedAppearance || 'light');

    const mediaQuery = getMediaQuery();
    mediaQuery?.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    // Get saved appearance from localStorage
    const savedAppearance = typeof localStorage !== 'undefined' ? (localStorage.getItem('appearance') as Appearance | null) : null;

    // Initialize with saved appearance or default to light
    let appearance = $state(savedAppearance || 'light');

    onMount(() => {
        // Initialize theme
        initializeTheme();

        // Make sure appearance state matches localStorage
        if (typeof localStorage !== 'undefined') {
            const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
            if (currentAppearance) {
                appearance = currentAppearance;
            }
        }

        return () => {
            const mediaQuery = getMediaQuery();
            mediaQuery?.removeEventListener('change', handleSystemThemeChange);
        };
    });

    function updateAppearance(value: Appearance) {
        appearance = value;
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('appearance', value);
        }
        updateTheme(value);
    }

    return {
        get appearance() {
            return appearance;
        },
        updateAppearance,
    };
}
