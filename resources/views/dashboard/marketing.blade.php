@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

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
            <h3>Leads Generated</h3>
            <p>{{ (int) $sourceBreakdownChart->sum('total') }}</p>
        </div>
        <div class="card">
            <h3>MQL Volume</h3>
            <p>{{ $mqlCount }}</p>
        </div>
        <div class="card">
            <h3>Quality Proxy</h3>
            <p>{{ number_format($avgLeadScore, 1) }}</p>
        </div>
        <div class="card">
            <h3>Cost Proxy</h3>
            <p style="font-size: 18px;">Ad Spend Data N/A</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <div class="chart-heading">
                <h3>MQL Trend (Score >= {{ $mqlThreshold }})</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="MQL trend help">?</span>
                    <span class="chart-help-tip">Shows how many marketing-qualified leads you got each month.</span>
                </span>
            </div>
            <canvas id="mqlTrendChart"></canvas>
        </div>
        <div class="chart">
            <div class="chart-heading">
                <h3>Leads by Source/Campaign</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Source chart help">?</span>
                    <span class="chart-help-tip">Compares lead volume by source or campaign.</span>
                </span>
            </div>
            <canvas id="sourceChart"></canvas>
        </div>
    </div>

    <div class="card">
        <h3>Needs Action Now</h3>
        <table>
            <thead>
                <tr>
                    <th>Source/Campaign</th>
                    <th>Leads</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sourceBreakdown as $row)
                    <tr>
                        <td>{{ $row->source_label }}</td>
                        <td>{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No lead source data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 16px;">
            {{ $sourceBreakdown->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const mqlTrendCtx = document.getElementById('mqlTrendChart').getContext('2d');
        new Chart(mqlTrendCtx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'MQL Leads',
                    data: @json($trendValues),
                    borderColor: '#240E35',
                    backgroundColor: 'rgba(36, 14, 53, 0.15)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: @json($sourceBreakdownChart->pluck('source_label')->values()),
                datasets: [{
                    label: 'Leads',
                    data: @json($sourceBreakdownChart->pluck('total')->values()),
                    backgroundColor: '#6B4A7A'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
@endsection
