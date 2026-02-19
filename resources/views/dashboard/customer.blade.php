@extends('layouts.admin')

@section('title', 'Customer Portal')

@php
    $companyName = optional(auth()->user()->tenant)->company_name ?? 'No Company';
    $companyInitials = collect(preg_split('/\s+/', trim($companyName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $companyInitials = $companyInitials !== '' ? $companyInitials : 'NC';
    $companyHue = abs(crc32($companyName ?: 'company')) % 360;
    $companyBg = "hsl({$companyHue}, 60%, 42%)";
@endphp

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <div class="company-chip">
            <div class="company-chip-avatar" style="background: {{ $companyBg }};">
                @if(optional(auth()->user()->tenant)->logo_path)
                    <img src="{{ asset('storage/' . auth()->user()->tenant->logo_path) }}" alt="Company Logo">
                @else
                    {{ $companyInitials }}
                @endif
            </div>
            <div class="company-chip-content">
                <span class="company-chip-label">Company</span>
                <span class="company-chip-name">{{ $companyName }}</span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="card">
            <h3>Portal Role</h3>
            <p style="font-size: 20px; font-weight: 700;">Customer</p>
        </div>
        <div class="card">
            <h3>Account Status</h3>
            <p style="font-size: 20px; font-weight: 700;">{{ ucfirst(auth()->user()->status ?? 'active') }}</p>
        </div>
        <div class="card">
            <h3>Last Login</h3>
            <p style="font-size: 20px; font-weight: 700;">{{ optional(auth()->user()->last_login_at)->format('Y-m-d H:i') ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Customer Portal</h3>
        <p style="margin-bottom: 10px; color: #334155; font-weight: 600;">
            Your portal access is active. Use Manage Profile from the account menu to update your personal details and password.
        </p>
        <p style="color: #334155; font-weight: 600;">
            For billing and service inquiries, please contact your account manager or support.
        </p>
    </div>
@endsection
