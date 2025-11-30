<script lang="ts">
    import confetti from 'canvas-confetti';

    /**
     * ConfettiEffect Component
     *
     * A reusable confetti animation component using canvas-confetti library.
     *
     * @example
     * ```svelte
     * <ConfettiEffect bind:trigger={showConfetti} colors={['#ff0000', '#00ff00']} />
     * ```
     *
     * @prop {boolean} trigger - When set to true, triggers the confetti animation
     * @prop {string[]} colors - Array of hex color codes for confetti particles (default: pink theme colors)
     * @prop {number} duration - Animation duration in milliseconds (default: 3000)
     * @prop {number} particleCount - Base particle count per burst (default: 50)
     * @prop {number} spread - Spread angle in degrees (default: 360)
     * @prop {number} startVelocity - Initial velocity of particles (default: 30)
     * @prop {number} ticks - Number of animation frames (default: 60)
     */
    import { getThemeColors } from '@/lib/colors';

    interface Props {
        /** When set to true, triggers the confetti animation */
        trigger?: boolean;
        /** Array of hex color codes for confetti particles */
        colors?: string[];
        /** Animation duration in milliseconds */
        duration?: number;
        /** Base particle count per burst */
        particleCount?: number;
        /** Spread angle in degrees */
        spread?: number;
        /** Initial velocity of particles */
        startVelocity?: number;
        /** Number of animation frames */
        ticks?: number;
        /** Theme color to generate confetti colors from */
        themeColor?: string;
        /** Secondary color for dual themes */
        secondaryColor?: string | null;
    }

    let {
        trigger = $bindable(false),
        colors,
        duration = 3000,
        particleCount = 50,
        spread = 360,
        startVelocity = 30,
        ticks = 60,
        themeColor = '#ec4899',
        secondaryColor = null,
    }: Props = $props();

    // Generate colors from theme if not provided
    const confettiColors = $derived(() => {
        if (colors && colors.length > 0) {
            return colors;
        }
        const themeColors = getThemeColors(themeColor, secondaryColor);
        const colorArray = [themeColors.base, themeColors.medium, themeColors.lighter, themeColors.light, themeColors.dark];
        if (secondaryColor && themeColors.secondary) {
            colorArray.push(themeColors.secondary);
            if ((themeColors as any).secondaryLight) {
                colorArray.push((themeColors as any).secondaryLight);
            }
            if ((themeColors as any).secondaryDark) {
                colorArray.push((themeColors as any).secondaryDark);
            }
        }
        return colorArray;
    });

    function fireConfetti() {
        const animationEnd = Date.now() + duration;
        const defaults = {
            startVelocity,
            spread,
            ticks,
            zIndex: 9999,
            colors: confettiColors(),
        };

        function randomInRange(min: number, max: number) {
            return Math.random() * (max - min) + min;
        }

        const interval = setInterval(() => {
            const timeLeft = animationEnd - Date.now();

            if (timeLeft <= 0) {
                clearInterval(interval);
                return;
            }

            const currentParticleCount = particleCount * (timeLeft / duration);

            // Fire from left side
            confetti({
                ...defaults,
                particleCount: currentParticleCount,
                origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
            });

            // Fire from right side
            confetti({
                ...defaults,
                particleCount: currentParticleCount,
                origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
            });
        }, 250);
    }

    $effect(() => {
        if (trigger) {
            fireConfetti();
            trigger = false;
        }
    });
</script>
