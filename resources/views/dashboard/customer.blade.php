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

    <div class="kpi-cards">
        <div class="card">
            <h3>Subscription Status</h3>
            <p>{{ $subscriptionStatus }}</p>
        </div>
        <div class="card">
            <h3>Subscription Plan</h3>
            <p>{{ $subscriptionPlan }}</p>
        </div>
        <div class="card">
            <h3>Profile</h3>
            <p style="font-size: 18px;">{{ auth()->user()->name }}</p>
        </div>
        <div class="card">
            <h3>Company</h3>
            <p style="font-size: 18px;">{{ $companyName }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Recent Payments / Invoices</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($payment->status) }}</td>
                        <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No payment or invoice records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
