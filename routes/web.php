<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarDayController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\IntroPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // Redirect authenticated users to their appropriate page
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->is_admin) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('intro');
    }

    // Show login page for unauthenticated users
    return app(\App\Http\Controllers\Auth\AuthenticatedSessionController::class)->create(request());
})->name('home');

Route::middleware(['auth'])->get('/intro', [IntroPageController::class, 'show'])
    ->name('intro');

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

    Route::get('calendars/{calendar}/export-pdf', [CalendarController::class, 'exportPdf'])
        ->name('calendars.export-pdf');

    // Invite recipient to calendar (owners only)
    Route::post('calendars/{calendar}/invite-recipient', [InvitationController::class, 'inviteRecipient'])
        ->name('calendars.invite-recipient');

    // Admin routes
    Route::middleware('can:admin')->group(function () {
        Route::get('admin/calendars', [AdminController::class, 'index'])
            ->name('admin.calendars.index');

        Route::get('admin/intro', [IntroPageController::class, 'edit'])
            ->name('admin.intro.edit');

        Route::put('admin/intro', [IntroPageController::class, 'update'])
            ->name('admin.intro.update');

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
