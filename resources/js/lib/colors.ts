/**
 * Convert hex color to RGB
 */
export function hexToRgb(hex: string): { r: number; g: number; b: number } | null {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result
        ? {
              r: parseInt(result[1], 16),
              g: parseInt(result[2], 16),
              b: parseInt(result[3], 16),
          }
        : null;
}

/**
 * Convert RGB to HSL
 */
export function rgbToHsl(r: number, g: number, b: number): { h: number; s: number; l: number } {
    r /= 255;
    g /= 255;
    b /= 255;

    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h = 0;
    let s = 0;
    const l = (max + min) / 2;

    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

        switch (max) {
            case r:
                h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
                break;
            case g:
                h = ((b - r) / d + 2) / 6;
                break;
            case b:
                h = ((r - g) / d + 4) / 6;
                break;
        }
    }

    return { h: h * 360, s: s * 100, l: l * 100 };
}

/**
 * Convert HSL to RGB
 */
export function hslToRgb(h: number, s: number, l: number): { r: number; g: number; b: number } {
    h /= 360;
    s /= 100;
    l /= 100;

    let r: number, g: number, b: number;

    if (s === 0) {
        r = g = b = l;
    } else {
        const hue2rgb = (p: number, q: number, t: number) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 6) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };

        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;

        r = hue2rgb(p, q, h + 1 / 3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1 / 3);
    }

    return {
        r: Math.round(r * 255),
        g: Math.round(g * 255),
        b: Math.round(b * 255),
    };
}

/**
 * Convert RGB to hex
 */
export function rgbToHex(r: number, g: number, b: number): string {
    return '#' + [r, g, b].map((x) => x.toString(16).padStart(2, '0')).join('');
}

/**
 * Adjust lightness of a color
 */
export function adjustLightness(hex: string, amount: number): string {
    const rgb = hexToRgb(hex);
    if (!rgb) return hex;

    const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
    const newLightness = Math.max(0, Math.min(100, hsl.l + amount));
    const newRgb = hslToRgb(hsl.h, hsl.s, newLightness);

    return rgbToHex(newRgb.r, newRgb.g, newRgb.b);
}

/**
 * Adjust saturation of a color
 */
export function adjustSaturation(hex: string, amount: number): string {
    const rgb = hexToRgb(hex);
    if (!rgb) return hex;

    const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);
    const newSaturation = Math.max(0, Math.min(100, hsl.s + amount));
    const newRgb = hslToRgb(hsl.h, newSaturation, hsl.l);

    return rgbToHex(newRgb.r, newRgb.g, newRgb.b);
}

/**
 * Get color with opacity (returns rgba string)
 */
export function colorWithOpacity(hex: string, opacity: number): string {
    const rgb = hexToRgb(hex);
    if (!rgb) return hex;

    return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${opacity})`;
}

/**
 * Generate color variations for theme
 */
export function getThemeColors(themeColor: string, secondaryColor?: string | null) {
    const rgb = hexToRgb(themeColor);
    if (!rgb) {
        // Fallback to default pink
        return {
            base: '#ec4899',
            secondary: secondaryColor || null,
            light: '#fce7f3',
            lighter: '#f9a8d4',
            medium: '#f472b6',
            dark: '#db2777',
            darker: '#be185d',
            withOpacity: {
                '10': 'rgba(236, 72, 153, 0.1)',
                '20': 'rgba(236, 72, 153, 0.2)',
                '30': 'rgba(236, 72, 153, 0.3)',
                '50': 'rgba(236, 72, 153, 0.5)',
                '80': 'rgba(236, 72, 153, 0.8)',
            },
        };
    }

    const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);

    const result: {
        base: string;
        secondary: string | null;
        light: string;
        lighter: string;
        medium: string;
        dark: string;
        darker: string;
        withOpacity: Record<string, string>;
    } = {
        base: themeColor,
        secondary: secondaryColor || null,
        light: rgbToHex(...Object.values(hslToRgb(hsl.h, Math.max(0, hsl.s - 30), Math.min(100, hsl.l + 30)))),
        lighter: rgbToHex(...Object.values(hslToRgb(hsl.h, Math.max(0, hsl.s - 20), Math.min(100, hsl.l + 20)))),
        medium: rgbToHex(...Object.values(hslToRgb(hsl.h, hsl.s, hsl.l))),
        dark: rgbToHex(...Object.values(hslToRgb(hsl.h, hsl.s, Math.max(0, hsl.l - 10)))),
        darker: rgbToHex(...Object.values(hslToRgb(hsl.h, hsl.s, Math.max(0, hsl.l - 20)))),
        withOpacity: {
            '10': colorWithOpacity(themeColor, 0.1),
            '20': colorWithOpacity(themeColor, 0.2),
            '30': colorWithOpacity(themeColor, 0.3),
            '50': colorWithOpacity(themeColor, 0.5),
            '80': colorWithOpacity(themeColor, 0.8),
        },
    };

    // Add secondary color variations if provided
    if (secondaryColor) {
        const secondaryRgb = hexToRgb(secondaryColor);
        if (secondaryRgb) {
            const secondaryHsl = rgbToHsl(secondaryRgb.r, secondaryRgb.g, secondaryRgb.b);
            (result as any).secondaryLight = rgbToHex(
                ...Object.values(hslToRgb(secondaryHsl.h, Math.max(0, secondaryHsl.s - 30), Math.min(100, secondaryHsl.l + 30)))
            );
            (result as any).secondaryDark = rgbToHex(
                ...Object.values(hslToRgb(secondaryHsl.h, secondaryHsl.s, Math.max(0, secondaryHsl.l - 10)))
            );
        }
    }

    return result;
}
