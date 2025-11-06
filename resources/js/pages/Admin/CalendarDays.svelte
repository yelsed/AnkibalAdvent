<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { Form } from '@inertiajs/svelte';

    interface CalendarDay {
        id: number;
        day_number: number;
        gift_type: 'text' | 'image_text' | 'product';
        title: string | null;
        content_text: string | null;
        content_image_path: string | null;
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

    interface Props {
        calendar: Calendar;
    }

    let { calendar }: Props = $props();

    let selectedDayIndex = $state(0);
    let selectedDay = $derived(calendar.days[selectedDayIndex]);
</script>

<AppLayout
    breadcrumbs={[
        { title: 'Home', href: '/' },
        { title: 'Calendars', href: '/calendars' },
        { title: calendar.title, href: `/calendars/${calendar.id}` },
        { title: 'Manage', href: `/admin/calendars/${calendar.id}/manage` }
    ]}
>
    <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-pink-700 sm:text-4xl">Manage Calendar Days</h1>
                <p class="mt-2 text-gray-600">{calendar.title} - {calendar.year}</p>
            </div>
            <Button
                variant="outline"
                onclick={() => router.visit(`/calendars/${calendar.id}`)}
            >
                ← Back to Calendar
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Day Selector -->
            <Card class="lg:col-span-1">
                <CardHeader>
                    <CardTitle>Select Day</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-5 lg:grid-cols-3">
                        {#each calendar.days as day, index}
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
                    <CardTitle>Edit Day {selectedDay.day_number}</CardTitle>
                </CardHeader>
                <CardContent>
                    <Form
                        action={`/calendar-days/${selectedDay.id}`}
                        method="put"
                        enctype="multipart/form-data"
                        data={{
                            gift_type: selectedDay.gift_type,
                            title: selectedDay.title || '',
                            content_text: selectedDay.content_text || ''
                        }}
                    >
                        {#snippet children({ data, errors, processing })}
                            <div class="space-y-4">
                                <!-- Gift Type -->
                                <div>
                                    <Label for="gift_type">Gift Type</Label>
                                    <select
                                        name="gift_type"
                                        bind:value={data.gift_type}
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Select gift type</option>
                                        <option value="text">Text Only</option>
                                        <option value="image_text">Image + Text</option>
                                        <option value="product">Product/Coupon</option>
                                    </select>
                                    {#if errors.gift_type}
                                        <p class="mt-1 text-sm text-red-600">{errors.gift_type}</p>
                                    {/if}
                                </div>

                                <!-- Title -->
                                <div>
                                    <Label for="title">Title (optional)</Label>
                                    <Input
                                        id="title"
                                        name="title"
                                        bind:value={data.title}
                                        placeholder="A special surprise..."
                                    />
                                    {#if errors.title}
                                        <p class="mt-1 text-sm text-red-600">{errors.title}</p>
                                    {/if}
                                </div>

                                <!-- Content Text -->
                                <div>
                                    <Label for="content_text">Content Text</Label>
                                    <Textarea
                                        id="content_text"
                                        name="content_text"
                                        bind:value={data.content_text}
                                        placeholder="Your message here..."
                                        rows={6}
                                    />
                                    {#if errors.content_text}
                                        <p class="mt-1 text-sm text-red-600">{errors.content_text}</p>
                                    {/if}
                                </div>

                                <!-- Image Upload (only for image_text type) -->
                                {#if data.gift_type === 'image_text'}
                                    <div>
                                        <Label for="content_image">Image</Label>
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
                                                <p class="mb-2 text-sm text-gray-600">Current image:</p>
                                                <img
                                                    src={`/storage/${selectedDay.content_image_path}`}
                                                    alt="Current"
                                                    class="h-32 w-auto rounded-lg object-cover"
                                                />
                                            </div>
                                        {/if}
                                    </div>
                                {/if}

                                <!-- Preview -->
                                <div class="rounded-lg border-2 border-dashed border-pink-200 bg-pink-50 p-4">
                                    <h4 class="mb-2 font-semibold text-pink-700">Preview</h4>
                                    {#if data.title}
                                        <h5 class="mb-2 text-lg font-bold text-pink-700">{data.title}</h5>
                                    {/if}
                                    {#if data.content_text}
                                        <p class="whitespace-pre-wrap text-sm text-gray-700">{data.content_text}</p>
                                    {:else}
                                        <p class="italic text-gray-400">No content yet...</p>
                                    {/if}
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end gap-2 pt-4">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        class="bg-pink-500 hover:bg-pink-600"
                                    >
                                        {processing ? 'Saving...' : 'Save Day ' + selectedDay.day_number}
                                    </Button>
                                </div>

                                <!-- Status -->
                                {#if selectedDay.unlocked_at}
                                    <div class="rounded-lg bg-green-50 p-3 text-center">
                                        <p class="text-sm text-green-700">
                                            ✓ This day was unlocked on {new Date(selectedDay.unlocked_at).toLocaleDateString()}
                                        </p>
                                    </div>
                                {/if}
                            </div>
                        {/snippet}
                    </Form>
                </CardContent>
            </Card>
        </div>

        <!-- Quick Stats -->
        <Card>
            <CardHeader>
                <CardTitle>Calendar Statistics</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-pink-600">{calendar.days.length}</p>
                        <p class="text-sm text-gray-600">Total Days</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">
                            {calendar.days.filter(d => d.unlocked_at).length}
                        </p>
                        <p class="text-sm text-gray-600">Unlocked</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-600">
                            {calendar.days.filter(d => !d.unlocked_at).length}
                        </p>
                        <p class="text-sm text-gray-600">Locked</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-pink-600">
                            {Math.round((calendar.days.filter(d => d.unlocked_at).length / calendar.days.length) * 100)}%
                        </p>
                        <p class="text-sm text-gray-600">Progress</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</AppLayout>
