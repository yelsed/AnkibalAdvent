<script lang="ts">
    import { router, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { Form } from '@inertiajs/svelte';
    import { t, initTranslations } from '@/lib/translations';

    // Initialize translations from page props
    $effect(() => {
        const translations = ($page.props as any)?.translations;
        if (translations) {
            initTranslations(translations);
        }
    });

    interface CalendarDay {
        id: number;
        day_number: number;
        gift_type: 'text' | 'image_text' | 'product';
        title: string | null;
        content_text: string | null;
        content_image_path: string | null;
        product_code: string | null;
        audio_url: string | null;
        audio_file_id: number | null;
        unlocked_at: string | null;
    }

    interface Calendar {
        id: number;
        title: string;
        year: number;
        description: string | null;
        theme_color: string;
        days: CalendarDay[];
    }

    interface AudioFile {
        id: number;
        name: string;
        url: string;
    }

    interface Props {
        calendar: Calendar;
        audioFiles: AudioFile[];
    }

    let { calendar, audioFiles = [] }: Props = $props();

    let selectedDayIndex = $state(0);
    let selectedDay = $derived(calendar?.days?.[selectedDayIndex]);
    let currentGiftType = $state<string>('text');
    let giftTypeHiddenInput: HTMLInputElement;
    let audioSourceType = $state<'none' | 'library' | 'url'>('none');
    let selectedAudioFileId = $state<number | null>(null);
    let audioUrlInput = $state('');

    // Ensure selectedDayIndex is valid and update currentGiftType when day changes
    $effect(() => {
        if (calendar?.days && calendar.days.length > 0) {
            if (selectedDayIndex >= calendar.days.length) {
                selectedDayIndex = 0;
            }
        }
        if (selectedDay) {
            const newGiftType = selectedDay.gift_type || 'text';
            currentGiftType = newGiftType;
        }
    });

    // Sync hidden input value when currentGiftType changes
    $effect(() => {
        if (giftTypeHiddenInput) {
            giftTypeHiddenInput.value = currentGiftType;
        }
    });

    // Update audio source type when selected day changes
    let previousDayId = $state<number | null>(null);
    $effect(() => {
        // Only update when the day actually changes (by ID)
        if (selectedDay && selectedDay.id !== previousDayId) {
            if (selectedDay.audio_file_id) {
                audioSourceType = 'library';
                selectedAudioFileId = selectedDay.audio_file_id;
                audioUrlInput = '';
            } else if (selectedDay.audio_url) {
                audioSourceType = 'url';
                audioUrlInput = selectedDay.audio_url;
                selectedAudioFileId = null;
            } else {
                audioSourceType = 'none';
                selectedAudioFileId = null;
                audioUrlInput = '';
            }
            previousDayId = selectedDay.id;
        } else if (!selectedDay) {
            previousDayId = null;
        }
    });

    const breadcrumbs = $derived([
        { title: t('common.home'), href: '/' },
        { title: t('common.calendars'), href: '/calendars' },
        { title: calendar.title, href: `/calendars/${calendar.id}` },
        { title: t('common.manage'), href: `/admin/calendars/${calendar.id}/manage` }
    ]);
</script>

{#if calendar}
<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-pink-700 sm:text-4xl">{t('calendar.manage_calendar_days')}</h1>
                <p class="mt-2 text-gray-600">{calendar.title} - {calendar.year}</p>
            </div>
            <Button
                variant="outline"
                onclick={() => router.visit(`/calendars/${calendar.id}`)}
            >
                {t('calendar.back_to_calendar')}
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Day Selector -->
            <Card class="lg:col-span-1">
                <CardHeader>
                    <CardTitle>{t('calendar.select_day')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-5 lg:grid-cols-3">
                        {#each calendar.days || [] as day, index}
                            <button
                                type="button"
                                onclick={() => (selectedDayIndex = index)}
                                class="aspect-square rounded-lg border-2 transition-all hover:scale-105"
                                class:border-pink-500={selectedDayIndex === index}
                                class:bg-pink-50={selectedDayIndex === index}
                                class:border-gray-200={selectedDayIndex !== index}
                                class:bg-green-50={day.unlocked_at && selectedDayIndex !== index}
                            >
                                <div class="flex h-full flex-col items-center justify-center">
                                    <span
                                        class="text-lg font-bold"
                                        class:text-pink-700={selectedDayIndex === index}
                                    >
                                        {day.day_number}
                                    </span>
                                    {#if day.unlocked_at}
                                        <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    {/if}
                                </div>
                            </button>
                        {/each}
                    </div>
                </CardContent>
            </Card>

            <!-- Day Editor -->
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>
                        {#if selectedDay}
                            {t('calendar.edit_day', { day: selectedDay.day_number })}
                        {:else}
                            {t('calendar.select_day')}
                        {/if}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    {#if selectedDay}
                        {#key selectedDay.id}
                            <Form
                                action={`/calendar-days/${selectedDay.id}`}
                                method="post"
                                enctype="multipart/form-data"
                            >
                            {#snippet children({ data, errors, processing }: { data: Record<string, unknown>; errors: Record<string, string>; processing: boolean })}
                                <div class="space-y-4">
                                    <!-- Hidden inputs for method spoofing and gift_type -->
                                    <input type="hidden" name="_method" value="put" />
                                    <input type="hidden" name="gift_type" bind:this={giftTypeHiddenInput} />

                                    <!-- Gift Type -->
                                    <div>
                                        <Label for="gift_type">{t('calendar.gift_type_required')}</Label>
                                        <select
                                            id="gift_type"
                                            bind:value={currentGiftType}
                                            required
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="text">{t('calendar.text_only')}</option>
                                            <option value="image_text">{t('calendar.image_text')}</option>
                                            <option value="product">{t('calendar.product')}</option>
                                        </select>
                                        {#if errors.gift_type}
                                            <p class="mt-1 text-sm text-red-600">{errors.gift_type}</p>
                                        {/if}
                                    </div>

                                    <!-- Title -->
                                    <div>
                                        <Label for="title">{t('calendar.title_optional')}</Label>
                                        <Input
                                            id="title"
                                            name="title"
                                            defaultValue={selectedDay.title || ''}
                                            placeholder={t('calendar.title_placeholder')}
                                        />
                                        {#if errors.title}
                                            <p class="mt-1 text-sm text-red-600">{errors.title}</p>
                                        {/if}
                                    </div>

                                    <!-- Content Text (for text and image_text types) -->
                                    {#if currentGiftType === 'text' || currentGiftType === 'image_text'}
                                        <div>
                                            <Label for="content_text">{t('calendar.content_text')}</Label>
                                            <Textarea
                                                id="content_text"
                                                name="content_text"
                                                defaultValue={selectedDay.content_text || ''}
                                                placeholder={t('calendar.content_text_placeholder')}
                                                rows={6}
                                            />
                                            {#if errors.content_text}
                                                <p class="mt-1 text-sm text-red-600">{errors.content_text}</p>
                                            {/if}
                                        </div>
                                    {/if}

                                    <!-- Image Upload (only for image_text type) -->
                                    {#if currentGiftType === 'image_text'}
                                        <div>
                                            <Label for="content_image">{t('calendar.content_image_required')}</Label>
                                            {#if selectedDay.content_image_path}
                                                <div class="mb-2 rounded-lg border-2 border-green-200 bg-green-50 p-3">
                                                    <p class="mb-2 text-sm font-medium text-green-700">✓ {t('calendar.current_image')}</p>
                                                    <img
                                                        src={`/storage/${selectedDay.content_image_path}`}
                                                        alt={t('common.current')}
                                                        class="h-32 w-auto rounded-lg object-cover"
                                                        style="image-rendering: auto;"
                                                    />
                                                    <p class="mt-2 text-xs text-gray-600">{t('calendar.upload_new_image')}</p>
                                                </div>
                                            {/if}
                                            <Input
                                                id="content_image"
                                                name="content_image"
                                                type="file"
                                                accept="image/*"
                                            />
                                            {#if errors.content_image}
                                                <p class="mt-1 text-sm text-red-600">{errors.content_image}</p>
                                            {/if}
                                        </div>
                                    {/if}

                                    <!-- Product Code (only for product type) -->
                                    {#if currentGiftType === 'product'}
                                        <div>
                                            <Label for="product_code">{t('calendar.product_code_required')}</Label>
                                            <Input
                                                id="product_code"
                                                name="product_code"
                                                defaultValue={selectedDay.product_code || ''}
                                                placeholder={t('calendar.product_code_placeholder')}
                                            />
                                            {#if errors.product_code}
                                                <p class="mt-1 text-sm text-red-600">{errors.product_code}</p>
                                            {/if}
                                        </div>
                                        <div>
                                            <Label for="content_image">{t('calendar.product_image_optional')}</Label>
                                            <Input
                                                id="content_image"
                                                name="content_image"
                                                type="file"
                                                accept="image/*"
                                            />
                                            {#if errors.content_image}
                                                <p class="mt-1 text-sm text-red-600">{errors.content_image}</p>
                                            {/if}
                                            {#if selectedDay.content_image_path}
                                                <div class="mt-2">
                                                    <p class="mb-2 text-sm text-gray-600">{t('calendar.current_image')}</p>
                                                    <img
                                                        src={`/storage/${selectedDay.content_image_path}`}
                                                        alt={t('common.current')}
                                                        class="h-32 w-auto rounded-lg object-cover"
                                                        style="image-rendering: auto;"
                                                    />
                                                </div>
                                            {/if}
                                        </div>
                                        <div>
                                            <Label for="content_text">{t('calendar.product_description_optional')}</Label>
                                            <Textarea
                                                id="content_text"
                                                name="content_text"
                                                defaultValue={selectedDay.content_text || ''}
                                                placeholder={t('calendar.product_description_placeholder')}
                                                rows={4}
                                            />
                                            {#if errors.content_text}
                                                <p class="mt-1 text-sm text-red-600">{errors.content_text}</p>
                                            {/if}
                                        </div>
                                    {/if}

                                    <!-- Audio (optional for all types) -->
                                    <div>
                                        <Label>{t('calendar.audio_optional')}</Label>
                                        <div class="space-y-3">
                                            <!-- Audio Source Type Selection -->
                                            <div class="flex gap-2">
                                                <button
                                                    type="button"
                                                    onclick={() => {
                                                        audioSourceType = 'none';
                                                        selectedAudioFileId = null;
                                                        audioUrlInput = '';
                                                    }}
                                                    class="rounded-md border px-3 py-2 text-sm transition-colors"
                                                    class:border-pink-500={audioSourceType === 'none'}
                                                    class:bg-pink-50={audioSourceType === 'none'}
                                                    class:border-gray-300={audioSourceType !== 'none'}
                                                >
                                                    {t('common.none')}
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick={() => {
                                                        audioSourceType = 'library';
                                                        audioUrlInput = '';
                                                    }}
                                                    class="rounded-md border px-3 py-2 text-sm transition-colors"
                                                    class:border-pink-500={audioSourceType === 'library'}
                                                    class:bg-pink-50={audioSourceType === 'library'}
                                                    class:border-gray-300={audioSourceType !== 'library'}
                                                >
                                                    {t('calendar.from_library')}
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick={() => {
                                                        audioSourceType = 'url';
                                                        selectedAudioFileId = null;
                                                    }}
                                                    class="rounded-md border px-3 py-2 text-sm transition-colors"
                                                    class:border-pink-500={audioSourceType === 'url'}
                                                    class:bg-pink-50={audioSourceType === 'url'}
                                                    class:border-gray-300={audioSourceType !== 'url'}
                                                >
                                                    {t('calendar.audio_url')}
                                                </button>
                                            </div>

                                            <!-- Library Selection -->
                                            {#if audioSourceType === 'library'}
                                                <div>
                                                    <select
                                                        id="audio_file_id"
                                                        name="audio_file_id"
                                                        bind:value={selectedAudioFileId}
                                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        <option value="">{t('calendar.select_audio_file')}</option>
                                                        {#each audioFiles as audioFile}
                                                            <option value={audioFile.id}>{audioFile.name}</option>
                                                        {/each}
                                                    </select>
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        {t('calendar.choose_from_library')}
                                                        <a href="/admin/audio-files" target="_blank" class="text-pink-600 hover:underline"> {t('calendar.manage_library')}</a>
                                                    </p>
                                                    {#if errors.audio_file_id}
                                                        <p class="mt-1 text-sm text-red-600">{errors.audio_file_id}</p>
                                                    {/if}
                                                </div>
                                            {/if}

                                            <!-- URL Input -->
                                            {#if audioSourceType === 'url'}
                                                <div>
                                                    <Input
                                                        id="audio_url"
                                                        name="audio_url"
                                                        type="url"
                                                        bind:value={audioUrlInput}
                                                        placeholder="https://example.com/audio.mp3"
                                                    />
                                                    <p class="mt-1 text-xs text-gray-500">{t('calendar.enter_audio_url')}</p>
                                                    {#if errors.audio_url}
                                                        <p class="mt-1 text-sm text-red-600">{errors.audio_url}</p>
                                                    {/if}
                                                </div>
                                            {/if}

                                            <!-- Hidden inputs to clear the other field -->
                                            {#if audioSourceType === 'library'}
                                                <input type="hidden" name="audio_url" value="" />
                                            {:else if audioSourceType === 'url'}
                                                <input type="hidden" name="audio_file_id" value="" />
                                            {:else}
                                                <input type="hidden" name="audio_url" value="" />
                                                <input type="hidden" name="audio_file_id" value="" />
                                            {/if}
                                        </div>
                                    </div>

                                    <!-- Preview -->
                                    <div class="rounded-lg border-2 border-dashed border-pink-200 bg-pink-50 p-4">
                                        <h4 class="mb-2 font-semibold text-pink-700">{t('common.preview')}</h4>
                                        {#if selectedDay.title}
                                            <h5 class="mb-2 text-lg font-bold text-pink-700">{selectedDay.title}</h5>
                                        {/if}
                                        {#if currentGiftType === 'product' && selectedDay.product_code}
                                            <div class="mb-2 rounded bg-pink-100 p-2">
                                                <p class="text-sm font-semibold text-pink-800">{t('calendar.code')}: {selectedDay.product_code}</p>
                                            </div>
                                        {/if}
                                        {#if (currentGiftType === 'image_text' || currentGiftType === 'product') && selectedDay.content_image_path}
                                            <div class="mb-2">
                                                <img
                                                    src={`/storage/${selectedDay.content_image_path}`}
                                                    alt={t('common.preview')}
                                                    class="h-24 w-auto rounded-lg object-cover"
                                                    style="image-rendering: auto;"
                                                />
                                            </div>
                                        {/if}
                                        {#if selectedDay.content_text}
                                            <p class="whitespace-pre-wrap text-sm text-gray-700">{selectedDay.content_text}</p>
                                        {:else if currentGiftType === 'product'}
                                            <p class="italic text-gray-400">{t('calendar.no_description_yet')}</p>
                                        {:else}
                                            <p class="italic text-gray-400">{t('calendar.no_content_yet')}</p>
                                        {/if}
                                    </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end gap-2 pt-4">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        class="bg-pink-500 hover:bg-pink-600"
                                    >
                                        {processing ? t('common.saving') : t('calendar.save_day', { day: selectedDay.day_number })}
                                    </Button>
                                </div>

                                <!-- Status -->
                                {#if selectedDay.unlocked_at}
                                    <div class="rounded-lg bg-green-50 p-3 text-center">
                                        <p class="text-sm text-green-700">
                                            {t('calendar.day_unlocked_on', { date: new Date(selectedDay.unlocked_at).toLocaleDateString() })}
                                        </p>
                                    </div>
                                {/if}
                                </div>
                            {/snippet}
                            </Form>
                        {/key}
                    {:else}
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <p class="text-gray-500">{t('calendar.please_select_day')}</p>
                        </div>
                    {/if}
                </CardContent>
            </Card>
        </div>

        <!-- Quick Stats -->
        <Card>
            <CardHeader>
                <CardTitle>{t('calendar.calendar_statistics')}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-pink-600">{calendar.days?.length || 0}</p>
                        <p class="text-sm text-gray-600">{t('calendar.total_days')}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">
                            {calendar.days?.filter(d => d.unlocked_at).length || 0}
                        </p>
                        <p class="text-sm text-gray-600">{t('calendar.unlocked')}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-600">
                            {calendar.days?.filter(d => !d.unlocked_at).length || 0}
                        </p>
                        <p class="text-sm text-gray-600">{t('calendar.locked')}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-pink-600">
                            {calendar.days && calendar.days.length > 0
                                ? Math.round((calendar.days.filter(d => d.unlocked_at).length / calendar.days.length) * 100)
                                : 0}%
                        </p>
                        <p class="text-sm text-gray-600">{t('calendar.progress')}</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</AppLayout>
{/if}
