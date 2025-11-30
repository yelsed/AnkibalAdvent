<script lang="ts">
    import { Drawer } from 'vaul-svelte';
    import { page } from '@inertiajs/svelte';
    import GiftContent from './GiftContent.svelte';
    import ConfettiEffect from './ConfettiEffect.svelte';
    import AudioPlayer from './AudioPlayer.svelte';
    import Bow from './Bow.svelte';
    import { getThemeColors } from '@/lib/colors';
    import { t, initTranslations } from '@/lib/translations';

    // Initialize translations immediately from page props
    const translations = ($page.props as any)?.translations;
    if (translations) {
        initTranslations(translations);
    }

    interface CalendarDay {
        id: number;
        day_number: number;
        gift_type: 'text' | 'image_text' | 'product';
        title: string | null;
        content_text: string | null;
        content_image_path: string | null;
        product_code: string | null;
        audio_url: string | null;
        unlocked_at: string | null;
    }

    interface Props {
        day: CalendarDay | null;
        open?: boolean;
        onOpenChange?: (open: boolean) => void;
        justUnlocked?: boolean;
        themeColor?: string;
        secondaryColor?: string | null;
    }

    let { day, open = $bindable(false), onOpenChange, justUnlocked = $bindable(false), themeColor = '#ec4899', secondaryColor = null }: Props = $props();

    const themeColors = $derived(getThemeColors(themeColor, secondaryColor));

    let triggerConfetti = $state(false);

    $effect(() => {
        if (open && justUnlocked) {
            triggerConfetti = true;
            justUnlocked = false;
        }
    });
</script>

<ConfettiEffect bind:trigger={triggerConfetti} themeColor={themeColor} secondaryColor={secondaryColor} />

<Drawer.Root bind:open onOpenChange={onOpenChange}>
    <Drawer.Portal>
        <Drawer.Overlay class="fixed inset-0 z-[100] bg-black/40" />
        <Drawer.Content
            class="fixed inset-x-0 bottom-0 z-[100] mt-24 flex h-auto max-h-[90vh] flex-col rounded-t-[10px] border bg-white overflow-visible"
        >
            <!-- Decorative bow at the top - positioned to sit above the modal -->
            <div class="absolute left-1/2 top-0 z-40 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                <Bow width={80} height={80} class="drop-shadow-xl" themeColor={themeColor} secondaryColor={secondaryColor} />
            </div>

            <div
                class="mx-auto mt-4 h-2 w-[100px] rounded-full"
                style="background-color: {themeColors.lighter};"
            ></div>

            <div class="flex-1 overflow-y-auto p-6">
                {#if day}
                    <div class="mx-auto max-w-2xl">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-3xl font-bold" style="color: {themeColors.dark};">
                                {t('calendar.day_number', { number: day.day_number })}
                            </h2>
                            <div class="rounded-full px-4 py-2" style="background-color: {themeColors.light};">
                                <span class="text-sm font-medium" style="color: {themeColors.darker};">
                                    {new Date(day.unlocked_at!).toLocaleDateString()}
                                </span>
                            </div>
                        </div>

                        {#if day.audio_url}
                            <div class="mb-6">
                                <AudioPlayer audioUrl={day.audio_url} autoplay={true} themeColor={themeColor} />
                            </div>
                        {/if}

                        <GiftContent gift={day} themeColor={themeColor} />
                    </div>
                {/if}
            </div>

            <div class="border-t p-4">
                <button
                    type="button"
                    onclick={() => (open = false)}
                    class="w-full rounded-lg px-4 py-3 font-semibold text-white transition-colors"
                    style="background-color: {themeColors.base};"
                    onmouseenter={(e) => {
                        e.currentTarget.style.backgroundColor = themeColors.dark;
                    }}
                    onmouseleave={(e) => {
                        e.currentTarget.style.backgroundColor = themeColors.base;
                    }}
                >
                    {t('common.close')}
                </button>
            </div>
        </Drawer.Content>
    </Drawer.Portal>
</Drawer.Root>
