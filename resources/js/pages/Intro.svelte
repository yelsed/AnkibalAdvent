<script lang="ts">
    import { router, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import type { BreadcrumbItem } from '@/types';
    import { t, initTranslations } from '@/lib/translations';

    interface IntroPage {
        id: number;
        title: string;
        body: string;
    }

    interface Props {
        introPage: IntroPage;
    }

    let { introPage }: Props = $props();

    // Initialize translations from page props
    $effect(() => {
        const translations = ($page.props as any)?.translations;
        if (translations) {
            initTranslations(translations);
        }
    });

    const breadcrumbs = $derived<BreadcrumbItem[]>([
        {
            title: t('common.home'),
            href: '/intro',
        },
    ]);
</script>

<svelte:head>
    <title>{introPage.title}</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="mx-auto flex min-h-[60vh] max-w-3xl flex-col gap-8 p-6">
        <Card tiltEnabled={true} class="border-pink-100 bg-pink-50/60 transition-all hover:shadow-lg">
            <CardHeader>
                <CardTitle class="text-3xl font-bold text-pink-700 font-display">
                    {introPage.title}
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4 text-pink-900">
                <p class="whitespace-pre-line leading-relaxed font-serif">
                    {introPage.body}
                </p>

                <div class="mt-6 rounded-xl bg-white/70 p-4 text-sm text-pink-800 shadow-sm">
                    <p class="font-serif">
                        {t('calendar.intro_highlight', {
                            default: 'Je krijgt een persoonlijke adventskalender met 31 liefdevolle dagen vol kleine cadeautjes, woorden en verrassingen.',
                        })}
                    </p>
                </div>
            </CardContent>
        </Card>

        <div class="flex flex-col items-center gap-4">
            <Button
                size="lg"
                class="h-12 rounded-full bg-pink-500 px-8 text-base font-semibold text-white shadow-lg shadow-pink-300/60 transition-all hover:bg-pink-600 hover:shadow-pink-400/70"
                onclick={() => router.visit('/calendars')}
            >
                🎁 {t('calendar.go_to_my_calendars', { default: 'Ga naar mijn kalenders' })}
            </Button>
            <p class="text-xs text-pink-700/80 font-serif">
                {t('calendar.intro_cta_hint', {
                    default: 'Daar zie je al jouw adventskalenders en kun je de dagen één voor één openen in december.',
                })}
            </p>
        </div>
    </div>
</AppLayout>
