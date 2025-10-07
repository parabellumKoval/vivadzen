<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

use Illuminate\Support\Facades\Auth;

/**
 * @group Auth
 *
 * APIs for authentication
 */
class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('profile')->attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json('good');
        }

        return response()->json([
            'email' => 'The provided credentials do not match our records.',
        ], 400);
    }

    public function profile(Request $request)
    {
        return response()->json('YES');
    }
}
