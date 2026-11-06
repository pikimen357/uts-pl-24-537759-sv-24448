<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            // Sengaja TIDAK mencatat email/password: cukup tahu ada percobaan gagal.
            Log::warning('Login gagal: kredensial tidak cocok', [
                'actor' => 'anonymous',
                'action' => 'auth.login',
                'reason' => $user ? 'password_salah' : 'akun_tidak_ditemukan',
            ]);

            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        Log::info('Login berhasil', [
            'actor' => 'user:'.$user->id,
            'action' => 'auth.login',
            'reason' => 'kredensial_valid',
        ]);

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $user->createToken('api-token')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $userId = $request->user()->id;
        $request->user()->currentAccessToken()->delete();

        Log::info('Logout berhasil, token dicabut', [
            'actor' => 'user:'.$userId,
            'action' => 'auth.logout',
            'reason' => 'permintaan_pengguna',
        ]);

        return response()->json(['message' => 'Logout berhasil']);
    }
}
