@extends('layouts.admin')

@section('title', 'Account Owner Dashboard')

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

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="card">
            <h3>Total Leads</h3>
            <p style="font-size: 26px; font-weight: 700;">{{ $totalLeads }}</p>
        </div>
        <div class="card">
            <h3>Leads This Month</h3>
            <p style="font-size: 26px; font-weight: 700;">{{ $leadsThisMonth }}</p>
        </div>
        <div class="card">
            <h3>Conversion Rate</h3>
            <p style="font-size: 26px; font-weight: 700;">{{ $conversionRate }}%</p>
        </div>
        <div class="card">
            <h3>Paid Revenue</h3>
            <p style="font-size: 26px; font-weight: 700;">${{ number_format($revenueTotal, 2) }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Leads by Status</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leadsByStatus as $status => $count)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $status)) }}</td>
                        <td>{{ $count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No lead data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
