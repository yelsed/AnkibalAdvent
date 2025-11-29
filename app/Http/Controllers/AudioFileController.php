<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAudioFileRequest;
use App\Models\AudioFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AudioFileController extends Controller
{
    /**
     * Display a listing of audio files.
     */
    public function index(): Response
    {
        Gate::authorize('admin');

        $audioFiles = AudioFile::latest()->get()->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->name,
                'file_path' => $file->file_path,
                'original_filename' => $file->original_filename,
                'mime_type' => $file->mime_type,
                'file_size' => $file->file_size,
                'description' => $file->description,
                'created_at' => $file->created_at,
                'url' => $file->url,
            ];
        });

        return Inertia::render('Admin/AudioFiles', [
            'audioFiles' => $audioFiles,
        ]);
    }

    /**
     * Store a newly uploaded audio file.
     */
    public function store(StoreAudioFileRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        // Store the file
        $path = $file->store('audio_files', 'public');

        // Create the audio file record
        $audioFile = AudioFile::create([
            'name' => $request->name,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.audio-files.index')
            ->with('success', 'Audio file uploaded successfully!');
    }

    /**
     * Remove the specified audio file.
     */
    public function destroy(AudioFile $audioFile): RedirectResponse
    {
        Gate::authorize('admin');

        // Check if file is being used
        if ($audioFile->calendarDays()->count() > 0) {
            return redirect()->route('admin.audio-files.index')
                ->with('error', 'Cannot delete audio file that is being used by calendar days.');
        }

        // Delete the physical file
        Storage::disk('public')->delete($audioFile->file_path);

        // Delete the record
        $audioFile->delete();

        return redirect()->route('admin.audio-files.index')
            ->with('success', 'Audio file deleted successfully!');
    }
}
