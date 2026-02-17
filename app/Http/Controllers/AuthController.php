<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // Redirect based on role
            if ($user->hasRole('super-admin')) {
                return redirect()->intended('/admin/dashboard');
            }

            if ($user->hasRole('account-owner')) {
                return redirect()->intended(route('dashboard.owner'));
            }

            if ($user->hasRole('marketing-manager')) {
                return redirect()->intended(route('dashboard.marketing'));
            }

            if ($user->hasRole('sales-agent')) {
                return redirect()->intended(route('dashboard.sales'));
            }

            if ($user->hasRole('finance')) {
                return redirect()->intended(route('dashboard.finance')); 
            }

            // Fallback for any unassigned role
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your role does not have access.');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
