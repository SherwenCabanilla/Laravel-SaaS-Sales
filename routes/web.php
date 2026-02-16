<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AuthController;

// Login & Logout routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -----------------------------
// Admin Dashboard — Super Admin Only
Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// -----------------------------
// Leads Routes — Accessible by Sales Agent, Marketing Manager, Account Owner
Route::middleware(['auth', 'role:sales-agent,marketing-manager,account-owner'])->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::delete('/leads/{lead}/delete', [LeadController::class, 'destroy'])->name('leads.destroy');
});
