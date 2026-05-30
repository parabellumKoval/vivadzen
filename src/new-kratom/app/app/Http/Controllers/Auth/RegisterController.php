<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Locale;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'marketing_consent' => 'sometimes|boolean',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'marketing_consent' => (bool) ($data['marketing_consent'] ?? false),
            'locale' => app()->getLocale(),
        ]);

        event(new Registered($user));

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'verified' => false,
            'redirect' => Locale::url('/ucet'),
        ], 201);
    }
}
