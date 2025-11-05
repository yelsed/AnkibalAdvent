<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarDayController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Calendar routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('calendars', CalendarController::class)->only([
        'index', 'store', 'show', 'destroy',
    ]);

    Route::post('calendar-days/{calendarDay}/unlock', [CalendarDayController::class, 'unlock'])
        ->name('calendar-days.unlock');

    // Admin routes
    Route::middleware('can:admin')->group(function () {
        Route::get('admin/calendars/{calendar}/manage', [AdminController::class, 'manageCalendar'])
            ->name('admin.calendars.manage');

        Route::put('calendar-days/{calendarDay}', [CalendarDayController::class, 'update'])
            ->name('calendar-days.update');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
