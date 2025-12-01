<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
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
        { title: t('common.home'), href: '/' },
        { title: t('common.admin'), href: '/admin/calendars' },
        { title: t('admin.intro_page'), href: '/admin/intro' },
    ]);
</script>

<svelte:head>
    <title>{t('admin.intro_page')}</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-4xl space-y-8 p-6">
        <Card class="border-pink-200">
            <CardHeader>
                <CardTitle class="text-3xl font-bold text-pink-700">
                    {t('admin.intro_page')}
                </CardTitle>
                <CardDescription class="text-pink-800">
                    {t('admin.intro_page_description', {
                        default:
                            'Pas hier de introductietekst aan die gebruikers zien voordat ze hun kalenderoverzicht openen.',
                    })}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form action="/admin/intro" method="post">
                    {#snippet children({ errors, processing })}
                        <input type="hidden" name="_method" value="put" />

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <Label for="title">
                                    {t('common.title')}
                                </Label>
                                <Input
                                    id="title"
                                    name="title"
                                    type="text"
                                    defaultValue={introPage.title}
                                    required
                                />
                                {#if errors.title}
                                    <p class="text-sm text-red-600">{errors.title}</p>
                                {/if}
                            </div>

                            <div class="space-y-2">
                                <Label for="body">
                                    {t('common.content')}
                                </Label>
                                <Textarea
                                    id="body"
                                    name="body"
                                    rows={8}
                                    defaultValue={introPage.body}
                                    class="leading-relaxed"
                                    required
                                />
                                {#if errors.body}
                                    <p class="text-sm text-red-600">{errors.body}</p>
                                {/if}
                                <p class="text-xs text-pink-800/80">
                                    {t('admin.intro_page_hint', {
                                        default:
                                            'Schrijf in warme, moederlijke taal. Je kunt meerdere alinea’s gebruiken; regeleinden worden netjes getoond.',
                                    })}
                                </p>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <Button
                                    type="submit"
                                    class="bg-pink-500 hover:bg-pink-600"
                                    disabled={processing}
                                >
                                    {processing ? t('common.saving') : t('common.save')}
                                </Button>
                            </div>
                        </div>
                    {/snippet}
                </Form>
            </CardContent>
        </Card>
    </div>
</AppLayout>
