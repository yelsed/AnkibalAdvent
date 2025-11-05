<script lang="ts">
    import confetti from 'canvas-confetti';
    import { onMount } from 'svelte';

    interface Props {
        trigger?: boolean;
        colors?: string[];
    }

    let { trigger = $bindable(false), colors = ['#ec4899', '#f472b6', '#fb7185', '#f9a8d4', '#fce7f3'] }: Props = $props();

    function fireConfetti() {
        const duration = 3000;
        const animationEnd = Date.now() + duration;
        const defaults = {
            startVelocity: 30,
            spread: 360,
            ticks: 60,
            zIndex: 9999,
            colors: colors
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

            const particleCount = 50 * (timeLeft / duration);

            confetti({
                ...defaults,
                particleCount,
                origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
            });

            confetti({
                ...defaults,
                particleCount,
                origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
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


