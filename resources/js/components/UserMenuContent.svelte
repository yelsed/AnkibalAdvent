<script lang="ts">
    import UserInfo from '@/components/UserInfo.svelte';
    import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
    import type { User } from '@/types';
    import { Link, router, page } from '@inertiajs/svelte';
    import { LogOut, Settings } from 'lucide-svelte';
    import { t, initTranslations } from '@/lib/translations';

    interface Props {
        user: User;
    }

    let { user }: Props = $props();

    $effect(() => {
        const translations = ($page.props as any)?.translations;
        if (translations) {
            initTranslations(translations);
        }
    });

    const handleLogout = () => {
        router.flushAll();
    };
</script>

<DropdownMenuLabel class="p-0 font-normal">
    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
        <UserInfo {user} showEmail={true} />
    </div>
</DropdownMenuLabel>
<DropdownMenuSeparator />
<DropdownMenuGroup>
    <DropdownMenuItem>
        <Link class="block w-full" href={route('profile.edit')} prefetch as="button">
            <div class="flex items-center">
                <Settings class="mr-2 h-4 w-4" />
                <span>{t('common.settings')}</span>
            </div>
        </Link>
    </DropdownMenuItem>
</DropdownMenuGroup>
<DropdownMenuSeparator />
<DropdownMenuItem>
    <Link class="block w-full" method="post" onclick={handleLogout} href={route('logout')} as="button">
        <div class="flex items-center">
            <LogOut class="mr-2 h-4 w-4" />
            <span>{t('common.logout')}</span>
        </div>
    </Link>
</DropdownMenuItem>
