<script lang="ts">
    import { Pause, Play } from 'lucide-svelte';
    import { page } from '@inertiajs/svelte';
    import { t, initTranslations } from '@/lib/translations';

    // Initialize translations immediately from page props
    const translations = ($page.props as any)?.translations;
    if (translations) {
        initTranslations(translations);
    }

    interface Props {
        audioUrl: string;
        autoplay?: boolean;
        loop?: boolean;
        class?: string;
    }

    let { audioUrl, autoplay = false, loop = false, class: className = '' }: Props = $props();

    let audioElement: HTMLAudioElement;
    let isPlaying = $state(false);
    let isLoading = $state(false);
    let error = $state<string | null>(null);

    function togglePlay() {
        if (!audioElement) return;

        if (isPlaying) {
            audioElement.pause();
        } else {
            audioElement.play().catch((err) => {
                error = t('common.error');
                console.error('Audio playback error:', err);
            });
        }
    }

    function handlePlay() {
        isPlaying = true;
        isLoading = false;
        error = null;
    }

    function handlePause() {
        isPlaying = false;
    }

    function handleLoadStart() {
        isLoading = true;
    }

    function handleLoadedData() {
        isLoading = false;
    }

    function handleError() {
        error = t('common.error');
        isLoading = false;
        isPlaying = false;
    }

    // Handle autoplay when component mounts or when autoplay prop changes
    $effect(() => {
        if (autoplay && audioElement && audioUrl) {
            // Small delay to ensure audio element is ready
            const timer = setTimeout(() => {
                audioElement.play().catch((err) => {
                    // Autoplay might fail due to browser policies, that's okay
                    console.error('Autoplay failed:', err);
                });
            }, 100);

            return () => clearTimeout(timer);
        }
    });
</script>

{#if audioUrl}
    <div
        role="button"
        tabindex="0"
        onclick={togglePlay}
        onkeydown={(e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePlay();
            }
        }}
        class="flex cursor-pointer items-center gap-3 rounded-lg border border-pink-200 bg-pink-50 p-3 transition-colors hover:bg-pink-100 {className}"
        class:opacity-50={isLoading || !!error}
        class:cursor-not-allowed={isLoading || !!error}
        aria-label={isPlaying ? t('common.pause') : t('common.play')}
    >
        <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-pink-500 text-white transition-colors"
        >
            {#if isLoading}
                <div class="h-6 w-6 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
            {:else if isPlaying}
                <Pause class="h-5 w-5" />
            {:else}
                <Play class="h-5 w-5" />
            {/if}
        </div>

        <div class="flex-1 min-w-0">
            {#if error}
                <p class="text-sm text-red-600">{error}</p>
            {:else}
                <p class="text-lg font-medium text-pink-700">{t('common.audio')}</p>
            {/if}
        </div>

        <audio
            bind:this={audioElement}
            src={audioUrl}
            {loop}
            onplay={handlePlay}
            onpause={handlePause}
            onloadstart={handleLoadStart}
            onloadeddata={handleLoadedData}
            onerror={handleError}
            preload="metadata"
        ></audio>
    </div>
{/if}
