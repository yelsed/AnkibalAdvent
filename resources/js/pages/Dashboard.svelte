<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { type BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
    ];
</script>

<svelte:head>
    <title>Dashboard</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="space-y-6 p-6">
        <!-- Welcome Hero -->
        <div class="rounded-lg bg-gradient-to-r from-pink-500 to-pink-600 p-8 text-white">
            <h1 class="mb-2 text-3xl font-bold">Welcome to AnkiBal Advent! 🎄</h1>
            <p class="mb-4 text-pink-100">Create magical advent calendar experiences with 31 days of surprises</p>
            <Button
                onclick={() => router.visit('/calendars')}
                class="bg-white text-pink-600 hover:bg-pink-50"
            >
                View My Calendars
            </Button>
        </div>

        <!-- Quick Actions -->
        <div class="grid gap-4 md:grid-cols-3">
            <Card class="cursor-pointer transition-all hover:scale-105 hover:shadow-lg" onclick={() => router.visit('/calendars')}>
                <CardHeader>
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-lg bg-pink-100 text-pink-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <CardTitle>My Calendars</CardTitle>
                    <CardDescription>View and manage your advent calendars</CardDescription>
                </CardHeader>
            </Card>

            <Card class="border-dashed border-pink-200 bg-pink-50/50">
                <CardHeader>
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-lg bg-pink-100 text-pink-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <CardTitle class="text-pink-700">31 Days</CardTitle>
                    <CardDescription>Each calendar has 31 days of surprises</CardDescription>
                </CardHeader>
            </Card>

            <Card class="border-dashed border-pink-200 bg-pink-50/50">
                <CardHeader>
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-lg bg-pink-100 text-pink-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <CardTitle class="text-pink-700">Unlock in December</CardTitle>
                    <CardDescription>Days unlock day by day throughout December</CardDescription>
                </CardHeader>
            </Card>
        </div>

        <!-- Features -->
        <Card>
            <CardHeader>
                <CardTitle>Features</CardTitle>
                <CardDescription>What makes our advent calendars special</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            ✨
                        </div>
                        <div>
                            <h4 class="font-semibold">Confetti Celebrations</h4>
                            <p class="text-sm text-muted-foreground">Beautiful animations when unlocking each day</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            🎁
                        </div>
                        <div>
                            <h4 class="font-semibold">Gift Wrap Design</h4>
                            <p class="text-sm text-muted-foreground">Locked days show festive gift wrap patterns</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            📱
                        </div>
                        <div>
                            <h4 class="font-semibold">Mobile Friendly</h4>
                            <p class="text-sm text-muted-foreground">Perfectly responsive design for all devices</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-600">
                            🎨
                        </div>
                        <div>
                            <h4 class="font-semibold">Three Gift Types</h4>
                            <p class="text-sm text-muted-foreground">Text, images with text, or redeemable products</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</AppLayout>
