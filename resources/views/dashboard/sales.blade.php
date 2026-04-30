@extends('layouts.admin')

@section('title', 'Sales Dashboard')

@section('styles')
        <link rel="stylesheet" href="{{ asset('css/extracted/dashboard-sales-style1.css') }}">
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

    <div class="admin-kpi-board">
        <section class="admin-kpi-group" aria-label="Sales Snapshot">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Sales Snapshot</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--3">
                <article class="admin-kpi-card admin-kpi-card--primary">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">My Assigned Leads</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-address-card" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($myAssignedLeadsCount) }}</div>
                    <div class="admin-kpi-card__meta">Active leads currently assigned to your pipeline</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--danger">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Overdue Follow-ups</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-bell" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($overdueFollowUpsCount) }}</div>
                    <div class="admin-kpi-card__meta">Leads that need immediate response or outreach</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--warning">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Today Tasks</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-calendar-day" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($todayTaskCount) }}</div>
                    <div class="admin-kpi-card__meta">Follow-up actions and selling tasks due today</div>
                </article>
            </div>
        </section>
    </div>

    <div class="charts">
        <div class="chart">
            <div class="chart-heading">
                <h3>My Pipeline Stage Counts</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Pipeline chart help">?</span>
                    <span class="chart-help-tip">Shows the number of your assigned leads in each pipeline stage.</span>
                </span>
            </div>
            <canvas id="salesPipelineChart"></canvas>
        </div>
        <div class="chart">
            <h3>Needs Action Now</h3>
            <div class="app-table-scroll app-table-scroll--wide">
            <table>
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Status</th>
                        <th>Last Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueLeads as $lead)
                        <tr>
                            <td>{{ $lead->name }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $lead->status)) }}</td>
                            <td>{{ $lead->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No overdue follow-ups.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div style="margin-top: 12px;">
                {{ $overdueLeads->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <div class="card">
        <h3>My Recent Assigned Leads</h3>
        <div class="app-table-scroll app-table-scroll--wide">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myRecentLeads as $lead)
                    <tr>
                        <td>{{ $lead->name }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $lead->status)) }}</td>
                        <td>{{ $lead->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No assigned leads found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div style="margin-top: 16px;">
            {{ $myRecentLeads->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>Commission Snapshot</h3>
        <div class="app-table-scroll app-table-scroll--wide">
        <table>
            <thead>
                <tr>
                    <th>Held</th>
                    <th>Payable</th>
                    <th>Paid</th>
                    <th>Active Entries</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PHP {{ number_format((float) data_get($commissionSummary, 'held_total', 0), 2) }}</td>
                    <td>PHP {{ number_format((float) data_get($commissionSummary, 'payable_total', 0), 2) }}</td>
                    <td>PHP {{ number_format((float) data_get($commissionSummary, 'paid_total', 0), 2) }}</td>
                    <td>{{ number_format((int) data_get($commissionSummary, 'active_count', 0)) }}</td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>
@endsection

@section('scripts')
    @php
        $salesPipelineLabels = array_map(function ($status) {
            return ucwords(str_replace('_', ' ', $status));
        }, array_keys($pipelineStageCounts));
    @endphp
    <script>
        const salesPipelineCtx = document.getElementById('salesPipelineChart').getContext('2d');
        new Chart(salesPipelineCtx, {
            type: 'bar',
            data: {
                labels: @json($salesPipelineLabels),
                datasets: [{
                    label: 'Leads',
                    data: @json(array_values($pipelineStageCounts)),
                    backgroundColor: '#240E35'
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

