<?php

use Illuminate\Support\Facades\Route;

// ─── Público ──────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// ─── Auth (login, register, logout) ──────────────────────────────────────────
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors(['email' => 'Credenciais inválidas.'])->onlyInput('email');
})->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'                  => ['required', 'string', 'max:255'],
        'email'                 => ['required', 'email', 'unique:users'],
        'password'              => ['required', 'min:8', 'confirmed'],
    ]);

    $user = \App\Models\User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
    ]);

    \Illuminate\Support\Facades\Auth::login($user);

    return redirect()->route('dashboard');
})->name('register.post');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('welcome');
})->name('logout');

// ─── Área autenticada ─────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // Meu Setup
    Route::get('/hardware', function () {
        return view('hardware.index');
    })->name('hardware.index');

    // Catálogo
    Route::get('/catalog', function () {
        return view('catalog.index');
    })->name('catalog.index');

    // Bottleneck
    Route::get('/bottleneck', function () {
        return view('bottleneck.index');
    })->name('bottleneck.index');

    // Comparador
    Route::get('/compare', function () {
        return view('compare.index');
    })->name('compare.index');

    // Upgrades
    Route::get('/upgrade', function () {
        return view('upgrade.index');
    })->name('upgrade.index');

});
