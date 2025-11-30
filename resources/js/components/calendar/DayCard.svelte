<script lang="ts">
    import Bow from './Bow.svelte';
    import { getThemeColors } from '@/lib/colors';

    interface CalendarDay {
        id: number;
        day_number: number;
        unlocked_at: string | null;
    }

    interface Props {
        day: CalendarDay;
        onclick?: () => void;
        canUnlock?: boolean;
        countdown?: string | null;
        themeColor?: string;
        secondaryColor?: string | null;
    }

    let { day, onclick, canUnlock = false, countdown = null, themeColor = '#ec4899', secondaryColor = null }: Props = $props();

    const themeColors = $derived(getThemeColors(themeColor, secondaryColor));

    const isUnlocked = $derived(day.unlocked_at !== null);

    // 3D Tilt state
    let rotateX = $state(0);
    let rotateY = $state(0);
    let isHovering = $state(false);
    let isReturning = $state(false);
    let cardElement = $state<HTMLButtonElement | null>(null);
    let returnAnimationFrame: number | null = null;
    let touchStartX = $state(0);
    let touchStartY = $state(0);
    let hasMoved = $state(false);

    // Tilt intensity (adjustable)
    const MAX_ROTATION = 15; // degrees
    const PERSPECTIVE = 1000; // px

    function handleMouseMove(event: MouseEvent) {
        if (!cardElement || !isHovering) return;

        // Cancel return animation if active
        if (returnAnimationFrame) {
            cancelAnimationFrame(returnAnimationFrame);
            returnAnimationFrame = null;
            isReturning = false;
        }

        const rect = cardElement.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const mouseX = event.clientX - centerX;
        const mouseY = event.clientY - centerY;

        // Calculate rotation based on mouse position relative to card center
        rotateX = (mouseY / (rect.height / 2)) * -MAX_ROTATION;
        rotateY = (mouseX / (rect.width / 2)) * MAX_ROTATION;
    }

    function handleMouseEnter() {
        // Cancel return animation if active
        if (returnAnimationFrame) {
            cancelAnimationFrame(returnAnimationFrame);
            returnAnimationFrame = null;
            isReturning = false;
        }
        isHovering = true;
    }

    function smoothReturnToCenter() {
        if (returnAnimationFrame) {
            cancelAnimationFrame(returnAnimationFrame);
        }

        isReturning = true;
        const startX = rotateX;
        const startY = rotateY;
        const duration = 300; // ms
        const startTime = performance.now();

        function animate(currentTime: number) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Ease out cubic for smooth deceleration
            const easeOut = 1 - Math.pow(1 - progress, 3);

            rotateX = startX * (1 - easeOut);
            rotateY = startY * (1 - easeOut);

            if (progress < 1) {
                returnAnimationFrame = requestAnimationFrame(animate);
            } else {
                rotateX = 0;
                rotateY = 0;
                isReturning = false;
                returnAnimationFrame = null;
            }
        }

        returnAnimationFrame = requestAnimationFrame(animate);
    }

    function handleMouseLeave() {
        isHovering = false;
        smoothReturnToCenter();
    }

    function handleTouchMove(event: TouchEvent) {
        if (!cardElement || event.touches.length === 0) return;

        // Only prevent scrolling for unlockable cards
        if (!isUnlocked && !canUnlock) return;

        const rect = cardElement.getBoundingClientRect();
        const touch = event.touches[0];

        // Check if touch is within card bounds
        const isWithinCard =
            touch.clientX >= rect.left &&
            touch.clientX <= rect.right &&
            touch.clientY >= rect.top &&
            touch.clientY <= rect.bottom;

        if (!isWithinCard) return;

        // Detect movement
        const moveThreshold = 5; // pixels
        const deltaX = Math.abs(touch.clientX - touchStartX);
        const deltaY = Math.abs(touch.clientY - touchStartY);
        const totalMovement = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

        if (totalMovement > moveThreshold) {
            hasMoved = true;

            // Determine if this is a scroll gesture (primarily vertical) or tilt gesture
            // If movement is primarily vertical and significant, allow scrolling
            // If movement is horizontal or small, prevent scrolling to allow tilting
            const isVerticalScroll = deltaY > deltaX * 1.5 && deltaY > 15; // More vertical than horizontal, and significant

            // Only prevent scrolling if it's NOT a clear vertical scroll gesture
            // This allows normal page scrolling while still enabling tilt on small/horizontal movements
            if (!isVerticalScroll) {
                // Prevent scrolling for tilt gestures (horizontal or small movements)
                event.preventDefault();
            }
        }

        // Cancel return animation if active
        if (returnAnimationFrame) {
            cancelAnimationFrame(returnAnimationFrame);
            returnAnimationFrame = null;
            isReturning = false;
        }

        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const touchX = touch.clientX - centerX;
        const touchY = touch.clientY - centerY;

        // Calculate rotation based on touch position
        rotateX = (touchY / (rect.height / 2)) * -MAX_ROTATION;
        rotateY = (touchX / (rect.width / 2)) * MAX_ROTATION;
        isHovering = true;
    }

    function handleTouchEnd(event: TouchEvent) {
        // With touch-action: manipulation, normal clicks work, so we don't need manual triggering
        // Just reset state
        isHovering = false;
        hasMoved = false;
        smoothReturnToCenter();
    }

    function handleTouchStart(event: TouchEvent) {
        // Store initial touch position to detect movement
        if (event.touches.length > 0) {
            touchStartX = event.touches[0].clientX;
            touchStartY = event.touches[0].clientY;
            hasMoved = false;
        }

        // Don't prevent default here - allow normal scrolling
        // We'll only prevent scrolling in touchMove when movement is detected

        // Cancel return animation if active
        if (returnAnimationFrame) {
            cancelAnimationFrame(returnAnimationFrame);
            returnAnimationFrame = null;
            isReturning = false;
        }
        isHovering = true;
    }

    // Optional: Gyroscope support for mobile (subtle effect)
    let gyroEnabled = $state(false);
    let gyroX = $state(0);
    let gyroY = $state(0);

    $effect(() => {
        // Only enable gyroscope on mobile devices if available
        if (typeof window === 'undefined') return;

        const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
        if (!isMobile) return;

        // Check if deviceorientation is available
        if (typeof DeviceOrientationEvent !== 'undefined' && DeviceOrientationEvent.requestPermission) {
            // iOS 13+ requires permission
            return;
        }

        function handleDeviceOrientation(event: DeviceOrientationEvent) {
            if (event.gamma === null || event.beta === null) return;

            // Subtle gyroscope effect (only when not touching)
            if (!isHovering) {
                gyroX = (event.gamma || 0) * 0.1; // Scale down for subtlety
                gyroY = (event.beta || 0) * 0.1;
            }
        }

        window.addEventListener('deviceorientation', handleDeviceOrientation);
        gyroEnabled = true;

        return () => {
            window.removeEventListener('deviceorientation', handleDeviceOrientation);
        };
    });

    // Combine touch/mouse rotation with gyroscope
    const finalRotateX = $derived(isHovering ? rotateX : rotateX + gyroX);
    const finalRotateY = $derived(isHovering ? rotateY : rotateY + gyroY);

    // Check if card is currently tilted
    const isTilted = $derived(Math.abs(finalRotateX) > 0.5 || Math.abs(finalRotateY) > 0.5);

    // Transform style string
    const transformStyle = $derived(
        `perspective(${PERSPECTIVE}px) rotateX(${finalRotateX}deg) rotateY(${finalRotateY}deg)`
    );

    // Set CSS custom properties for theme colors
    $effect(() => {
        if (cardElement) {
            const rgb = themeColors.withOpacity;
            cardElement.style.setProperty('--theme-shadow-1', rgb['30']);
            cardElement.style.setProperty('--theme-shadow-2', rgb['20']);
            cardElement.style.setProperty('--theme-shadow-3', rgb['10']);
            cardElement.style.setProperty('--theme-shadow-4', rgb['30']);
            cardElement.style.setProperty('--theme-shadow-5', rgb['30']);
            cardElement.style.setProperty('--theme-shadow-6', rgb['20']);
        }
    });

    // Simple click handler - touch-action: manipulation allows normal clicks
    function handleClick() {
        if (onclick) {
            onclick();
        }
    }

    // Cleanup animation frame on component destroy
    $effect(() => {
        return () => {
            if (returnAnimationFrame) {
                cancelAnimationFrame(returnAnimationFrame);
            }
        };
    });
