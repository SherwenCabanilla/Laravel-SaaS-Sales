@extends('layouts.admin')

@section('title', 'Finance Dashboard')

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
        <div>
            <h1>Welcome, {{ auth()->user()->name }}</h1>
            <p>This is your Finance Dashboard.</p>
        </div>
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

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3>Financial Overview</h3>
        <p>Manage billing, invoices, and subscription details.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="background: #fff beb; padding: 15px; border-radius: 6px;">
                <h4 style="margin: 0; color: #b45309;">Outstanding Invoices</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">$1,250.00</p>
            </div>
             <div style="background: #ecfdf5; padding: 15px; border-radius: 6px;">
                <h4 style="margin: 0; color: #047857;">MCR (Monthly Reoccurring)</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">$4,500.00</p>
            </div>
        </div>
    </div>
@endsection
