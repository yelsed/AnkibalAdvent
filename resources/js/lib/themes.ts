export interface ThemeDefinition {
    name: string;
    primary: string;
    secondary: string;
    description: string;
}

export const themes: Record<string, ThemeDefinition> = {
    sinterklaas: {
        name: 'Sinterklaas',
        primary: '#dc2626',
        secondary: '#fbbf24',
        description: 'Rood en goud voor Sinterklaas',
    },
    kerst: {
        name: 'Kerst',
        primary: '#dc2626',
        secondary: '#16a34a',
        description: 'Rood en groen voor Kerst',
    },
    oudjaar: {
        name: 'Oudjaarsdag',
        primary: '#000000',
        secondary: '#fbbf24',
        description: 'Zwart en goud voor Oudjaarsdag',
    },
};

export const defaultSeasonalRanges = [
    { days: [5], theme: 'sinterklaas' },
    { days: [24, 25, 26], theme: 'kerst' },
    { days: [31], theme: 'oudjaar' },
];

export interface ThemeResult {
    primary: string;
    secondary: string | null;
}

export function getThemeForDay(
    themeType: 'single' | 'dual' | 'seasonal',
    dayNumber: number,
    primaryColor: string,
    secondaryColor: string | null,
    seasonalConfig: any,
    dayThemeOverride?: any
): ThemeResult {
    // Check voor per-dag override (als die bestaat)
    if (dayThemeOverride) {
        return {
            primary: dayThemeOverride.primary || primaryColor,
            secondary: dayThemeOverride.secondary || secondaryColor,
        };
    }

    if (themeType === 'seasonal') {
        const config = seasonalConfig || { ranges: defaultSeasonalRanges };
        let foundInRange = false;

        for (const range of config.ranges) {
            if (range.days.includes(dayNumber)) {
                const theme = themes[range.theme];
                if (theme) {
                    foundInRange = true;
                    return {
                        primary: theme.primary,
                        secondary: theme.secondary,
                    };
                }
            }
        }

        // For days not in ranges, return the normal primary color
        if (!foundInRange && primaryColor) {
            return {
                primary: primaryColor,
                secondary: null,
            };
        }
    }

    if (themeType === 'dual') {
        return {
            primary: primaryColor,
            secondary: secondaryColor || primaryColor,
        };
    }

    // Single theme (default)
    return {
        primary: primaryColor,
        secondary: null,
    };
}
