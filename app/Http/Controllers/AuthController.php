<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Handle the Registration Logic
    public function register(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' checks for password_confirmation field
        ]);

        // 2. Create the User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Always Hash the password!
        ]);

        // 3. Log the user in immediately (Optional)
        Auth::login($user);

        // 4. Redirect to home or home
        return redirect()->route('home')->with('success', 'Registration successful!');
    }

    // Show the Login Form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle the Login Logic
    public function login(Request $request)
    {
        // 1. Validate inputs
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Attempt to Log in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Important for security (prevents session fixation)

            return redirect()->intended('home'); // Redirects to where they wanted to go
        }

        // 3. If failed, go back with error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
