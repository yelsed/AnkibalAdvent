<script lang="ts">
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
    import { type NavItem } from '@/types';
    import { Link, page } from '@inertiajs/svelte';
    import { BookOpen, Calendar, Folder, LayoutGrid, Settings } from 'lucide-svelte';
    import AppLogo from './AppLogo.svelte';
    import { t, initTranslations } from '@/lib/translations';

    $effect(() => {
        const translations = ($page.props as any)?.translations;
        if (translations) {
            initTranslations(translations);
        }
    });

    const user = $derived($page.props.auth.user);
    const isAdmin = $derived(user?.is_admin ?? false);

    const mainNavItems: NavItem[] = $derived([
        ...(isAdmin ? [{
            title: t('common.dashboard'),
            href: '/dashboard',
            icon: LayoutGrid,
        }] : []),
        {
            title: t('common.advent_calendars'),
            href: '/calendars',
            icon: Calendar,
        },
    ]);

    const adminNavItems: NavItem[] = $derived([
        {
            title: t('admin.calendar_management'),
            href: '/admin/calendars',
            icon: Settings,
        },
        {
            title: t('admin.intro_page'),
            href: '/admin/intro',
            icon: LayoutGrid,
        },
    ]);

    const footerNavItems: NavItem[] = $derived([
        {
            title: t('common.repository'),
            href: 'https://github.com/oseughu/svelte-starter-kit',
            icon: Folder,
        },
        {
            title: t('common.documentation'),
            href: 'https://laravel.com/docs/starter-kits',
            icon: BookOpen,
        },
    ]);
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg">
                    <Link href={isAdmin ? route('dashboard') : route('calendars.index')}>
                        <AppLogo />
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
        {#if isAdmin}
            <NavMain items={adminNavItems} />
        {/if}
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} class="mt-auto" />
        <NavUser />
    </SidebarFooter>
</Sidebar>
