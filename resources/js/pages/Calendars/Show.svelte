<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import axios from 'axios';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import DayCard from '@/components/calendar/DayCard.svelte';
    import DayModal from '@/components/calendar/DayModal.svelte';
    import AudioPlayer from '@/components/calendar/AudioPlayer.svelte';
    import { Button } from '@/components/ui/button';
    import { toast } from 'svelte-sonner';

    interface CalendarDay {
        id: number;
        day_number: number;
        gift_type: 'text' | 'image_text' | 'product';
        title: string | null;
        content_text: string | null;
        content_image_path: string | null;
        product_code: string | null;
        audio_url: string | null;
        unlocked_at: string | null;
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
        days: CalendarDay[];
        user?: User;
    }

    interface Props {
        calendar: Calendar;
        canManage: boolean;
        isAdmin: boolean;
        isOwner: boolean;
    }

    let { calendar: initialCalendar, canManage, isAdmin, isOwner }: Props = $props();

    // Convert calendar prop to state so we can mutate it reactively
    let calendar = $state(initialCalendar);

    let selectedDay = $state<CalendarDay | null>(null);
    let modalOpen = $state(false);
    let justUnlocked = $state(false);

    // Debug mode toggle (stored in localStorage)
    const DEBUG_KEY = 'calendar_debug_mode';
    let debugMode = $state(
        typeof localStorage !== 'undefined' ? localStorage.getItem(DEBUG_KEY) === 'true' : false
    );

    function toggleDebugMode() {
        debugMode = !debugMode;
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem(DEBUG_KEY, debugMode.toString());
        }
    }

    const currentDay = new Date().getDate();
    const currentMonth = new Date().getMonth() + 1;
    const isDecember = currentMonth === 12;

    function canUnlockDay(day: CalendarDay): boolean {
        if (day.unlocked_at) {
            return false;
        }

        // Admins can always unlock (when debug mode is on, or in admin view)
        if (isAdmin && debugMode) {
            return true;
        }

        // Debug mode: allow all days to be unlocked
        if (debugMode) {
            return true;
        }

        // Can only unlock in December
        if (!isDecember) {
            return false;
        }

        // Can only unlock if day_number <= current day
        return day.day_number <= currentDay;
    }

    async function handleDayClick(day: CalendarDay) {
        if (day.unlocked_at) {
            // Already unlocked, just show the content
            selectedDay = day;
            justUnlocked = false;
            modalOpen = true;
            return;
        }

        // Frontend check - backend will also validate
        if (!canUnlockDay(day)) {
            toast.error('This day cannot be unlocked yet!', {
                description: isDecember
                    ? `Come back on December ${day.day_number}`
                    : 'Advent calendars can only be opened in December',
            });
            return;
        }

        // Unlock the day
        try {
            const response = await axios.post(`/calendar-days/${day.id}/unlock`);

            // Update the day with the unlocked data - replace entire array to trigger reactivity
            const dayIndex = calendar.days.findIndex(d => d.id === day.id);
            if (dayIndex !== -1) {
                calendar.days = [
                    ...calendar.days.slice(0, dayIndex),
                    response.data.day,
                    ...calendar.days.slice(dayIndex + 1),
                ];
                selectedDay = response.data.day;
                justUnlocked = true;
                modalOpen = true;
            }

            toast.success('Day unlocked! 🎉', {
                description: response.data.message,
            });
        } catch (error: any) {
            toast.error('Failed to unlock day', {
                description: error.response?.data?.message || 'An error occurred',
            });
        }
    }

    function goToAdmin() {
        router.visit(`/admin/calendars/${calendar.id}/manage`);
    }
</script>

<AppLayout
    breadcrumbs={[
        { title: 'Home', href: '/' },
        { title: 'Calendars', href: '/calendars' },
        { title: calendar.title, href: `/calendars/${calendar.id}` }
    ]}
