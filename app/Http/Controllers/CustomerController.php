<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    // Redirect ke Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Callback dari Google
    public function callback()
    {
        try {
            Log::info('Callback dari Google dimulai');

            // Mendapatkan informasi pengguna dari Google
            $socialUser = Socialite::driver('google')->user();

            // Debug: Tampilkan info socialUser
            Log::info('Data socialUser', [
                'id' => $socialUser->id,
                'email' => $socialUser->email,
                'name' => $socialUser->name,
            ]);

            // Cek apakah email sudah terdaftar
            $registeredUser = User::where('email', $socialUser->email)->first();

            if (!$registeredUser) {
                // Jika email belum terdaftar, buat user baru
                Log::info('Email belum terdaftar, membuat user baru', ['email' => $socialUser->email]);

                $user = User::create([
                    'nama' => $socialUser->name,
                    'email' => $socialUser->email,
                    'role' => '2', // Role customer
                    'status' => 1, // Status aktif
                    'password' => Hash::make('default_password'), // Password default
                ]);

                Log::info('User baru berhasil dibuat', ['user' => $user]);

                // Buat data customer
                $customer = Customer::create([
                    'user_id' => $user->id,
                    'google_id' => $socialUser->id,
                    'google_token' => $socialUser->token
                ]);

                Log::info('Customer baru berhasil dibuat', ['customer' => $customer]);

                // Login pengguna baru
                Auth::login($user);
            } else {
                // Jika email sudah terdaftar, langsung login
                Log::info('Email sudah terdaftar, langsung login', ['email' => $socialUser->email]);
                Auth::login($registeredUser);
            }

            // Redirect ke halaman utama setelah login
            return redirect()->intended('beranda');
        } catch (\Exception $e) {
            // Log error jika terjadi masalah
            Log::error('Terjadi kesalahan saat login dengan Google', [
                'error' => $e->getMessage(),
            ]);

            // Redirect ke halaman utama jika terjadi kesalahan
            return redirect('/')->with('error', 'Terjadi kesalahan saat login dengan Google.');
        }
    }

    // Fungsi logout
    public function logout(Request $request)
    {
        Log::info('User logout dimulai');

        Auth::logout(); // Logout pengguna
        $request->session()->invalidate(); // Hapus session
        $request->session()->regenerateToken(); // Regenerate token CSRF

        Log::info('User berhasil logout');

        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }
}