</script>

<div class="relative w-full overflow-visible">
    <button
        bind:this={cardElement}
        type="button"
        onclick={handleClick}
        disabled={!isUnlocked && !canUnlock}
        onmousemove={handleMouseMove}
        onmouseenter={handleMouseEnter}
        onmouseleave={handleMouseLeave}
        ontouchstart={handleTouchStart}
        ontouchmove={handleTouchMove}
        ontouchend={handleTouchEnd}
        style="transform: {transformStyle}; transform-style: preserve-3d;"
        class="group relative flex aspect-square w-full items-center justify-center overflow-visible rounded-xl disabled:cursor-not-allowed disabled:opacity-50"
        class:unlocked={isUnlocked}
        class:locked={!isUnlocked}
        class:tilt-enabled={isUnlocked || canUnlock}
        class:tilted={isTilted && (isUnlocked || canUnlock)}
    >
        <!-- Decorative bow at the top - inside button so it moves with tilt -->
        <div class="absolute left-1/2 top-2 z-30 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
            <Bow width={60} height={60} class="drop-shadow-lg" themeColor={themeColor} secondaryColor={secondaryColor} />
        </div>

    {#if isUnlocked}
        <!-- Unlocked state with solid background (no gradient) -->
        <div
            class="absolute inset-0 rounded-xl"
            style="background-color: {themeColors.medium}; opacity: 0.9;"
        ></div>
        <div class="relative z-10 flex flex-col items-center gap-2 text-white">
            <span class="text-5xl font-bold">{day.day_number}</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                />
            </svg>
        </div>
    {:else}
        <!-- Locked state with gift wrap pattern -->
        <div
            class="absolute inset-0 rounded-xl"
            style="background-image: repeating-linear-gradient(
                45deg,
                {themeColors.light} 0px,
                {themeColors.light} 10px,
                {themeColors.lighter} 10px,
                {themeColors.lighter} 20px
            ); opacity: 0.9;"
        ></div>

        <!-- Ribbon effect - use secondary color for accents if available -->
        <div class="absolute inset-0 rounded-xl overflow-hidden">
            <div
                class="absolute left-1/2 top-0 h-full w-4 -translate-x-1/2 opacity-80"
                style="background-color: {secondaryColor ? (themeColors as any).secondaryDark || secondaryColor : themeColors.medium};"
            ></div>
            <div
                class="absolute left-0 top-1/2 h-4 w-full -translate-y-1/2 opacity-80"
                style="background-color: {secondaryColor ? (themeColors as any).secondaryDark || secondaryColor : themeColors.medium};"
            ></div>
        </div>

        <div class="relative z-10 flex flex-col items-center gap-2">
            <span class="text-5xl font-bold" style="color: {themeColors.dark};">{day.day_number}</span>
            {#if canUnlock}
                <span class="text-xs font-medium" style="color: {themeColors.dark};">Tap to open</span>
            {:else if countdown}
                <div class="flex flex-col items-center gap-0.5">
                    <span class="text-xs font-medium font-mono leading-tight" style="color: {themeColors.dark};">
                        {countdown}
                    </span>
                </div>
            {:else}
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: {themeColors.dark};">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            {/if}
        </div>
    {/if}

    <!-- Hover effect -->
    {#if isUnlocked || canUnlock}
        <div class="absolute inset-0 bg-white opacity-0 transition-opacity group-hover:opacity-10 rounded-xl"></div>
    {/if}
    </button>
</div>

<style>
    button {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        will-change: transform;
        backface-visibility: hidden;
    }

    button.tilt-enabled:not(:disabled) {
        cursor: pointer;
        /* Allow normal scrolling, but we'll prevent it only when tilting */
        /* manipulation allows taps and pan gestures, but prevents double-tap zoom */
        touch-action: manipulation;
        -webkit-touch-callout: none;
    }

    button.tilt-enabled:not(:disabled):hover {
        box-shadow: 0 20px 25px -5px var(--theme-shadow-1),
            0 10px 10px -5px var(--theme-shadow-2),
            0 0 0 1px var(--theme-shadow-3);
    }

    /* Enhanced glow effect when tilted */
    button.tilt-enabled.tilted:not(:disabled) {
        box-shadow: 0 25px 30px -5px var(--theme-shadow-4),
            0 15px 15px -5px var(--theme-shadow-5),
            0 0 20px var(--theme-shadow-6);
    }

    /* Smooth transition for box-shadow only */
    button.tilt-enabled:not(:disabled) {
        transition: box-shadow 0.2s ease-out;
    }

    /* Prevent tilt on disabled cards */
    button:disabled {
        transform: none !important;
    }
</style>
