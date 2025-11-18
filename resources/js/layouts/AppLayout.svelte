<script lang="ts">
    import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.svelte';
    import SimpleLayout from '@/layouts/app/SimpleLayout.svelte';
    import type { BreadcrumbItemType } from '@/types';
    import type { Snippet } from 'svelte';
    import { page } from '@inertiajs/svelte';

    interface Props {
        breadcrumbs?: BreadcrumbItemType[];
        children?: Snippet;
    }

    let { breadcrumbs = [], children }: Props = $props();

    const user = $derived($page.props.auth.user);
    // Explicitly check if user is admin - only show sidebar for admins
    const isAdmin = $derived(user?.is_admin === true);
</script>

{#if isAdmin}
    <AppSidebarLayout {breadcrumbs}>
        {@render children?.()}
    </AppSidebarLayout>
{:else}
    <SimpleLayout>
        {@render children?.()}
    </SimpleLayout>
{/if}
