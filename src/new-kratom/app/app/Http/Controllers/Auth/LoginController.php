<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        $user = \App\Models\User::where('email', $data['email'])->first();

        if (! $user || ! $user->hasPassword() || ! Auth::guard('web')->getProvider()->validateCredentials($user, $data)) {
            throw ValidationException::withMessages([
                'email' => __('site.auth.errors.invalid_credentials'),
            ]);
        }

        if ($user->isBlocked()) {
            throw ValidationException::withMessages([
                'email' => __('site.auth.errors.blocked'),
            ]);
        }

        Auth::guard('web')->login($user, (bool) ($data['remember'] ?? false));
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'verified' => $user->hasVerifiedEmail(),
            'redirect' => Locale::url('/ucet'),
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(Locale::url('/'));
    }
}
