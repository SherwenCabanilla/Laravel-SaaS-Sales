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

    <div class="admin-kpi-board">
        <section class="admin-kpi-group" aria-label="Customer Overview">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Customer Overview</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--4">
                <article class="admin-kpi-card admin-kpi-card--primary">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Subscription Status</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-signal" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value admin-kpi-card__value--text">{{ $subscriptionStatus }}</div>
                    <div class="admin-kpi-card__meta">Current billing and access state for your account</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Subscription Plan</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value admin-kpi-card__value--text">{{ $subscriptionPlan }}</div>
                    <div class="admin-kpi-card__meta">Plan currently attached to your tenant workspace</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Profile</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-user" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value admin-kpi-card__value--text">{{ auth()->user()->name }}</div>
                    <div class="admin-kpi-card__meta">Primary account identity for this customer portal</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Company</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value admin-kpi-card__value--text">{{ $companyName }}</div>
                    <div class="admin-kpi-card__meta">Organization currently associated with your login</div>
                </article>
            </div>
        </section>
    </div>

    <div class="card">
        <h3>Recent Payments / Invoices</h3>
        <div class="app-table-scroll app-table-scroll--wide">
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
        <div style="margin-top: 16px;">
            {{ $recentPayments->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
