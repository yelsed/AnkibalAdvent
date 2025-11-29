<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarDayController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // Redirect authenticated users to their appropriate page
    if (auth()->check()) {
        if (auth()->user()->is_admin) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('calendars.index');
    }

    // Show login page for unauthenticated users
    return app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)->create(request());
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'can:admin'])->name('dashboard');

// Invitation routes (public)
Route::get('invitations/{token}/accept', [InvitationController::class, 'accept'])
    ->name('invitations.accept');
Route::post('invitations/accept', [InvitationController::class, 'store'])
    ->name('invitations.store');

// Calendar routes
Route::middleware(['auth'])->group(function () {
    Route::resource('calendars', CalendarController::class)->only([
        'index', 'store', 'show', 'destroy',
    ]);

    Route::post('calendar-days/{calendarDay}/unlock', [CalendarDayController::class, 'unlock'])
        ->name('calendar-days.unlock');

    // Admin routes
    Route::middleware('can:admin')->group(function () {
        Route::get('admin/calendars', [AdminController::class, 'index'])
            ->name('admin.calendars.index');

        Route::post('admin/calendars', [AdminController::class, 'store'])
            ->name('admin.calendars.store');

        Route::get('admin/calendars/{calendar}/manage', [AdminController::class, 'manageCalendar'])
            ->name('admin.calendars.manage');

        Route::put('calendar-days/{calendarDay}', [CalendarDayController::class, 'update'])
            ->name('calendar-days.update');

        Route::get('admin/audio-files', [\App\Http\Controllers\AudioFileController::class, 'index'])
            ->name('admin.audio-files.index');

        Route::post('admin/audio-files', [\App\Http\Controllers\AudioFileController::class, 'store'])
            ->name('admin.audio-files.store');

        Route::delete('admin/audio-files/{audioFile}', [\App\Http\Controllers\AudioFileController::class, 'destroy'])
            ->name('admin.audio-files.destroy');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
