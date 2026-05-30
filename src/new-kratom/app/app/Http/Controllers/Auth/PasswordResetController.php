<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // Always answer ok to avoid leaking which e-mails are registered.
        Password::broker()->sendResetLink($request->only('email'));

        return response()->json(['ok' => true, 'message' => __('site.auth.forgot.sent')]);
    }

    public function showResetForm(Request $request, string $token): \Illuminate\View\View
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::broker()->reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            Auth::guard('web')->login($user);
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return redirect(Locale::url('/ucet'))->with('status', __('site.auth.reset.success'));
    }
}
