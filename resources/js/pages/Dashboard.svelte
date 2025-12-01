<script lang="ts">
    import { router, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { type BreadcrumbItem } from '@/types';
    import { t, initTranslations } from '@/lib/translations';

    // Initialize translations immediately from page props
    const translations = ($page.props as any)?.translations;
    if (translations) {
        initTranslations(translations);
    }

    const breadcrumbs = $derived<BreadcrumbItem[]>([
        {
            title: t('common.dashboard'),
            href: '/dashboard',
        },
    ]);
</script>

<svelte:head>
    <title>{t('common.dashboard')}</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="space-y-6 p-6">
        <!-- Welcome Hero -->
        <div class="rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 p-8 text-white">
            <h1 class="mb-2 text-3xl font-bold">{t('dashboard.welcome')}</h1>
            <p class="mb-4 text-pink-100 font-serif">{t('dashboard.welcome_description')}</p>
            <Button
                onclick={() => router.visit('/calendars')}
                class="bg-white text-pink-600 hover:bg-pink-50"
            >
                {t('dashboard.view_my_calendars')}
            </Button>
        </div>

        <!-- Quick Actions -->
        <div class="grid gap-4 md:grid-cols-3">
            <Card class="cursor-pointer transition-all hover:shadow-lg" onclick={() => router.visit('/calendars')}>
                <CardHeader>
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-lg bg-pink-100 text-pink-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <CardTitle class="font-serif">{t('dashboard.my_calendars')}</CardTitle>
                    <CardDescription class="font-serif">{t('dashboard.view_and_manage')}</CardDescription>
                </CardHeader>
            </Card>

            <Card class="border-dashed border-pink-200 bg-pink-50/50">
                <CardHeader>
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-lg bg-pink-100 text-pink-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <CardTitle class="text-pink-700 font-serif">{t('dashboard.days')}</CardTitle>
                    <CardDescription class="font-serif">{t('dashboard.each_calendar_has_31_days')}</CardDescription>
                </CardHeader>
            </Card>

            <Card class="border-dashed border-pink-200 bg-pink-50/50">
                <CardHeader>
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-lg bg-pink-100 text-pink-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <CardTitle class="text-pink-700 font-serif">{t('dashboard.unlock_in_december')}</CardTitle>
                    <CardDescription class="font-serif">{t('dashboard.days_unlock_day_by_day')}</CardDescription>
                </CardHeader>
            </Card>
        </div>

        <!-- Features -->
        <Card>
            <CardHeader>
                <CardTitle class="font-serif">{t('dashboard.features')}</CardTitle>
                <CardDescription class="font-serif">{t('dashboard.what_makes_special')}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            ✨
                        </div>
                        <div>
                            <h4 class="font-semibold font-serif">{t('dashboard.confetti_celebrations')}</h4>
                            <p class="text-sm text-muted-foreground font-serif">{t('dashboard.confetti_description')}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            🎁
                        </div>
                        <div>
                            <h4 class="font-semibold font-serif">{t('dashboard.gift_wrap_design')}</h4>
                            <p class="text-sm text-muted-foreground font-serif">{t('dashboard.gift_wrap_description')}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            📱
                        </div>
                        <div>
                            <h4 class="font-semibold font-serif">{t('dashboard.mobile_friendly')}</h4>
                            <p class="text-sm text-muted-foreground font-serif">{t('dashboard.mobile_description')}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            🎨
                        </div>
                        <div>
                            <h4 class="font-semibold font-serif">{t('dashboard.three_gift_types')}</h4>
                            <p class="text-sm text-muted-foreground font-serif">{t('dashboard.three_gift_types_description')}</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</AppLayout>
