<script lang="ts">
    interface CalendarDay {
        id: number;
        day_number: number;
        unlocked_at: string | null;
    }

    interface Props {
        day: CalendarDay;
        onclick?: () => void;
        canUnlock?: boolean;
    }

    let { day, onclick, canUnlock = false }: Props = $props();

    const isUnlocked = $derived(day.unlocked_at !== null);
</script>

<button
    type="button"
    onclick={onclick}
    disabled={!isUnlocked && !canUnlock}
    class="group relative flex aspect-square w-full items-center justify-center overflow-hidden rounded-xl transition-all duration-300 hover:scale-105 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100"
    class:unlocked={isUnlocked}
    class:locked={!isUnlocked}
>
    {#if isUnlocked}
        <!-- Unlocked state with gradient background -->
        <div class="absolute inset-0 bg-gradient-to-br from-pink-400 to-pink-600"></div>
        <div class="relative z-10 flex flex-col items-center gap-2 text-white">
            <span class="text-4xl font-bold">{day.day_number}</span>
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
            class="absolute inset-0"
            style="background-image: repeating-linear-gradient(
                45deg,
                #fce7f3 0px,
                #fce7f3 10px,
                #fbcfe8 10px,
                #fbcfe8 20px
            );"
        ></div>

        <!-- Ribbon effect -->
        <div class="absolute inset-0">
            <div class="absolute left-1/2 top-0 h-full w-4 -translate-x-1/2 bg-pink-400 opacity-80"></div>
            <div class="absolute left-0 top-1/2 h-4 w-full -translate-y-1/2 bg-pink-400 opacity-80"></div>
        </div>

        <!-- Bow on top -->
        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="flex items-center justify-center">
                <div class="h-8 w-8 rounded-full bg-pink-500 shadow-lg"></div>
            </div>
        </div>

        <div class="relative z-10 flex flex-col items-center gap-2">
            <span class="text-4xl font-bold text-pink-600">{day.day_number}</span>
            {#if canUnlock}
                <span class="text-xs font-medium text-pink-600">Tap to open</span>
            {:else}
                <svg class="h-6 w-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="absolute inset-0 bg-white opacity-0 transition-opacity group-hover:opacity-10"></div>
    {/if}
</button>

<style>
    button {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    button:hover:not(:disabled) {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
</style>


