<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * Signed link from the verification e-mail. The signature itself is the
     * security boundary, so this works even without an active session.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect(Locale::url('/'))->with('auth_error', __('site.auth.verify.invalid'));
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if (! Auth::guard('web')->check()) {
            Auth::guard('web')->login($user);
        }

        return redirect(Locale::url('/ucet'))->with('status', __('site.auth.verify.success'));
    }

    public function resend(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('site.auth.verify.resent')]);
        }

        return back()->with('status', __('site.auth.verify.resent'));
    }
}
