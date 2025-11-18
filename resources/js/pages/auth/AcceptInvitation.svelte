<script lang="ts">
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import AuthBase from '@/layouts/AuthLayout.svelte';
    import { Form } from '@inertiajs/svelte';
    import { LoaderCircle } from 'lucide-svelte';

    interface Calendar {
        id: number;
        title: string;
    }

    interface Invitation {
        token: string;
        email: string;
        calendar: Calendar | null;
    }

    interface Props {
        invitation: Invitation;
    }

    let { invitation }: Props = $props();
</script>

<svelte:head>
    <title>Account aanmaken</title>
</svelte:head>

<AuthBase title="Account aanmaken" description="Stel je wachtwoord in om je advent kalender te bekijken">
    <div class="mb-6 rounded-lg bg-pink-50 border border-pink-200 p-4">
        <p class="text-sm text-pink-800">
            <strong>E-mail:</strong> {invitation.email}
        </p>
        {#if invitation.calendar}
            <p class="text-sm text-pink-800 mt-2">
                <strong>Kalender:</strong> {invitation.calendar.title}
            </p>
        {/if}
    </div>

    <Form method="post" action={route('invitations.store')} resetOnSuccess={['password', 'password_confirmation']} class="flex flex-col gap-6">
        {#snippet children({ errors, processing }: { errors: Record<string, string>; processing: boolean })}
            <input type="hidden" name="token" value={invitation.token} />

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Naam</Label>
                    <Input id="name" name="name" type="text" required autofocus tabindex={1} autocomplete="name" placeholder="Volledige naam" />
                    <InputError message={errors.name} />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Wachtwoord</Label>
                    <Input id="password" name="password" type="password" required tabindex={2} autocomplete="new-password" placeholder="Wachtwoord" />
                    <InputError message={errors.password} />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Bevestig wachtwoord</Label>
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        tabindex={3}
                        autocomplete="new-password"
                        placeholder="Bevestig wachtwoord"
                    />
                    <InputError message={errors.password_confirmation} />
                </div>

                <Button type="submit" class="mt-2 w-full bg-pink-500 hover:bg-pink-600" tabindex={4} disabled={processing}>
                    {#if processing}
                        <LoaderCircle class="h-4 w-4 animate-spin" />
                    {/if}
                    Account aanmaken
                </Button>
            </div>
        {/snippet}
    </Form>
</AuthBase>




