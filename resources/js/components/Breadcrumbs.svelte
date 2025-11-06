<script lang="ts">
    import { Breadcrumb, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator, Item } from '@/components/ui/breadcrumb';
    import { Link } from '@inertiajs/svelte';

    interface BreadcrumbItem {
        title: string;
        href?: string;
    }

    interface Props {
        breadcrumbs: BreadcrumbItem[];
    }

    let { breadcrumbs }: Props = $props();

    // Filter out any undefined or invalid items
    const validBreadcrumbs = $derived(breadcrumbs.filter(item => item && item.title));
</script>

<Breadcrumb>
    <BreadcrumbList>
        {#each validBreadcrumbs as item, index (index)}
            <Item>
                {#if index === validBreadcrumbs.length - 1}
                    <BreadcrumbPage>{item.title}</BreadcrumbPage>
                {:else}
                    <BreadcrumbLink>
                        <Link href={item.href ?? '#'}>{item.title}</Link>
                    </BreadcrumbLink>
                {/if}
            </Item>
            {#if index !== validBreadcrumbs.length - 1}
                <BreadcrumbSeparator />
            {/if}
        {/each}
    </BreadcrumbList>
</Breadcrumb>
