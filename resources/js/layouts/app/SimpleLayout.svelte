<script lang="ts">
    import { Link, page, router } from '@inertiajs/svelte';
    import { Button } from '@/components/ui/button';
    import { LogOut } from 'lucide-svelte';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';

    interface Props {
        children?: Snippet;
    }

    let { children }: Props = $props();

    const user = $derived($page.props.auth.user);
    const isAdmin = $derived(user?.is_admin ?? false);

    const handleLogout = () => {
        router.flushAll();
    };
</script>

<div class="min-h-screen w-full flex flex-col">
    <!-- Minimal header with logout -->
    <header class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-gray-200 px-6">
        <Link href={isAdmin ? route('dashboard') : route('calendars.index')} class="flex items-center">
            <AppLogo />
        </Link>
        <div class="flex items-center gap-4">
            {#if user}
                <span class="text-sm text-gray-600">{user.name}</span>
                <Button
                    variant="ghost"
                    size="sm"
                    onclick={handleLogout}
                    asChild
                >
                    <Link method="post" href={route('logout')} class="flex items-center gap-2">
                        <LogOut class="h-4 w-4" />
                        <span>Log out</span>
                    </Link>
                </Button>
            {/if}
        </div>
    </header>

    <!-- Main content -->
    <main class="flex-1">
        {@render children?.()}
    </main>
</div>
