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
    const isAdmin = $derived(user?.is_admin ?? false);
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
