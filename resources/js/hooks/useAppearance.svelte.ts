import { onMount } from 'svelte';

export type Appearance = 'light' | 'dark' | 'system';

// Define mediaQuery as a function to safely access window
const getMediaQuery = () => (typeof window !== 'undefined' ? window.matchMedia('(prefers-color-scheme: dark)') : null);

// Initialize theme only in browser environments
// Force light mode always
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
    // Always remove dark class and force light mode
    document.documentElement.classList.remove('dark');
    // Override any saved appearance setting to light
    if (typeof localStorage !== 'undefined') {
        localStorage.setItem('appearance', 'light');
    }
}

export function updateTheme(value: Appearance) {
    // Only run in browser environment
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    // Always force light mode - ignore dark and system settings
    document.documentElement.classList.remove('dark');
}

const handleSystemThemeChange = () => {
    // Only run in browser environment
    if (typeof window === 'undefined') return;

    // Always force light mode - ignore system theme changes
    updateTheme('light');
};

export function initializeTheme() {
    // Only run in browser environment
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    // Always force light mode
    updateTheme('light');
    // Override localStorage to ensure light mode
    if (typeof localStorage !== 'undefined') {
        localStorage.setItem('appearance', 'light');
    }

    // Don't listen to system theme changes since we always want light mode
    // const mediaQuery = getMediaQuery();
    // mediaQuery?.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    // Always use light mode - ignore any saved preferences
    let appearance = $state<Appearance>('light');

    onMount(() => {
        // Initialize theme - always light
        initializeTheme();

        // Force light mode in localStorage
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('appearance', 'light');
            appearance = 'light';
        }
    });

    function updateAppearance(value: Appearance) {
        // Always force light mode, ignore any attempts to change to dark/system
        appearance = 'light';
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('appearance', 'light');
        }
        updateTheme('light');
    }

    return {
        get appearance() {
            return 'light' as Appearance;
        },
        updateAppearance,
    };
}
