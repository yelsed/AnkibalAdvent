<script lang="ts">
    import { Pause, Play } from 'lucide-svelte';
    import { onMount } from 'svelte';

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
                error = 'Failed to play audio';
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
        error = 'Failed to load audio';
        isLoading = false;
        isPlaying = false;
    }

    onMount(() => {
        if (autoplay && audioElement) {
            audioElement.play().catch((err) => {
                console.error('Autoplay failed:', err);
            });
        }
    });
</script>

{#if audioUrl}
    <div class="flex items-center gap-3 rounded-lg border border-pink-200 bg-pink-50 p-3 {className}">
        <button
            type="button"
            onclick={togglePlay}
            disabled={isLoading || !!error}
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-pink-500 text-white transition-colors hover:bg-pink-600 disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label={isPlaying ? 'Pause audio' : 'Play audio'}
        >
            {#if isLoading}
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
            {:else if isPlaying}
                <Pause class="h-5 w-5" />
            {:else}
                <Play class="h-5 w-5" />
            {/if}
        </button>

        <div class="flex-1 min-w-0">
            {#if error}
                <p class="text-sm text-red-600">{error}</p>
            {:else}
                <p class="text-sm font-medium text-pink-700">Audio</p>
                <p class="text-xs text-pink-600">Click to play</p>
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
        />
    </div>
{/if}
