<script lang="ts">
    import { router, page, Form } from '@inertiajs/svelte';
    import axios from 'axios';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import DayCard from '@/components/calendar/DayCard.svelte';
    import DayModal from '@/components/calendar/DayModal.svelte';
    import AudioPlayer from '@/components/calendar/AudioPlayer.svelte';
    import { Button } from '@/components/ui/button';
    import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { toast } from 'svelte-sonner';
    import { getCountdownString, getNextDayForCountdown } from '@/lib/countdown';
    import { getThemeColors } from '@/lib/colors';
    import { getThemeForDay } from '@/lib/themes';
    import { t, initTranslations } from '@/lib/translations';

    // Initialize translations immediately from page props
    const translations = ($page.props as any)?.translations;
    if (translations) {
        initTranslations(translations);
    }

    function formatCountdownWithLabels(countdown: string, dayNumber: number): string {
        const parts = countdown.split(':');

        if (parts.length === 4) {
            // Format: DD:HH:MM:SS -> "DD dagen HH uren MM min SS sec"
            const [days, hours, minutes, seconds] = parts;
            const daysNum = parseInt(days, 10);
            if (daysNum > 0) {
                return `${days} ${t('common.days')} ${hours} ${t('common.hours')} ${minutes} ${t('common.minutes')} ${seconds} ${t('common.seconds')}`;
            } else {
                // If days is 00, format as HH uren MM min SS sec
                return `${hours} ${t('common.hours')} ${minutes} ${t('common.minutes')} ${seconds} ${t('common.seconds')}`;
            }
        } else if (parts.length === 3) {
            // Format: HH:MM:SS -> "HH uren MM min SS sec"
            const [hours, minutes, seconds] = parts;
            return `${hours} ${t('common.hours')} ${minutes} ${t('common.minutes')} ${seconds} ${t('common.seconds')}`;
        }

        return countdown;
    }

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
        theme_override?: any;
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
        theme_type?: 'single' | 'dual' | 'seasonal';
        secondary_color?: string | null;
        seasonal_config?: any;
        days: CalendarDay[];
        owner?: User;
        recipient?: User;
        user?: User; // backward compatibility
    }

    interface Props {
        calendar: Calendar;
        canManage: boolean;
        isAdmin: boolean;
        isOwner: boolean;
        isRecipient?: boolean;
        invitationAcceptUrl?: string | null;
    }

    let {
        calendar: initialCalendar,
        canManage,
        isAdmin,
        isOwner,
        isRecipient = false,
        invitationAcceptUrl = null,
    }: Props = $props();

    // Convert calendar prop to state so we can mutate it reactively
    let calendar = $state(initialCalendar);

    // Set defaults for theme_type if not present (backward compatibility)
    if (!calendar.theme_type) {
        calendar.theme_type = 'single';
    }

    const themeColors = $derived(
        getThemeColors(
            calendar.theme_color,
            calendar.theme_type === 'dual' ? calendar.secondary_color : null
        )
    );

    let selectedDay = $state<CalendarDay | null>(null);
    let modalOpen = $state(false);
    let justUnlocked = $state(false);
    let currentTime = $state(new Date());
    let inviteDialogOpen = $state(false);
    let invitationLinkCopied = $state(false);

    // Check if debug mode is enabled from server config
    const calendarDebugEnabled = $derived(($page.props as any)?.calendarDebugEnabled ?? false);

    // Debug mode toggle (stored in localStorage)
    const DEBUG_KEY = 'calendar_debug_mode';
    let debugMode = $state(false);

    // Initialize debug mode from localStorage
    $effect(() => {
        if (calendarDebugEnabled && typeof localStorage !== 'undefined') {
            debugMode = localStorage.getItem(DEBUG_KEY) === 'true';
        }
    });

    function toggleDebugMode() {
        debugMode = !debugMode;
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem(DEBUG_KEY, debugMode.toString());
        }
    }

    // Update current time every second for countdown
    $effect(() => {
        const interval = setInterval(() => {
            currentTime = new Date();
        }, 1000);

        return () => clearInterval(interval);
    });

    const currentDay = $derived(currentTime.getDate());
    const currentMonth = $derived(currentTime.getMonth() + 1);
    const currentHour = $derived(currentTime.getHours());
    const isDecember = $derived(currentMonth === 12);

    // Determine which day should show countdown
    const nextDayForCountdown = $derived(getNextDayForCountdown(calendar.days));

    // Calculate countdown string for the next day
    // Explicitly depend on currentTime so it updates every second
    const countdownMap = $derived.by(() => {
        // Access currentTime to create dependency
        currentTime;

        const map = new Map<number, string | null>();

        if (nextDayForCountdown === null || debugMode) {
            return map;
        }

        const nextDay = calendar.days.find(d => d.day_number === nextDayForCountdown);
        if (nextDay) {
            const countdown = getCountdownString(
                nextDay.day_number,
                nextDay.unlocked_at !== null,
                calendar.year,
                currentTime
            );
            // Only show countdown if day cannot be unlocked yet
            if (countdown !== null && !canUnlockDay(nextDay)) {
                const formattedCountdown = formatCountdownWithLabels(countdown, nextDay.day_number);
                map.set(nextDay.day_number, formattedCountdown);
            }
        }

        return map;
    });

    async function copyInvitationLink() {
        if (!invitationAcceptUrl) {
            return;
        }

        try {
            if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(invitationAcceptUrl);
                invitationLinkCopied = true;

                setTimeout(() => {
                    invitationLinkCopied = false;
                }, 2000);
            }
        } catch (error) {
            console.error('Failed to copy invitation link', error);
        }
    }

    function canUnlockDay(day: CalendarDay): boolean {
        if (day.unlocked_at) {
            return false;
        }

        // Admins can always unlock (when debug mode is on, or in admin view)
        if (isAdmin && debugMode) {
            return true;
        }

        // Debug mode: allow all days to be unlocked (only if enabled in config)
        if (calendarDebugEnabled && debugMode) {
            return true;
        }

        // Can only unlock in December
        if (!isDecember) {
            return false;
        }

        // Can only unlock if day_number <= current day
        if (day.day_number > currentDay) {
            return false;
        }

        // Can only unlock if it's 07:00 or later on that day
        // If it's the current day, check if it's past 07:00
        if (day.day_number === currentDay) {
            if (currentHour < 7) {
                return false;
            }
        }

        return true;
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
            toast.error(t('calendar.this_day_cannot_unlocked_yet'), {
                description: isDecember
                    ? t('calendar.come_back_december', { day: day.day_number })
                    : t('calendar.advent_calendars_only_december'),
            });
            return;
        }

        // Unlock the day
        try {
            const response = await axios.post(`/calendar-days/${day.id}/unlock`, {
                debug_mode: debugMode
            });

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

            toast.success(t('calendar.day_unlocked'), {
                description: response.data.message,
            });
        } catch (error: any) {
            toast.error(t('calendar.failed_unlock_day'), {
                description: error.response?.data?.message || t('common.error'),
            });
        }
    }

    function goToAdmin() {
        router.visit(`/admin/calendars/${calendar.id}/manage`);
    }

    const breadcrumbs = $derived([
        { title: t('common.home'), href: '/' },
        { title: t('common.calendars'), href: '/calendars' },
        { title: calendar.title, href: `/calendars/${calendar.id}` }
    ]);
