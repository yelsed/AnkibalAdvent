<script lang="ts">
    import { router, useForm } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
    import { toast } from 'svelte-sonner';

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
        created_at: string;
        user: User;
    }

    interface Props {
        calendars: Calendar[];
        users: User[];
    }

    let { calendars, users }: Props = $props();

    let showCreateDialog = $state(false);

    const form = useForm({
        user_id: '',
        title: '',
        year: new Date().getFullYear(),
        description: '',
        theme_color: '#ec4899'
    });

    function submitForm() {
        form.post('/admin/calendars', {
            onSuccess: () => {
                showCreateDialog = false;
                form.reset();
                form.year = new Date().getFullYear();
                form.theme_color = '#ec4899';
                toast.success('Calendar created successfully!');
            },
            onError: () => {
                toast.error('Failed to create calendar');
            }
        });
    }
</script>

<AppLayout
    breadcrumbs={[
        { title: 'Home', href: '/' },
        { title: 'Admin', href: '/admin/calendars' }
    ]}
>
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
                        <form onsubmit={(e) => { e.preventDefault(); submitForm(); }}>
                            <div class="space-y-4">
                                <!-- User Selection -->
                                <div>
                                    <Label for="user_id">Assign to User *</Label>
                                    <select
                                        id="user_id"
                                        name="user_id"
                                        bind:value={form.user_id}
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                    >
                                        <option value="">Select a user...</option>
                                        {#each users as user}
                                            <option value={user.id}>{user.name} ({user.email})</option>
                                        {/each}
                                    </select>
                                    {#if form.errors.user_id}
                                        <p class="mt-1 text-sm text-red-600">{form.errors.user_id}</p>
                                    {/if}
                                </div>

                                <div>
                                    <Label for="title">Title *</Label>
                                    <Input
                                        id="title"
                                        name="title"
                                        bind:value={form.title}
                                        placeholder="My Advent Calendar 2025"
                                        required
                                    />
                                    {#if form.errors.title}
                                        <p class="mt-1 text-sm text-red-600">{form.errors.title}</p>
                                    {/if}
                                </div>

                                <div>
                                    <Label for="year">Year *</Label>
                                    <Input
                                        id="year"
                                        name="year"
                                        type="number"
                                        bind:value={form.year}
                                        placeholder={new Date().getFullYear().toString()}
                                        required
                                    />
                                    {#if form.errors.year}
                                        <p class="mt-1 text-sm text-red-600">{form.errors.year}</p>
                                    {/if}
                                </div>

                                <div>
                                    <Label for="description">Description (optional)</Label>
                                    <Textarea
                                        id="description"
                                        name="description"
                                        bind:value={form.description}
                                        placeholder="A special advent calendar for..."
                                        rows={3}
                                    />
                                    {#if form.errors.description}
                                        <p class="mt-1 text-sm text-red-600">{form.errors.description}</p>
                                    {/if}
                                </div>

                                <div>
                                    <Label for="theme_color">Theme Color</Label>
                                    <Input
                                        id="theme_color"
                                        name="theme_color"
                                        type="color"
                                        bind:value={form.theme_color}
                                    />
                                    {#if form.errors.theme_color}
                                        <p class="mt-1 text-sm text-red-600">{form.errors.theme_color}</p>
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
                                        disabled={form.processing}
                                        class="bg-pink-500 hover:bg-pink-600"
                                    >
                                        {form.processing ? 'Creating...' : 'Create Calendar'}
                                    </Button>
                                </div>
                            </div>
                        </form>
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
                                                <div class="font-medium text-gray-900">{calendar.user.name}</div>
                                                <div class="text-sm text-gray-500">{calendar.user.email}</div>
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
