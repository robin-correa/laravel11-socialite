<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/redirect', function () {
    return Socialite::driver('github')->redirect();
});

Route::get('/auth/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    // Check if a user with the same email already exists
    $user = User::where('email', $githubUser->email)->first();

    if (! $user) {
        $user = User::create([
            'name' => $githubUser->name ?? $githubUser->nickname,
            'email' => $githubUser->email,
            'github_id' => $githubUser->id,
        ]);
    } else {
        // Update existing user with GitHub data
        $user->update([
            'github_id' => $githubUser->id,
        ]);
    }

    // Log in the user
    Auth::login($user);

    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

// Logout route
Route::post('/logout', function () {
    Auth::logout();

    return redirect('/');
})->name('logout');
