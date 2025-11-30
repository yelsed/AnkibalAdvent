<script lang="ts">
    import { router, Form } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
    import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
    import { toast } from 'svelte-sonner';
    import { page } from '@inertiajs/svelte';
    import { t, initTranslations } from '@/lib/translations';
    import { themes, defaultSeasonalRanges } from '@/lib/themes';

    // Initialize translations immediately from page props
    const translations = ($page.props as any)?.translations;
    if (translations) {
        initTranslations(translations);
    }

    interface User {
        id: number;
        name: string;
        email: string;
    }

    interface Calendar {
        id: number;
        title: string;
        year: number;
        description: string | null;
        theme_color: string;
        audio_url: string | null;
        created_at: string;
        owner: User;
        recipient?: User;
        user?: User; // backward compatibility
    }

    interface Props {
        calendars: Calendar[];
        users: User[];
    }

    let { calendars, users }: Props = $props();

    let showCreateDialog = $state(false);
    let inviteNewUser = $state(false);
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
            inviteNewUser = false;
            themeType = 'single';
            secondaryColor = '#fbbf24';
            seasonalTheme = 'kerst';
        }
        previousDialogState = showCreateDialog;
    });

    // Breadcrumbs - must be defined after translations are initialized
    const breadcrumbs = $derived([
        { title: t('common.home'), href: '/' },
        { title: t('common.admin'), href: '/admin/calendars' }
    ]);