</script>

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-7xl space-y-8 p-4 sm:p-6">
        <!-- Admin Banner -->
        {#if isAdmin && !isOwner}
            <div class="rounded-lg bg-blue-50 border-2 border-blue-300 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-900 font-serif">{t('calendar.admin_view')}</p>
                        <p class="text-sm text-blue-700 font-serif">
                            {#if calendar.owner || calendar.user}
                                {@const owner = calendar.owner || calendar.user}
                                {t('calendar.viewing_calendar_owned_by', { name: owner.name, email: owner.email })}
                            {:else}
                                {t('calendar.viewing_calendar')}
                            {/if}
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        onclick={() => router.visit('/admin/calendars')}
                        class="border-blue-300 text-blue-700 hover:bg-blue-100"
                    >
                        {t('calendar.back_to_admin')}
                    </Button>
                </div>
            </div>
        {/if}

        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-3xl font-bold sm:text-4xl" style="color: {themeColors.darker};">
                        {calendar.title}
                    </h1>
                    {#if isAdmin}
                        <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                            {t('common.admin')}
                        </span>
                    {/if}
                </div>
                {#if calendar.description}
                    <p class="mt-2 text-gray-600 font-serif">{calendar.description}</p>
                {/if}
                <div class="mt-1 flex flex-wrap gap-4 text-sm text-gray-500 font-serif">
                    <span>{t('common.year')}: {calendar.year}</span>
                    {#if isAdmin && (calendar.owner || calendar.user)}
                        {@const owner = calendar.owner || calendar.user}
                        <span>• {t('calendar.creator')}: {owner.name}</span>
                    {/if}
                </div>
            </div>

            <div class="flex gap-2">
                <!-- Debug Mode Toggle (only shown if enabled in config) -->
                {#if calendarDebugEnabled}
                <Button
                    onclick={toggleDebugMode}
                    variant={debugMode ? 'default' : 'outline'}
                    class={debugMode
                        ? 'bg-yellow-500 text-white hover:bg-yellow-600'
                        : 'border-yellow-300 text-yellow-600 hover:bg-yellow-50'}
                    title={t('calendar.toggle_debug_mode')}
                >
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                        />
                    </svg>
                        {t('calendar.debug_mode')} {debugMode ? t('calendar.debug_on') : t('calendar.debug_off')}
                    </Button>
                {/if}

                {#if canManage}
                    <Button
                        onclick={goToAdmin}
                        variant="outline"
                        style="border-color: {themeColors.lighter}; color: {themeColors.dark};"
                        onmouseenter={(e) => {
                            e.currentTarget.style.backgroundColor = themeColors.light;
                        }}
                        onmouseleave={(e) => {
                            e.currentTarget.style.backgroundColor = 'transparent';
                        }}
                    >
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {t('calendar.manage_days')}
                    </Button>
                {/if}
            </div>
        </div>

        <!-- Recipient Information & Invite Section -->
        {#if isOwner}
            <div
                class="rounded-lg border p-6"
                style="border-color: {themeColors.lighter}; background-color: {themeColors.light};"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold font-serif" style="color: {themeColors.darker};">
                            {t('calendar.recipient')}
                        </h3>
                        {#if calendar.recipient}
                            <p class="mt-1 text-sm text-gray-600 font-serif">
                                {t('calendar.invited_recipient', { email: calendar.recipient.email })}
                            </p>
                        {:else}
                            <p class="mt-1 text-sm text-gray-600 font-serif">
                                {t('calendar.no_recipient_yet')}
                            </p>
                        {/if}
                    </div>
                    <Dialog bind:open={inviteDialogOpen}>
                        <DialogTrigger>
                            <Button
                                style="background-color: {themeColors.dark}; color: white;"
                                class="hover:opacity-90"
                            >
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                                {t('calendar.invite_recipient')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            {#snippet children()}
                                <DialogHeader>
                                    <DialogTitle>{t('calendar.invite_recipient')}</DialogTitle>
                                    <DialogDescription>
                                        {t('calendar.invite_recipient_description')}
                                    </DialogDescription>
                                </DialogHeader>
                                <Form
                                    action={`/calendars/${calendar.id}/invite-recipient`}
                                    method="post"
                                    onSuccess={() => {
                                        inviteDialogOpen = false;
                                        router.reload();
                                    }}
                                >
                                    {#snippet children({ errors, processing }: { errors: Record<string, string>; processing: boolean })}
                                        <div class="space-y-4">
                                            <div>
                                                <Label for="email">{t('calendar.recipient_email')}</Label>
                                                <Input
                                                    id="email"
                                                    name="email"
                                                    type="email"
                                                    placeholder={t('calendar.recipient_email_placeholder')}
                                                    required
                                                />
                                                {#if errors.email}
                                                    <p class="mt-1 text-sm text-red-600">{errors.email}</p>
                                                {/if}
                                            </div>
                                            <div class="flex justify-end gap-2 pt-4">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    onclick={() => (inviteDialogOpen = false)}
                                                >
                                                    {t('common.cancel')}
                                                </Button>
                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                    style="background-color: {themeColors.dark}; color: white;"
                                                    class="hover:opacity-90"
                                                >
                                                    {processing ? t('common.submit') : t('calendar.send_invitation')}
                                                </Button>
                                            </div>
                                        </div>
                                    {/snippet}
                                </Form>
                            {/snippet}
                        </DialogContent>
                    </Dialog>
                </div>

                {#if invitationAcceptUrl}
                    <div class="mt-4 rounded-lg border border-pink-200 bg-pink-50 p-4">
                        <p class="text-sm font-semibold text-pink-800 font-serif">
                            {t('calendar.share_invitation_link')}
                        </p>
                        <p class="mt-1 text-xs text-pink-800 font-serif">
                            {t('calendar.share_invitation_link_description')}
                        </p>
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                            <code class="flex-1 break-all rounded bg-white/80 px-3 py-2 text-xs text-pink-900">
                                {invitationAcceptUrl}
                            </code>
                            <Button
                                type="button"
                                variant="outline"
                                class="mt-2 w-full border-pink-300 text-pink-700 hover:bg-pink-100 sm:mt-0 sm:w-auto"
                                onclick={copyInvitationLink}
                            >
                                {invitationLinkCopied
                                    ? t('calendar.link_copied')
                                    : t('calendar.copy_link')}
                            </Button>
                        </div>
                    </div>
                {/if}
            </div>
        {/if}

        <!-- Info Banner -->
        {#if calendarDebugEnabled && debugMode}
            <div class="rounded-lg bg-yellow-100 border-2 border-yellow-400 p-4 text-center">
                <p class="text-yellow-800 font-semibold font-serif">
                    {t('calendar.debug_mode_all_days')}
                </p>
            </div>
        {:else if !isDecember}
            <div class="rounded-lg p-4 text-center" style="background-color: {themeColors.light};">
                <p class="font-serif" style="color: {themeColors.darker};">
                    <span class="font-semibold">{t('calendar.note')}:</span> {t('calendar.days_only_unlock_december')}
                </p>
            </div>
        {:else}
            <div class="rounded-lg p-4 text-center" style="background-color: {themeColors.light};">
                <p class="font-serif" style="color: {themeColors.darker};">
                    {t('calendar.can_unlock_days', { day: currentDay })}
                </p>
            </div>
        {/if}

        <!-- Calendar Audio -->
        {#if calendar.audio_url}
            <div
                class="rounded-lg border p-4"
                style="border-color: {themeColors.lighter}; background-color: {themeColors.light};"
            >
                <h3 class="mb-3 text-lg font-semibold font-serif" style="color: {themeColors.darker};">
                    {t('calendar.calendar_music')}
                </h3>
                <AudioPlayer audioUrl={calendar.audio_url} loop={true} themeColor={calendar.theme_color} />
            </div>
        {/if}

        <!-- Calendar Grid -->
        <div class="grid grid-cols-2 gap-6 md:grid-cols-4 lg:grid-cols-6">
            {#each calendar.days as day (day.id)}
                {@const dayTheme = getThemeForDay(
                    calendar.theme_type || 'single',
                    day.day_number,
                    calendar.theme_color,
                    calendar.secondary_color || null,
                    calendar.seasonal_config,
                    day.theme_override
                )}
                <DayCard
                    {day}
                    canUnlock={canUnlockDay(day)}
                    countdown={countdownMap.get(day.day_number) || null}
                    onclick={() => handleDayClick(day)}
                    themeColor={dayTheme.primary}
                    secondaryColor={dayTheme.secondary}
                />
            {/each}
        </div>

        <!-- Stats -->
        <div
            class="grid grid-cols-2 gap-4 rounded-lg p-6 sm:grid-cols-3"
            style="background-color: {themeColors.light};"
        >
            <div class="text-center">
                <p class="text-3xl font-bold font-serif" style="color: {themeColors.dark};">
                    {calendar.days.filter(d => d.unlocked_at).length}
                </p>
                <p class="text-sm text-gray-600 font-serif">{t('calendar.days_unlocked')}</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold font-serif" style="color: {themeColors.dark};">
                    {calendar.days.length - calendar.days.filter(d => d.unlocked_at).length}
                </p>
                <p class="text-sm text-gray-600 font-serif">{t('calendar.days_remaining')}</p>
            </div>
            <div class="col-span-2 text-center sm:col-span-1">
                <p class="text-3xl font-bold font-serif" style="color: {themeColors.dark};">
                    {Math.round((calendar.days.filter(d => d.unlocked_at).length / calendar.days.length) * 100)}%
                </p>
                <p class="text-sm text-gray-600 font-serif">{t('calendar.complete')}</p>
            </div>
        </div>

        <!-- Admin Stats -->
        {#if isAdmin}
            <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-6">
                <h3 class="mb-4 text-lg font-semibold text-blue-900 font-serif">{t('calendar.admin_statistics')}</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700 font-serif">
                            {calendar.days.filter(d => d.content_text && d.content_text !== t('calendar.gift_hasnt_setup')).length}
                        </p>
                        <p class="text-xs text-blue-600 font-serif">{t('calendar.days_with_content')}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700 font-serif">
                            {calendar.days.filter(d => d.gift_type === 'image_text').length}
                        </p>
                        <p class="text-xs text-blue-600 font-serif">{t('calendar.image_days')}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700 font-serif">
                            {calendar.days.filter(d => d.gift_type === 'product').length}
                        </p>
                        <p class="text-xs text-blue-600 font-serif">{t('calendar.product_days')}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-700 font-serif">
                            {calendar.days.filter(d => !d.content_text || d.content_text === t('calendar.gift_hasnt_setup')).length}
                        </p>
                        <p class="text-xs text-blue-600 font-serif">{t('calendar.days_to_setup')}</p>
                    </div>
                </div>
            </div>
        {/if}
    </div>

    <!-- Day Modal -->
    {#if selectedDay}
        {@const dayTheme = getThemeForDay(
            calendar.theme_type || 'single',
            selectedDay.day_number,
            calendar.theme_color,
            calendar.secondary_color || null,
            calendar.seasonal_config,
            selectedDay.theme_override
        )}
        <DayModal
            day={selectedDay}
            bind:open={modalOpen}
            bind:justUnlocked
            themeColor={dayTheme.primary}
            secondaryColor={dayTheme.secondary}
        />
    {/if}
</AppLayout>
