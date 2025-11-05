<script lang="ts">
    interface Gift {
        gift_type: 'text' | 'image_text' | 'product';
        title: string | null;
        content_text: string | null;
        content_image_path: string | null;
    }

    interface Props {
        gift: Gift;
    }

    let { gift }: Props = $props();
</script>

<div class="gift-content">
    {#if gift.title}
        <h3 class="mb-4 text-2xl font-bold text-pink-700">{gift.title}</h3>
    {/if}

    {#if gift.gift_type === 'text'}
        <div class="prose prose-pink max-w-none">
            <p class="whitespace-pre-wrap text-gray-700">{gift.content_text}</p>
        </div>
    {:else if gift.gift_type === 'image_text'}
        <div class="space-y-4">
            {#if gift.content_image_path}
                <img
                    src={`/storage/${gift.content_image_path}`}
                    alt={gift.title || 'Gift image'}
                    class="h-auto w-full rounded-lg object-cover shadow-lg"
                />
            {/if}
            {#if gift.content_text}
                <div class="prose prose-pink max-w-none">
                    <p class="whitespace-pre-wrap text-gray-700">{gift.content_text}</p>
                </div>
            {/if}
        </div>
    {:else if gift.gift_type === 'product'}
        <div class="rounded-lg border-2 border-pink-200 bg-pink-50 p-6">
            <div class="mb-4 flex items-center justify-center">
                <svg
                    class="h-20 w-20 text-pink-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                    />
                </svg>
            </div>
            <div class="text-center">
                <h4 class="mb-2 text-xl font-semibold text-pink-700">Redeemable Gift</h4>
                {#if gift.content_text}
                    <p class="whitespace-pre-wrap text-gray-700">{gift.content_text}</p>
                {/if}
            </div>
        </div>
    {/if}
</div>


