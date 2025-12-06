<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification prompt page.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            $user = $request->user();
            $intended = $request->session()->pull('url.intended');

            if ($intended) {
                return redirect($intended);
            }

            return redirect()->route($user->is_admin ? 'dashboard' : 'intro');
        }

        return Inertia::render('auth/VerifyEmail', ['status' => $request->session()->get('status')]);
    }
}
