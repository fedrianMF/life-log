<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleController extends Controller
{
    /**
     * Send the user to Google's consent screen.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Log in the Google user when their email is on the allowlist.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('login');
        }

        $googleUser = Socialite::driver('google')->user();
        $email = strtolower($googleUser->getEmail());

        abort_unless(in_array($email, config('services.google.allowed_emails'), true), 403);

        $user = User::updateOrCreate(
            [
                'email' => $email
            ],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId()
            ],
        );

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