>
    <div class="mx-auto max-w-7xl space-y-8 p-4 sm:p-6">
        <!-- Admin Banner -->
        {#if isAdmin && !isOwner}
            <div class="rounded-lg bg-blue-50 border-2 border-blue-300 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-900">Admin View</p>
                        <p class="text-sm text-blue-700">
                            {#if calendar.user}
                                Viewing calendar owned by <strong>{calendar.user.name}</strong> ({calendar.user.email})
                            {:else}
                                Viewing calendar
                            {/if}
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        onclick={() => router.visit('/admin/calendars')}
                        class="border-blue-300 text-blue-700 hover:bg-blue-100"
                    >
                        Back to Admin
                    </Button>
                </div>
            </div>
        {/if}

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold text-pink-700 sm:text-4xl">{calendar.title}</h1>
                    {#if isAdmin}
                        <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                            ADMIN
                        </span>
                    {/if}
                </div>
                {#if calendar.description}
                    <p class="mt-2 text-gray-600">{calendar.description}</p>
                {/if}
                <div class="mt-1 flex flex-wrap gap-4 text-sm text-gray-500">
                    <span>Year: {calendar.year}</span>
                    {#if isAdmin && calendar.user}
                        <span>• Owner: {calendar.user.name}</span>
                    {/if}
                </div>
            </div>

            <div class="flex gap-2">
                <!-- Debug Mode Toggle -->
                <Button
                    onclick={toggleDebugMode}
                    variant={debugMode ? 'default' : 'outline'}
                    class={debugMode
                        ? 'bg-yellow-500 text-white hover:bg-yellow-600'
                        : 'border-yellow-300 text-yellow-600 hover:bg-yellow-50'}
                    title="Toggle debug mode to unlock all days"
                >
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                        />
                    </svg>
                    Debug {debugMode ? 'ON' : 'OFF'}
                </Button>

                {#if canManage}
                    <Button onclick={goToAdmin} variant="outline" class="border-pink-200 text-pink-600 hover:bg-pink-50">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Manage Days
                    </Button>
                {/if}
            </div>
        </div>

        <!-- Info Banner -->
        {#if debugMode}
            <div class="rounded-lg bg-yellow-100 border-2 border-yellow-400 p-4 text-center">
                <p class="text-yellow-800 font-semibold">
                    🐛 Debug Mode: All days can be unlocked regardless of date!
                </p>
            </div>
        {:else if !isDecember}
            <div class="rounded-lg bg-pink-50 p-4 text-center">
                <p class="text-pink-700">
                    <span class="font-semibold">Note:</span> Days can only be unlocked in December!
                </p>
            </div>
        {:else}
            <div class="rounded-lg bg-pink-50 p-4 text-center">
                <p class="text-pink-700">
                    You can unlock days 1-{currentDay} now! 🎅
                </p>
            </div>
        {/if}

        <!-- Calendar Audio -->
        {#if calendar.audio_url}
            <div class="rounded-lg border border-pink-200 bg-pink-50 p-4">
                <h3 class="mb-3 text-lg font-semibold text-pink-700">Calendar Music</h3>
                <AudioPlayer audioUrl={calendar.audio_url} loop={true} />
            </div>
        {/if}

        <!-- Calendar Grid -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-6">
            {#each calendar.days as day (day.id)}
                <DayCard
                    {day}
                    canUnlock={canUnlockDay(day)}
                    onclick={() => handleDayClick(day)}
                />
            {/each}
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 rounded-lg bg-gradient-to-r from-pink-50 to-pink-100 p-6 sm:grid-cols-3">
            <div class="text-center">
                <p class="text-3xl font-bold text-pink-600">
                    {calendar.days.filter(d => d.unlocked_at).length}
                </p>
                <p class="text-sm text-gray-600">Days Unlocked</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-pink-600">
                    {calendar.days.length - calendar.days.filter(d => d.unlocked_at).length}
                </p>
                <p class="text-sm text-gray-600">Days Remaining</p>
            </div>
            <div class="col-span-2 text-center sm:col-span-1">
                <p class="text-3xl font-bold text-pink-600">
                    {Math.round((calendar.days.filter(d => d.unlocked_at).length / calendar.days.length) * 100)}%
                </p>
                <p class="text-sm text-gray-600">Complete</p>
            </div>
        </div>

        <!-- Admin Stats -->
        {#if isAdmin}
            <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-6">
                <h3 class="mb-4 text-lg font-semibold text-blue-900">Admin Statistics</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700">
                            {calendar.days.filter(d => d.content_text && d.content_text !== 'This gift hasn\'t been set up yet.').length}
                        </p>
                        <p class="text-xs text-blue-600">Days with Content</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700">
                            {calendar.days.filter(d => d.gift_type === 'image_text').length}
                        </p>
                        <p class="text-xs text-blue-600">Image Days</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700">
                            {calendar.days.filter(d => d.gift_type === 'product').length}
                        </p>
                        <p class="text-xs text-blue-600">Product Days</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700">
                            {calendar.days.filter(d => !d.content_text || d.content_text === 'This gift hasn\'t been set up yet.').length}
                        </p>
                        <p class="text-xs text-blue-600">Days to Setup</p>
                    </div>
                </div>
            </div>
        {/if}
    </div>

    <!-- Day Modal -->
    <DayModal day={selectedDay} bind:open={modalOpen} bind:justUnlocked />
</AppLayout>
