<script lang="ts">
    import { router, Form, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

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

    // Reset form when dialog opens
    $effect(() => {
        if (showCreateDialog) {
            formKey++;
        }
    });
</script>

<AppLayout
    breadcrumbs={[
        { title: 'Home', href: '/' },
        { title: 'Calendars', href: '/calendars' }
    ]}
>
    <div class="mx-auto max-w-7xl space-y-8 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-pink-700">🎄 My Advent Calendars</h1>
                <p class="mt-2 text-gray-600">
                    {isAdmin ? 'Create and manage your festive advent calendars' : 'View your advent calendars'}
                </p>
            </div>

            {#if isAdmin}
                <Dialog bind:open={showCreateDialog}>
                <DialogTrigger>
                    <Button class="bg-pink-500 hover:bg-pink-600">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Calendar
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    {#snippet children()}
                        <DialogHeader>
                            <DialogTitle>Create New Advent Calendar</DialogTitle>
                            <DialogDescription>
                                Fill in the details to create a new advent calendar with 31 days.
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
                                        <Label for="title">Title</Label>
                                        <Input
                                            id="title"
                                            name="title"
                                            defaultValue=""
                                            placeholder="My Advent Calendar 2025"
                                            required
                                        />
                                        {#if errors.title}
                                            <p class="mt-1 text-sm text-red-600">{errors.title}</p>
                                        {/if}
                                    </div>

                                    <div>
                                        <Label for="year">Year</Label>
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
                                        <Label for="description">Description (optional)</Label>
                                        <Textarea
                                            id="description"
                                            name="description"
                                            defaultValue=""
                                            placeholder="A special advent calendar for..."
                                            rows={3}
                                        />
                                        {#if errors.description}
                                            <p class="mt-1 text-sm text-red-600">{errors.description}</p>
                                        {/if}
                                    </div>

                                    <div>
                                        <Label for="theme_color">Theme Color</Label>
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

                                    <div class="flex justify-end gap-2 pt-4">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onclick={() => (showCreateDialog = false)}
                                        >
                                            Cancel
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            class="bg-pink-500 hover:bg-pink-600"
                                        >
                                            {processing ? 'Creating...' : 'Create Calendar'}
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
                    <h3 class="mb-2 text-xl font-semibold text-gray-700">No calendars yet</h3>
                    <p class="mb-4 text-gray-500">Create your first advent calendar to get started!</p>
                </CardContent>
            </Card>
        {:else}
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {#each calendars as calendar}
                    <Card
                        class="group cursor-pointer transition-all hover:scale-105 hover:shadow-lg"
                        onclick={() => router.visit(`/calendars/${calendar.id}`)}
                    >
                        <CardHeader style="background: linear-gradient(135deg, {calendar.theme_color}20, {calendar.theme_color}10);">
                            <CardTitle class="text-2xl">{calendar.title}</CardTitle>
                            <CardDescription class="text-base">
                                {calendar.year} • Created {new Date(calendar.created_at).toLocaleDateString()}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="pt-6">
                            {#if calendar.description}
                                <p class="text-sm text-gray-600">{calendar.description}</p>
                            {:else}
                                <p class="text-sm italic text-gray-400">No description</p>
                            {/if}
                            <div class="mt-4 flex items-center gap-2">
                                <div
                                    class="h-6 w-6 rounded-full border-2 border-gray-200"
                                    style="background-color: {calendar.theme_color}"
                                ></div>
                                <span class="text-sm text-gray-500">31 days</span>
                            </div>
                        </CardContent>
                    </Card>
                {/each}
            </div>
        {/if}
    </div>
</AppLayout>