</script>

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-7xl space-y-8 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-pink-700">📅 Calendar Management</h1>
                <p class="mt-2 text-gray-600">Create and manage advent calendars for users</p>
            </div>

            <Dialog bind:open={showCreateDialog}>
                <DialogHeader class="sr-only">
                    <DialogTitle>Create New Calendar</DialogTitle>
                </DialogHeader>
                <DialogTrigger>
                    <Button class="bg-pink-500 hover:bg-pink-600">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Calendar
                    </Button>
                </DialogTrigger>
                <DialogContent class="max-w-2xl">
                    {#snippet children()}
                        <DialogHeader>
                            <DialogTitle>Create New Calendar</DialogTitle>
                            <DialogDescription>
                                Create a new advent calendar and assign it to a user. All 31 days will be created automatically.
                            </DialogDescription>
                        </DialogHeader>
                        {#key formKey}
                            <Form
                                action="/admin/calendars"
                                method="post"
                                resetOnSuccess
                                onSuccess={() => {
                                    showCreateDialog = false;
                                    inviteNewUser = false;
                                    toast.success('Calendar created successfully!');
                                }}
                                onError={() => {
                                    toast.error('Failed to create calendar');
                                }}
                            >
                                {#snippet children({ data, errors, processing }: { data: Record<string, any>; errors: Record<string, string>; processing: boolean })}
                                    <div class="space-y-4">
                                        <!-- User Selection Type -->
                                        <div>
                                            <Label>Toewijzen aan</Label>
                                            <div class="mt-2 flex gap-4">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input
                                                        type="radio"
                                                        name="user_type"
                                                        value="existing"
                                                        checked={!inviteNewUser}
                                                        onchange={() => {
                                                            inviteNewUser = false;
                                                        }}
                                                        class="h-4 w-4 text-pink-600"
                                                    />
                                                    <span class="text-sm">Bestaande gebruiker</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input
                                                        type="radio"
                                                        name="user_type"
                                                        value="new"
                                                        checked={inviteNewUser}
                                                        onchange={() => {
                                                            inviteNewUser = true;
                                                        }}
                                                        class="h-4 w-4 text-pink-600"
                                                    />
                                                    <span class="text-sm">Nieuwe gebruiker uitnodigen</span>
                                                </label>
                                            </div>
                                        </div>

                                        {#if inviteNewUser}
                                            <!-- New User Invitation -->
                                            <input type="hidden" name="invite_new_user" value="1" />
                                            <div>
                                                <Label for="email">E-mail adres *</Label>
                                                <Input
                                                    id="email"
                                                    name="email"
                                                    type="email"
                                                    defaultValue=""
                                                    placeholder="gebruiker@example.com"
                                                    required
                                                />
                                                {#if errors.email}
                                                    <p class="mt-1 text-sm text-red-600">{errors.email}</p>
                                                {/if}
                                            </div>

                                            <div>
                                                <Label for="name">Naam *</Label>
                                                <Input
                                                    id="name"
                                                    name="name"
                                                    type="text"
                                                    defaultValue=""
                                                    placeholder="Volledige naam"
                                                    required
                                                />
                                                {#if errors.name}
                                                    <p class="mt-1 text-sm text-red-600">{errors.name}</p>
                                                {/if}
                                            </div>
                                        {:else}
                                            <!-- Existing User Selection -->
                                            <input type="hidden" name="invite_new_user" value="0" />
                                            <div>
                                                <Label for="user_id">Selecteer gebruiker *</Label>
                                                <select
                                                    id="user_id"
                                                    name="user_id"
                                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                                    required
                                                >
                                                    <option value="">Selecteer een gebruiker...</option>
                                                    {#each users as user}
                                                        <option value={user.id}>{user.name} ({user.email})</option>
                                                    {/each}
                                                </select>
                                                {#if errors.user_id}
                                                    <p class="mt-1 text-sm text-red-600">{errors.user_id}</p>
                                                {/if}
                                            </div>
                                        {/if}

                                        <div>
                                            <Label for="title">Title *</Label>
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
                                            <Label for="year">Year *</Label>
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
                                            <Label>Theme Type</Label>
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
                                                            {@const dayRange = Array.isArray(range.days) ? range.days : range.days}
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

                                        <div>
                                            <Label for="audio_url">Audio URL (optional)</Label>
                                            <Input
                                                id="audio_url"
                                                name="audio_url"
                                                type="url"
                                                defaultValue=""
                                                placeholder="https://example.com/audio.mp3"
                                            />
                                            <p class="mt-1 text-xs text-gray-500">Enter a URL to an audio file that will play on the calendar page</p>
                                            {#if errors.audio_url}
                                                <p class="mt-1 text-sm text-red-600">{errors.audio_url}</p>
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
        </div>

        <!-- Calendars Table -->
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
                    <p class="mb-4 text-gray-500">Create your first calendar to get started!</p>
                </CardContent>
            </Card>
        {:else}
            <Card>
                <CardHeader>
                    <CardTitle>All Calendars ({calendars.length})</CardTitle>
                    <CardDescription>Manage calendars and assign them to users</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Title</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Year</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Owner</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Created</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each calendars as calendar}
                                    <tr class="border-b hover:bg-pink-50 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="h-4 w-4 rounded-full"
                                                    style="background-color: {calendar.theme_color}"
                                                ></div>
                                                <span class="font-medium">{calendar.title}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{calendar.year}</td>
                                        <td class="px-4 py-3">
                                            <div>
                                                {#if calendar.owner}
                                                    <div class="font-medium text-gray-900">{calendar.owner.name}</div>
                                                    <div class="text-sm text-gray-500">{calendar.owner.email}</div>
                                                {:else if calendar.user}
                                                    <div class="font-medium text-gray-900">{calendar.user.name}</div>
                                                    <div class="text-sm text-gray-500">{calendar.user.email}</div>
                                                {/if}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {new Date(calendar.created_at).toLocaleDateString()}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onclick={() => router.visit(`/calendars/${calendar.id}`)}
                                                    class="border-pink-200 text-pink-600 hover:bg-pink-50"
                                                >
                                                    View
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onclick={() => router.visit(`/admin/calendars/${calendar.id}/manage`)}
                                                    class="border-pink-500 text-pink-600 hover:bg-pink-50"
                                                >
                                                    Manage Content
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        {/if}
    </div>
</AppLayout>
