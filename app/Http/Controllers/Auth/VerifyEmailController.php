<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            $intended = $request->session()->pull('url.intended');

            if ($intended) {
                return redirect($intended.'?verified=1');
            }

            return redirect()->route($user->is_admin ? 'dashboard' : 'intro', ['verified' => 1]);
        }

        $request->fulfill();

        $intended = $request->session()->pull('url.intended');

        if ($intended) {
            return redirect($intended.'?verified=1');
        }

        return redirect()->route($user->is_admin ? 'dashboard' : 'calendars.index', ['verified' => 1]);
    }
}
