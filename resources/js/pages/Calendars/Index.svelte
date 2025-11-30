<script lang="ts">
    import { router, Form, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
    import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
    import { t, initTranslations } from '@/lib/translations';
    import { themes, defaultSeasonalRanges } from '@/lib/themes';

    // Initialize translations immediately from page props
    const translations = ($page.props as any)?.translations;
    if (translations) {
        initTranslations(translations);
    }

    interface Calendar {
        id: number;
        title: string;
        year: number;
        description: string | null;
        theme_color: string;
        created_at: string;
    }

    interface Props {
        calendars: Calendar[];
    }

    let { calendars }: Props = $props();

    const user = $derived($page.props.auth.user);
    const isAdmin = $derived(user?.is_admin ?? false);

    let showCreateDialog = $state(false);
    let formKey = $state(0);
    let themeType = $state<'single' | 'dual' | 'seasonal'>('single');
    let secondaryColor = $state('#fbbf24');
    let seasonalTheme = $state('kerst');

    // Reset form when dialog opens - prevent infinite loops by tracking previous state
    let previousDialogState = $state(false);
    $effect(() => {
        // Only reset when dialog changes from closed to open
        if (showCreateDialog && !previousDialogState) {
            formKey++;
            themeType = 'single';
            secondaryColor = '#fbbf24';
            seasonalTheme = 'kerst';
        }
        previousDialogState = showCreateDialog;
    });

    const breadcrumbs = $derived([
        { title: t('common.home'), href: '/' },
        { title: t('common.calendars'), href: '/calendars' }
    ]);
</script>

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-7xl space-y-8 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-pink-700">🎄 {t('calendar.my_advent_calendars')}</h1>
                <p class="mt-2 text-gray-600">
                    {isAdmin ? t('calendar.create_and_manage') : t('calendar.view_calendars')}
                </p>
            </div>

            {#if isAdmin}
                <Dialog bind:open={showCreateDialog}>
                <DialogTrigger>
                    <Button class="bg-pink-500 hover:bg-pink-600">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {t('calendar.create_new_calendar')}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    {#snippet children()}
                        <DialogHeader>
                            <DialogTitle>{t('calendar.create_new_calendar')}</DialogTitle>
                            <DialogDescription>
                                {t('calendar.calendar_details')}
                            </DialogDescription>
                        </DialogHeader>
                        {#key formKey}
                            <Form
                                action="/calendars"
                                method="post"
                                resetOnSuccess
                                onSuccess={() => {
                                    showCreateDialog = false;
                                }}
                            >
                            {#snippet children({ errors, processing }: { errors: Record<string, string>; processing: boolean })}
                                <div class="space-y-4">
                                    <div>
                                        <Label for="title">{t('common.title')}</Label>
                                        <Input
                                            id="title"
                                            name="title"
                                            defaultValue=""
                                            placeholder={t('calendar.calendar_title_placeholder')}
                                            required
                                        />
                                        {#if errors.title}
                                            <p class="mt-1 text-sm text-red-600">{errors.title}</p>
                                        {/if}
                                    </div>

                                    <div>
                                        <Label for="year">{t('common.year')}</Label>
                                        <Input
                                            id="year"
                                            name="year"
                                            type="number"
                                            defaultValue={new Date().getFullYear().toString()}
                                            placeholder={new Date().getFullYear().toString()}
                                            required
                                        />
                                        {#if errors.year}
                                            <p class="mt-1 text-sm text-red-600">{errors.year}</p>
                                        {/if}
                                    </div>

                                    <div>
                                        <Label for="description">{t('calendar.description_optional')}</Label>
                                        <Textarea
                                            id="description"
                                            name="description"
                                            defaultValue=""
                                            placeholder={t('calendar.description_placeholder')}
                                            rows={3}
                                        />
                                        {#if errors.description}
                                            <p class="mt-1 text-sm text-red-600">{errors.description}</p>
                                        {/if}
                                    </div>

                                    <div>
                                        <Label>{t('calendar.theme_type')}</Label>
                                        <RadioGroup bind:value={themeType} class="mt-2">
                                            <div class="flex items-center space-x-2">
                                                <RadioGroupItem value="single" id="theme-single" />
                                                <Label for="theme-single" class="cursor-pointer">Single Color</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <RadioGroupItem value="dual" id="theme-dual" />
                                                <Label for="theme-dual" class="cursor-pointer">Dual Color</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <RadioGroupItem value="seasonal" id="theme-seasonal" />
                                                <Label for="theme-seasonal" class="cursor-pointer">Seasonal Theme</Label>
                                            </div>
                                        </RadioGroup>
                                        <input type="hidden" name="theme_type" value={themeType} />
                                        {#if errors.theme_type}
                                            <p class="mt-1 text-sm text-red-600">{errors.theme_type}</p>
                                        {/if}
                                    </div>

                                    {#if themeType === 'single'}
                                        <div>
                                            <Label for="theme_color">{t('calendar.theme_color')}</Label>
                                            <Input
                                                id="theme_color"
                                                name="theme_color"
                                                type="color"
                                                defaultValue="#ec4899"
                                            />
                                            {#if errors.theme_color}
                                                <p class="mt-1 text-sm text-red-600">{errors.theme_color}</p>
                                            {/if}
                                        </div>
                                    {:else if themeType === 'dual'}
                                        <div>
                                            <Label for="theme_color">Primary Color</Label>
                                            <Input
                                                id="theme_color"
                                                name="theme_color"
                                                type="color"
                                                defaultValue="#ec4899"
                                            />
                                            {#if errors.theme_color}
                                                <p class="mt-1 text-sm text-red-600">{errors.theme_color}</p>
                                            {/if}
                                        </div>
                                        <div>
                                            <Label for="secondary_color">Secondary Color</Label>
                                            <Input
                                                id="secondary_color"
                                                name="secondary_color"
                                                type="color"
                                                bind:value={secondaryColor}
                                                defaultValue="#fbbf24"
                                            />
                                            {#if errors.secondary_color}
                                                <p class="mt-1 text-sm text-red-600">{errors.secondary_color}</p>
                                            {/if}
                                        </div>
                                    {:else if themeType === 'seasonal'}
                                        <div class="space-y-4">
                                            <div>
                                                <Label>Seasonal Theme Configuration</Label>
                                                <p class="mt-1 text-sm text-gray-600">
                                                    De kalender gebruikt automatisch verschillende themes per periode:
                                                </p>
                                                <div class="mt-3 space-y-2 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                                    {#each defaultSeasonalRanges as range}
                                                        {@const theme = themes[range.theme]}
                                                        {@const dayRange = range.days}
                                                        {@const firstDay = Math.min(...dayRange)}
                                                        {@const lastDay = Math.max(...dayRange)}
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="h-8 w-8 rounded-full border-2 border-white shadow-sm"
                                                                style="background-color: {theme.primary};"
                                                            ></div>
                                                            <div class="flex-1">
                                                                <p class="font-medium text-gray-900">{theme.name}</p>
                                                                <p class="text-sm text-gray-600">
                                                                    Dagen {firstDay === lastDay ? firstDay : `${firstDay}-${lastDay}`}
                                                                    {#if theme.secondary}
                                                                        <span class="ml-2">
                                                                            (
                                                                            <span
                                                                class="inline-block h-3 w-3 rounded-full align-middle"
                                                                style="background-color: {theme.primary};"
                                                            ></span>
                                                                            +
                                                                            <span
                                                                class="inline-block h-3 w-3 rounded-full align-middle"
                                                                style="background-color: {theme.secondary};"
                                                            ></span>
                                                                            )
                                                                        </span>
                                                                    {/if}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    {/each}
                                                </div>
                                                <input
                                                    type="hidden"
                                                    name="seasonal_config"
                                                    value={JSON.stringify({ ranges: defaultSeasonalRanges })}
                                                />
                                                {#if errors.seasonal_config}
                                                    <p class="mt-1 text-sm text-red-600">{errors.seasonal_config}</p>
                                                {/if}
                                            </div>
                                        </div>
                                    {/if}

                                    <div class="flex justify-end gap-2 pt-4">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onclick={() => (showCreateDialog = false)}
                                        >
                                            {t('common.cancel')}
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            class="bg-pink-500 hover:bg-pink-600"
                                        >
                                            {processing ? t('common.creating') : t('calendar.create_calendar')}
                                        </Button>
                                    </div>
                                </div>
                            {/snippet}
                            </Form>
                        {/key}
                    {/snippet}
                </DialogContent>
            </Dialog>
            {/if}
        </div>

        <!-- Calendars Grid -->
        {#if calendars.length === 0}
            <Card class="border-dashed border-pink-200">
                <CardContent class="flex flex-col items-center justify-center py-16">
                    <svg
                        class="mb-4 h-24 w-24 text-pink-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <h3 class="mb-2 text-xl font-semibold text-gray-700">{t('calendar.no_calendars_yet')}</h3>
                    <p class="mb-4 text-gray-500">{t('calendar.create_first_calendar')}</p>
                </CardContent>
            </Card>
        {:else}
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {#each calendars as calendar}
                    <Card
                        tiltEnabled={true}
                        style="border-color: {calendar.theme_color}"
                        class="group transition-all hover:shadow-lg"
                        onclick={() => router.visit(`/calendars/${calendar.id}`)}
                    >
                        <CardHeader style="background: linear-gradient(135deg, {calendar.theme_color}20, {calendar.theme_color}10);">
                            <CardTitle class="text-2xl">{calendar.title}</CardTitle>
                            <CardDescription class="text-base">
                                {calendar.year} • {t('calendar.created')} {new Date(calendar.created_at).toLocaleDateString()}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="pt-6">
                            {#if calendar.description}
                                <p class="text-sm text-gray-600">{calendar.description}</p>
                            {:else}
                                <p class="text-sm italic text-gray-400">{t('calendar.no_description')}</p>
                            {/if}
                            <div class="mt-4 flex items-center gap-2">
                                <div
                                    class="h-6 w-6 rounded-full border-2 border-gray-200"
                                    style="background-color: {calendar.theme_color}"
                                ></div>
                                <span class="text-sm text-gray-500">31 {t('calendar.days')}</span>
                            </div>
                        </CardContent>
                    </Card>
                {/each}
            </div>
        {/if}
    </div>
</AppLayout>
