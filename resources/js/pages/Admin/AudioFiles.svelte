<script lang="ts">
    import { router, Form, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Textarea } from '@/components/ui/textarea';
    import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
    import { toast } from 'svelte-sonner';
    import { Music, Trash2, Upload } from 'lucide-svelte';
    import { t, initTranslations } from '@/lib/translations';

    // Initialize translations immediately from page props
    const translations = ($page.props as any)?.translations;
    if (translations) {
        initTranslations(translations);
    }

    interface AudioFile {
        id: number;
        name: string;
        file_path: string;
        original_filename: string;
        mime_type: string | null;
        file_size: number | null;
        description: string | null;
        created_at: string;
        url: string;
    }

    interface Props {
        audioFiles: AudioFile[];
    }

    let { audioFiles }: Props = $props();

    let showUploadDialog = $state(false);
    let formKey = $state(0);
    let fileInput: HTMLInputElement;
    let selectedFile: File | null = $state(null);
    let fileName = $state('');

    function formatFileSize(bytes: number | null): string {
        if (!bytes) return 'Unknown';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function handleFileSelect(event: Event) {
        const target = event.target as HTMLInputElement;
        if (target.files && target.files[0]) {
            selectedFile = target.files[0];
            fileName = selectedFile.name.replace(/\.[^/.]+$/, ''); // Remove extension
        }
    }

    function handleDelete(audioFile: AudioFile) {
        if (confirm(t('admin.are_you_sure_delete_audio').replace(':name', audioFile.name))) {
            router.delete(`/admin/audio-files/${audioFile.id}`, {
                onSuccess: () => {
                    toast.success(t('admin.audio_file_deleted_successfully'));
                },
                onError: () => {
                    toast.error(t('admin.failed_delete_audio_file'));
                },
            });
        }
    }

    // Reset form when dialog opens
    let previousDialogState = $state(false);
    $effect(() => {
        // Only reset when dialog changes from closed to open
        if (showUploadDialog && !previousDialogState) {
            formKey++;
            selectedFile = null;
            fileName = '';
            // Use setTimeout to ensure fileInput is available
            setTimeout(() => {
                if (fileInput) {
                    fileInput.value = '';
                }
            }, 0);
        }
        previousDialogState = showUploadDialog;
    });

    const breadcrumbs = $derived([
        { title: t('common.home'), href: '/' },
        { title: t('common.admin'), href: '/admin/calendars' },
        { title: t('admin.manage_audio_files'), href: '/admin/audio-files' }
    ]);
</script>

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-7xl space-y-8 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold text-pink-700">🎵 {t('admin.manage_audio_files')}</h1>
                <p class="mt-2 text-gray-600">{t('admin.audio_library_description')}</p>
            </div>

            <Dialog bind:open={showUploadDialog}>
                <DialogHeader class="sr-only">
                    <DialogTitle>{t('admin.upload_audio_file')}</DialogTitle>
                </DialogHeader>
                <DialogTrigger>
                    <Button class="bg-pink-500 hover:bg-pink-600">
                        <Upload class="mr-2 h-5 w-5" />
                        {t('admin.upload_audio_file')}
                    </Button>
                </DialogTrigger>
                <DialogContent class="max-w-2xl">
                    {#snippet children()}
                        <DialogHeader>
                            <DialogTitle>{t('admin.upload_audio_file')}</DialogTitle>
                            <DialogDescription>
                                Upload een audiobestand om het toe te voegen aan je muziekbibliotheek. Ondersteunde formaten: MP3, WAV, OGG, M4A, AAC (max 10MB).
                            </DialogDescription>
                        </DialogHeader>
                        {#key formKey}
                            <Form
                                action="/admin/audio-files"
                                method="post"
                                enctype="multipart/form-data"
                                resetOnSuccess
                                onSuccess={() => {
                                    showUploadDialog = false;
                                    toast.success(t('admin.audio_file_uploaded_successfully'));
                                }}
                                onError={() => {
                                    toast.error(t('admin.failed_upload_audio_file'));
                                }}
                            >
                                {#snippet children({ data, errors, processing }: { data: Record<string, any>; errors: Record<string, string>; processing: boolean })}
                                    <div class="space-y-4">
                                        <!-- File Upload -->
                                        <div>
                                            <Label for="file">{t('common.audio')} {t('common.required')}</Label>
                                            <Input
                                                bind:this={fileInput}
                                                id="file"
                                                name="file"
                                                type="file"
                                                accept="audio/*"
                                                onchange={handleFileSelect}
                                                required
                                            />
                                            {#if selectedFile}
                                                <p class="mt-2 text-sm text-gray-600">
                                                    {t('common.select')}: {selectedFile.name} ({formatFileSize(selectedFile.size)})
                                                </p>
                                            {/if}
                                            {#if errors.file}
                                                <p class="mt-1 text-sm text-red-600">{errors.file}</p>
                                            {/if}
                                        </div>

                                        <!-- Name -->
                                        <div>
                                            <Label for="name">{t('common.name')} {t('common.required')}</Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                bind:value={fileName}
                                                placeholder={t('admin.audio_file_name_placeholder')}
                                                required
                                            />
                                            {#if errors.name}
                                                <p class="mt-1 text-sm text-red-600">{errors.name}</p>
                                            {/if}
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <Label for="description">{t('common.description')} ({t('common.optional')})</Label>
                                            <Textarea
                                                id="description"
                                                name="description"
                                                placeholder={t('common.description')}
                                                rows={3}
                                            />
                                            {#if errors.description}
                                                <p class="mt-1 text-sm text-red-600">{errors.description}</p>
                                            {/if}
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="flex justify-end gap-2 pt-4">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onclick={() => (showUploadDialog = false)}
                                            >
                                                {t('common.cancel')}
                                            </Button>
                                            <Button
                                                type="submit"
                                                disabled={processing || !selectedFile}
                                                class="bg-pink-500 hover:bg-pink-600"
                                            >
                                                {processing ? t('common.upload') + '...' : t('admin.upload_audio_file')}
                                            </Button>
                                        </div>
                                    </div>
                                {/snippet}
                            </Form>
                        {/key}
                    {/snippet}
                </DialogContent>
            </Dialog>
        </div>

        <!-- Audio Files Grid -->
        {#if audioFiles.length === 0}
            <Card>
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <Music class="mb-4 h-16 w-16 text-gray-400" />
                    <h3 class="mb-2 text-lg font-semibold text-gray-700">{t('admin.no_audio_files_yet')}</h3>
                    <p class="mb-4 text-sm text-gray-500">{t('admin.upload_first_audio_file')}</p>
                    <Button
                        onclick={() => (showUploadDialog = true)}
                        class="bg-pink-500 hover:bg-pink-600"
                    >
                        <Upload class="mr-2 h-4 w-4" />
                        {t('admin.upload_audio_file')}
                    </Button>
                </CardContent>
            </Card>
        {:else}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {#each audioFiles as audioFile (audioFile.id)}
                    <Card>
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <CardTitle class="text-lg">{audioFile.name}</CardTitle>
                                    {#if audioFile.description}
                                        <CardDescription class="mt-1">{audioFile.description}</CardDescription>
                                    {/if}
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onclick={() => handleDelete(audioFile)}
                                    class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">File:</span>
                                    <span class="font-medium">{audioFile.original_filename}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Size:</span>
                                    <span class="font-medium">{formatFileSize(audioFile.file_size)}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Type:</span>
                                    <span class="font-medium">{audioFile.mime_type || 'Unknown'}</span>
                                </div>
                                <div class="mt-4 pt-4 border-t">
                                    <audio controls class="w-full" src={audioFile.url}>
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                {/each}
            </div>
        {/if}
    </div>
</AppLayout>
