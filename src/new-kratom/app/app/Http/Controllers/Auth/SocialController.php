<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class SocialController extends Controller
{
    /** @var array<int, string> */
    private const PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider): SymfonyRedirect|RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $oauthUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect(Locale::url('/'))->with('auth_error', __('site.auth.errors.social_failed'));
        }

        $providerId = (string) $oauthUser->getId();
        $email = $oauthUser->getEmail();

        $social = SocialAccount::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($social) {
            $user = $social->user;
        } else {
            // Link to an existing account by email, or create a fresh one.
            $user = $email ? User::where('email', $email)->first() : null;

            if (! $user) {
                $user = User::create([
                    'name' => $oauthUser->getName() ?: ($oauthUser->getNickname() ?: 'Uživatel'),
                    'email' => $email ?: $provider.'_'.$providerId.'@social.local',
                    'password' => null,
                    'avatar_path' => $oauthUser->getAvatar(),
                    'email_verified_at' => $email ? now() : null,
                    'locale' => app()->getLocale(),
                ]);
            } elseif (! $user->email_verified_at && $email) {
                // Provider already verified this email for us.
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $providerId,
                'avatar' => $oauthUser->getAvatar(),
            ]);
        }

        if ($user->isBlocked()) {
            return redirect(Locale::url('/'))->with('auth_error', __('site.auth.errors.blocked'));
        }

        Auth::guard('web')->login($user, true);
        session()->regenerate();

        return redirect(Locale::url('/ucet'));
    }
}
