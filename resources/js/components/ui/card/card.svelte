<script lang="ts">
	import type { HTMLAttributes } from "svelte/elements";
	import { cn, type WithElementRef } from "@/lib/utils.js";

	let {
		ref = $bindable(null),
		class: className,
		children,
		tiltEnabled = false,
		onclick,
		style: styleProp,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> & { tiltEnabled?: boolean } = $props();

	// 3D Tilt state
	let rotateX = $state(0);
	let rotateY = $state(0);
	let isHovering = $state(false);
	let isReturning = $state(false);
	let cardElement = $state<HTMLDivElement | null>(null);
	let returnAnimationFrame: number | null = null;
	let touchStartX = $state(0);
	let touchStartY = $state(0);
	let hasMoved = $state(false);

	// Tilt intensity (adjustable)
	const MAX_ROTATION = 15; // degrees
	const PERSPECTIVE = 1000; // px

	function handleMouseMove(event: MouseEvent) {
		if (!cardElement || !isHovering || !tiltEnabled) return;

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
		if (!tiltEnabled) return;
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
		if (!tiltEnabled) return;
		isHovering = false;
		smoothReturnToCenter();
	}

	function handleTouchMove(event: TouchEvent) {
		if (!cardElement || event.touches.length === 0 || !tiltEnabled) return;

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
			const isVerticalScroll = deltaY > deltaX * 1.5 && deltaY > 15;

			// Only prevent scrolling if it's NOT a clear vertical scroll gesture
			if (!isVerticalScroll) {
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

	function handleTouchEnd() {
		if (!tiltEnabled) return;
		isHovering = false;
		hasMoved = false;
		smoothReturnToCenter();
	}

	function handleTouchStart(event: TouchEvent) {
		if (!tiltEnabled) return;
		// Store initial touch position to detect movement
		if (event.touches.length > 0) {
			touchStartX = event.touches[0].clientX;
			touchStartY = event.touches[0].clientY;
			hasMoved = false;
		}

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
		if (!tiltEnabled) return;
		// Only enable gyroscope on mobile devices if available
		if (typeof window === 'undefined') return;

		const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
		if (!isMobile) return;

		// Check if deviceorientation is available
		if (typeof DeviceOrientationEvent !== 'undefined' && 'requestPermission' in DeviceOrientationEvent) {
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
		tiltEnabled
			? `perspective(${PERSPECTIVE}px) rotateX(${finalRotateX}deg) rotateY(${finalRotateY}deg)`
			: ''
	);

	// Combine transform style with any existing style prop
	const combinedStyle = $derived.by(() => {
		if (!tiltEnabled && !styleProp) return styleProp;
		if (!tiltEnabled) return styleProp;

		// Parse existing style prop if it's a string
		const existingStyles: Record<string, string> = {};

		if (styleProp) {
			if (typeof styleProp === 'string') {
				// Parse string style (e.g., "border-color: pink; color: red")
				styleProp.split(';').forEach(rule => {
					const trimmed = rule.trim();
					if (trimmed) {
						const colonIndex = trimmed.indexOf(':');
						if (colonIndex > 0) {
							const key = trimmed.substring(0, colonIndex).trim();
							const value = trimmed.substring(colonIndex + 1).trim();
							if (key && value) {
								existingStyles[key] = value;
							}
						}
					}
				});
			} else if (styleProp && typeof styleProp === 'object' && !Array.isArray(styleProp)) {
				// Handle object style - convert to plain object
				for (const key in styleProp) {
					if (Object.prototype.hasOwnProperty.call(styleProp, key)) {
						const value = (styleProp as Record<string, unknown>)[key];
						if (typeof value === 'string' || typeof value === 'number') {
							existingStyles[key] = String(value);
						}
					}
				}
			}
		}

		// Add transform style (this will override any existing transform)
		if (transformStyle) {
			existingStyles.transform = transformStyle;
			existingStyles['transform-style'] = 'preserve-3d';
		}

		// Convert back to string
		const entries = Object.keys(existingStyles);
		if (entries.length === 0) return undefined;

		const styleString = entries
			.map((key) => {
				const value = existingStyles[key];
				// Handle CSS custom properties and kebab-case
				const cssKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
				return `${cssKey}: ${value}`;
			})
			.join('; ');

		return styleString;
	});

	// Cleanup animation frame on component destroy
	$effect(() => {
		return () => {
			if (returnAnimationFrame) {
				cancelAnimationFrame(returnAnimationFrame);
			}
		};
	});

	// Sync ref binding
	$effect(() => {
		if (cardElement) {
			ref = cardElement;
		}
	});

	// Handle click events
	function handleClick(event: MouseEvent) {
		if (onclick) {
			onclick(event as any);
		}
	}
</script>

<div
	bind:this={cardElement}
	data-slot="card"
	onmousemove={handleMouseMove}
	onmouseenter={handleMouseEnter}
	onmouseleave={handleMouseLeave}
	ontouchstart={handleTouchStart}
	ontouchmove={handleTouchMove}
	ontouchend={handleTouchEnd}
	onclick={handleClick}
	style={combinedStyle}
	class={cn(
		"bg-card border-2 text-card-foreground flex flex-col gap-6 rounded-xl py-6 shadow-sm",
		tiltEnabled && "cursor-pointer",
		tiltEnabled && isTilted && "tilted",
		className
	)}
	{...restProps}
>
	{@render children?.()}
</div>

<style>
	:global(div[data-slot="card"]) {
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
		will-change: transform;
		backface-visibility: hidden;
	}

	:global(div[data-slot="card"].cursor-pointer:hover) {
		box-shadow: 0 20px 25px -5px rgba(236, 72, 153, 0.3),
			0 10px 10px -5px rgba(236, 72, 153, 0.2),
			0 0 0 1px rgba(236, 72, 153, 0.1);
		transition: box-shadow 0.2s ease-out;
	}

	:global(div[data-slot="card"].cursor-pointer.tilted) {
		box-shadow: 0 25px 30px -5px rgba(236, 72, 153, 0.4),
			0 15px 15px -5px rgba(236, 72, 153, 0.3),
			0 0 20px rgba(236, 72, 153, 0.2);
	}

	:global(div[data-slot="card"].cursor-pointer) {
		touch-action: manipulation;
		-webkit-touch-callout: none;
		transition: box-shadow 0.2s ease-out;
	}
</style>
