<script lang="ts">
    import { getThemeColors } from '@/lib/colors';

    interface Gift {
        gift_type: 'text' | 'image_text' | 'product';
        title: string | null;
        content_text: string | null;
        content_image_path: string | null;
        product_code: string | null;
    }

    interface Props {
        gift: Gift;
        themeColor?: string;
    }

    let { gift, themeColor = '#ec4899' }: Props = $props();

    const themeColors = $derived(getThemeColors(themeColor));
</script>

<div class="gift-content">
    {#if gift.title}
        <h3 class="mb-4 text-2xl font-bold font-serif" style="color: {themeColors.darker};">{gift.title}</h3>
    {/if}

    {#if gift.gift_type === 'text'}
        <div class="space-y-4">
            <div class="prose prose-pink max-w-none">
                <p class="whitespace-pre-wrap text-gray-700 font-serif">{gift.content_text}</p>
            </div>
            {#if gift.product_code}
                <div
                    class="rounded-lg border-2 p-4"
                    style="background-color: {themeColors.light}; border-color: {themeColors.medium};"
                >
                    <p class="text-sm font-medium text-gray-600 mb-1">Coupon Code:</p>
                    <p class="text-2xl font-bold tracking-wider font-mono" style="color: {themeColors.darker};">
                        {gift.product_code}
                    </p>
                </div>
            {/if}
        </div>
    {:else if gift.gift_type === 'image_text'}
        <div class="space-y-4">
            {#if gift.content_image_path}
                <img
                    src={`/storage/${gift.content_image_path}`}
                    alt={gift.title || 'Gift image'}
                    class="h-auto w-full rounded-lg object-cover max-h-128 shadow-lg"
                    loading="eager"
                />
            {/if}
            {#if gift.content_text}
                <div class="prose prose-pink max-w-none">
                    <p class="whitespace-pre-wrap text-gray-700 font-serif">{gift.content_text}</p>
                </div>
            {/if}
            {#if gift.product_code}
                <div
                    class="rounded-lg border-2 p-4"
                    style="background-color: {themeColors.light}; border-color: {themeColors.medium};"
                >
                    <p class="text-sm font-medium text-gray-600 mb-1">Coupon Code:</p>
                    <p class="text-2xl font-bold tracking-wider font-mono" style="color: {themeColors.darker};">
                        {gift.product_code}
                    </p>
                </div>
            {/if}
        </div>
    {:else if gift.gift_type === 'product'}
        <div
            class="rounded-lg border-2 p-6"
            style="background-color: {themeColors.light}; border-color: {themeColors.lighter};"
        >
            <div class="space-y-4">
                {#if gift.content_image_path}
                    <div class="flex items-center justify-center">
                        <img
                            src={`/storage/${gift.content_image_path}`}
                            alt={gift.title || 'Product image'}
                            class="h-auto max-w-full rounded-lg object-cover shadow-lg"
                            loading="eager"
                        />
                    </div>
                {:else}
                    <div class="mb-4 flex items-center justify-center">
                        <svg
                            class="h-20 w-20"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            style="color: {themeColors.base};"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                            />
                        </svg>
                    </div>
                {/if}
                <div class="text-center space-y-4">
                    <h4 class="text-xl font-semibold font-serif" style="color: {themeColors.darker};">Redeemable Gift</h4>
                    {#if gift.product_code}
                        <div
                            class="rounded-lg bg-white border-2 p-4"
                            style="border-color: {themeColors.medium};"
                        >
                            <p class="text-sm font-medium text-gray-600 mb-1">Your Code:</p>
                            <p class="text-2xl font-bold tracking-wider font-mono" style="color: {themeColors.darker};">
                                {gift.product_code}
                            </p>
                        </div>
                    {/if}
                    {#if gift.content_text}
                        <p class="whitespace-pre-wrap text-gray-700 font-serif">{gift.content_text}</p>
                    {/if}
                </div>
            </div>
        </div>
    {/if}
</div>
