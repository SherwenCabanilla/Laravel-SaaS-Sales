@extends('layouts.admin')

@section('title', 'Finance Dashboard')

@section('styles')
    <style>
        .chart-heading { display:flex; align-items:center; gap:8px; margin:0 0 10px; }
        .chart-heading h3 { margin:0; }
        .chart-help-wrap { position:relative; display:inline-flex; }
        .chart-help-dot { display:inline-flex; align-items:center; justify-content:center; width:18px; height:18px; border-radius:50%; border:1px solid var(--theme-primary, #240E35); color:var(--theme-primary, #240E35); background:#fff; font-size:11px; font-weight:800; cursor:help; line-height:1; }
        .chart-help-tip { position:absolute; left:50%; top:calc(100% + 8px); transform:translateX(-50%); min-width:220px; max-width:280px; padding:8px 10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff; color:var(--theme-primary, #240E35); font-size:12px; font-weight:700; line-height:1.4; box-shadow:0 10px 24px rgba(15,23,42,.12); opacity:0; visibility:hidden; pointer-events:none; z-index:20; }
        .chart-help-wrap:hover .chart-help-tip,
        .chart-help-wrap:focus-within .chart-help-tip { opacity:1; visibility:visible; }
    </style>
@endsection

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
            <h3>Paid Total</h3>
            <p>₱{{ number_format((float) ($statusAmounts['paid'] ?? 0), 2) }}</p>
        </div>
        <div class="card">
            <h3>Pending Total</h3>
            <p>₱{{ number_format((float) ($statusAmounts['pending'] ?? 0), 2) }}</p>
        </div>
        <div class="card">
            <h3>Failed Total</h3>
            <p>₱{{ number_format((float) ($statusAmounts['failed'] ?? 0), 2) }}</p>
        </div>
        <div class="card">
            <h3>Outstanding Invoices</h3>
            <p>{{ $outstandingCount }} ({{ '₱' . number_format($outstandingAmount, 2) }})</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <div class="chart-heading">
                <h3>Payment Collection Trend (Paid)</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Collection trend help">?</span>
                    <span class="chart-help-tip">Shows paid amount collected over time.</span>
                </span>
            </div>
            <canvas id="collectionTrendChart"></canvas>
        </div>
        <div class="chart">
            <div class="chart-heading">
                <h3>Payment Status Distribution</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Payment distribution help">?</span>
                    <span class="chart-help-tip">Shows how payments are split across paid, pending, and failed statuses.</span>
                </span>
            </div>
            <canvas id="paymentStatusChart"></canvas>
        </div>
    </div>

    <div class="card">
        <h3>Needs Action Now (Pending Invoices)</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment Date</th>
                    <th>Lead</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingInvoices as $invoice)
                    <tr>
                        <td>{{ $invoice->payment_date->format('Y-m-d') }}</td>
                        <td>{{ $invoice->lead->name ?? 'N/A' }}</td>
                        <td>₱{{ number_format((float) $invoice->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No pending invoices.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 16px;">
            {{ $pendingInvoices->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const collectionTrendCtx = document.getElementById('collectionTrendChart').getContext('2d');
        new Chart(collectionTrendCtx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'Collected Amount',
                    data: @json($trendValues),
                    borderColor: '#240E35',
                    backgroundColor: 'rgba(36, 14, 53, 0.15)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
        new Chart(paymentStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Failed'],
                datasets: [{
                    data: [
                        {{ (int) ($statusCounts['paid'] ?? 0) }},
                        {{ (int) ($statusCounts['pending'] ?? 0) }},
                        {{ (int) ($statusCounts['failed'] ?? 0) }}
                    ],
                    backgroundColor: ['#240E35', '#6B4A7A', '#9E7BB5']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
@endsection
