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
    }

    let {
        trigger = $bindable(false),
        colors = ['#ec4899', '#f472b6', '#fb7185', '#f9a8d4', '#fce7f3'],
        duration = 3000,
        particleCount = 50,
        spread = 360,
        startVelocity = 30,
        ticks = 60,
    }: Props = $props();

    function fireConfetti() {
        const animationEnd = Date.now() + duration;
        const defaults = {
            startVelocity,
            spread,
            ticks,
            zIndex: 9999,
            colors: colors,
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
