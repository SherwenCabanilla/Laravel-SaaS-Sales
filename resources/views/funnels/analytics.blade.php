@extends('layouts.admin')

@section('title', 'Funnel Analytics')

@section('styles')
    <style>
        .analytics-shell { display: grid; gap: 18px; }
        .analytics-topbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; }
        .analytics-topbar h1 { margin: 0; color: var(--theme-primary, #240E35); }
        .analytics-topbar p { margin: 6px 0 0; color: var(--theme-muted, #6B7280); }
        .analytics-actions { display:flex; flex-wrap:wrap; gap:10px; }
        .analytics-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; border-radius:10px; border:1px solid var(--theme-border, #E6E1EF); background:#fff; color:var(--theme-primary, #240E35); text-decoration:none; font-weight:700; }
        .analytics-btn.primary { background: var(--theme-primary, #240E35); color:#fff; border-color: var(--theme-primary, #240E35); }
        .analytics-toggle-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:9px 14px; border-radius:10px; border:1px solid var(--theme-border, #E6E1EF); background:var(--theme-primary, #240E35); color:#fff; font-weight:800; cursor:pointer; }
        .analytics-alert { border-radius:14px; border:1px solid; padding:14px 16px; font-weight:600; }
        .analytics-alert--success { background:#ecfdf3; border-color:#a7f3d0; color:#065f46; }
        .analytics-alert--error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .analytics-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
        .analytics-field { display:grid; gap:6px; min-width:180px; }
        .analytics-field label { font-size:12px; font-weight:800; color: var(--theme-muted, #6B7280); text-transform:uppercase; letter-spacing:.04em; }
        .analytics-field input,
        .analytics-field select { padding:10px 12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff; }
        .analytics-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
        .analytics-kpi { background:#fff; border:1px solid var(--theme-border, #E6E1EF); border-radius:16px; padding:18px; box-shadow:0 10px 30px rgba(15,23,42,.04); }
        .analytics-kpi-label { font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color: var(--theme-muted, #6B7280); }
        .analytics-kpi-value { margin-top:10px; font-size:30px; font-weight:900; color: var(--theme-primary, #240E35); }
        .analytics-kpi-sub { margin-top:8px; color: var(--theme-muted, #6B7280); font-size:13px; }
        .analytics-grid { display:grid; grid-template-columns:2fr 1fr; gap:18px; }
        .analytics-grid.analytics-grid--summary { grid-template-columns:minmax(0, 1.7fr) minmax(320px, .95fr) minmax(300px, .85fr); align-items:stretch; }
        .analytics-card { background:#fff; border:1px solid var(--theme-border, #E6E1EF); border-radius:18px; padding:18px; box-shadow:0 10px 30px rgba(15,23,42,.04); }
        .analytics-card h3 { margin:0 0 14px; color: var(--theme-primary, #240E35); }
        .analytics-chart-wrap { position:relative; min-height:280px; }
        .analytics-chart-wrap canvas { width:100% !important; height:100% !important; display:block; }
        .analytics-card.analytics-card--step-visits { width:100%; }
        .analytics-card.analytics-card--step-visits .analytics-chart-wrap { min-height: 260px; }
        .analytics-card.analytics-card--offer-rates { width:100%; }
        .analytics-card.analytics-card--offer-rates .analytics-chart-wrap { min-height: 260px; max-height: 260px; display:flex; align-items:center; justify-content:center; }
        .analytics-card.analytics-card--offer-rates .analytics-chart-wrap canvas { max-width:300px !important; max-height:300px !important; margin:0 auto; }
        .analytics-card.analytics-card--offer-counts { width:100%; }
        .analytics-card.analytics-card--physical-overview { width:100%; }
        .analytics-callout { padding:14px 16px; border-radius:14px; border:1px solid var(--theme-border, #E6E1EF); background:linear-gradient(180deg,#fff,#faf7ff); color:var(--theme-muted, #6B7280); }
        .analytics-callout strong { display:block; margin-bottom:8px; color:var(--theme-primary, #240E35); }
        .analytics-list { display:grid; gap:10px; margin:0; padding:0; list-style:none; }
        .analytics-list li { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding-bottom:10px; border-bottom:1px solid var(--theme-border, #E6E1EF); }
        .analytics-list li:last-child { border-bottom:none; padding-bottom:0; }
        .analytics-list-label { font-weight:700; color:var(--theme-primary, #240E35); }
        .analytics-list-meta { display:block; margin-top:4px; font-size:12px; color:var(--theme-muted, #6B7280); }
        .analytics-list-value { font-weight:900; color:var(--theme-primary, #240E35); white-space:nowrap; }
        .analytics-chart-empty { min-height:280px; display:grid; place-items:center; text-align:center; padding:20px; border-radius:14px; background: var(--theme-surface-softer, #F7F7FB); color: var(--theme-muted, #6B7280); }
        .analytics-table-wrap { overflow:auto; }
        .analytics-table { width:100%; border-collapse:collapse; min-width:640px; }
        .analytics-table th, .analytics-table td { padding:12px 10px; border-bottom:1px solid var(--theme-border, #E6E1EF); text-align:left; vertical-align:top; }
        .analytics-table th { font-size:12px; text-transform:uppercase; letter-spacing:.05em; color: var(--theme-muted, #6B7280); }
        .analytics-pill { display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; background: var(--theme-surface-soft, #F3EEF7); color: var(--theme-primary, #240E35); font-size:12px; font-weight:800; }
        .analytics-mini-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .analytics-mini-stat { border:1px solid var(--theme-border, #E6E1EF); border-radius:14px; padding:14px; background:linear-gradient(180deg,#fff,#fcfbfe); }
        .analytics-mini-stat--button { width:100%; appearance:none; text-align:left; cursor:pointer; transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease; }
        .analytics-mini-stat--button:hover { transform:translateY(-1px); box-shadow:0 10px 24px rgba(15,23,42,.08); border-color:rgba(36, 14, 53, .18); }
        .analytics-mini-stat--button:focus-visible { outline:3px solid rgba(124,58,237,.24); outline-offset:2px; }
        .analytics-mini-stat span { display:block; font-size:12px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color: var(--theme-muted, #6B7280); }
        .analytics-mini-stat strong { display:block; margin-top:8px; font-size:24px; color: var(--theme-primary, #240E35); }
        .analytics-mini-stat small { display:block; margin-top:8px; color: var(--theme-muted, #6B7280); font-size:12px; }
        .analytics-events { display:grid; gap:12px; }
        .analytics-event { border:1px solid var(--theme-border, #E6E1EF); border-radius:14px; padding:14px; background:linear-gradient(180deg,#fff,#fcfbfe); }
        .analytics-event-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; }
        .analytics-event-meta { color: var(--theme-muted, #6B7280); font-size:13px; line-height:1.5; }
        .analytics-empty { padding:18px; border-radius:14px; background: var(--theme-surface-softer, #F7F7FB); color: var(--theme-muted, #6B7280); }
        .analytics-section-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; margin-bottom:14px; }
        .analytics-table-row-hidden { display:none; }
        .analytics-modal[hidden] { display:none !important; }
        .analytics-modal { position:fixed; inset:0; z-index:1100; display:grid; place-items:center; padding:20px; }
        .analytics-modal-backdrop { position:absolute; inset:0; background:rgba(15, 23, 42, .48); }
        .analytics-modal-dialog { position:relative; width:min(920px, calc(100vw - 32px)); max-height:calc(100vh - 40px); overflow:auto; border-radius:20px; border:1px solid var(--theme-border, #E6E1EF); background:#fff; box-shadow:0 24px 70px rgba(15,23,42,.28); }
        .analytics-modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:14px; padding:20px 20px 0; }
        .analytics-modal-head h3 { margin:0; }
        .analytics-modal-head p { margin:6px 0 0; color:var(--theme-muted, #6B7280); }
        .analytics-modal-close { appearance:none; border:1px solid var(--theme-border, #E6E1EF); background:#fff; color:var(--theme-primary, #240E35); width:40px; height:40px; border-radius:999px; font-size:18px; font-weight:900; cursor:pointer; }
        .analytics-modal-body { padding:18px 20px 20px; }
        .analytics-inline-form { display:grid; gap:8px; min-width:280px; }
        .analytics-inline-form input,
        .analytics-inline-form select,
        .analytics-inline-form textarea { width:100%; padding:9px 10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff; font:inherit; }
        .analytics-inline-form textarea { min-height:72px; resize:vertical; }
        .analytics-inline-form-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .analytics-link { color:#1d4ed8; text-decoration:none; font-weight:700; }
        @media (max-width: 1200px) {
            .analytics-grid.analytics-grid--summary { grid-template-columns:1fr; }
        }
        @media (max-width: 960px) {
            .analytics-grid { grid-template-columns:1fr; }
        }
    </style>
@endsection

@section('content')
    @php
        $totals = $analytics['totals'] ?? [];
        $rates = $analytics['rates'] ?? [];
        $offerCounts = $analytics['offer_counts'] ?? [];
        $stepVisits = collect($analytics['step_visits'] ?? []);
        $dropOff = collect($analytics['drop_off'] ?? []);
        $eventBreakdown = collect($analytics['step_event_breakdown'] ?? []);
        $dailySeries = collect($analytics['daily_series'] ?? []);
        $conversionFunnel = collect($analytics['conversion_funnel'] ?? []);
        $stepLabels = $stepVisits->map(fn ($row) => $row['step_title'])->values()->all();
        $stepValues = $stepVisits->map(fn ($row) => (int) $row['visits'])->values()->all();
        $stepDropOffValues = $dropOff->map(fn ($row) => (int) ($row['drop_off'] ?? 0))->values()->all();
        $dailyLabels = $dailySeries->pluck('date')->values()->all();
        $dailyVisitValues = $dailySeries->pluck('entry_visits')->map(fn ($value) => (int) $value)->values()->all();
        $dailyOptInValues = $dailySeries->pluck('opt_ins')->map(fn ($value) => (int) $value)->values()->all();
        $dailyCheckoutValues = $dailySeries->pluck('checkout_starts')->map(fn ($value) => (int) $value)->values()->all();
        $dailyPaidValues = $dailySeries->pluck('paid')->map(fn ($value) => (int) $value)->values()->all();
        $conversionLabels = $conversionFunnel->pluck('label')->values()->all();
        $conversionValues = $conversionFunnel->pluck('count')->map(fn ($value) => (int) $value)->values()->all();
        $funnelPurpose = \App\Models\Funnel::normalizePurpose($funnel->purpose ?? ($funnel->template_type ?? 'service'));
        $isPhysicalAnalytics = in_array($funnelPurpose, ['physical_product', 'hybrid'], true);
        $summarySectionTitle = $isPhysicalAnalytics ? 'Order Directory' : 'Offer Activity Table';
        $selectedOfferLabel = $isPhysicalAnalytics ? 'Selected Product' : 'Selected Service';
        $physicalOrders = collect($analytics['physical_orders'] ?? []);
        $physicalOrderTotals = $analytics['physical_order_totals'] ?? [];
        $physicalPendingOrders = collect($analytics['physical_pending_orders'] ?? []);
        $physicalPaidOrders = collect($analytics['physical_paid_orders'] ?? []);
        $physicalProductBreakdown = collect($analytics['physical_product_breakdown'] ?? []);
        $checkoutToPaidRate = (int) ($totals['checkout_start_count'] ?? 0) > 0
            ? round((((int) ($physicalOrderTotals['paid_orders'] ?? 0)) / max(1, (int) ($totals['checkout_start_count'] ?? 0))) * 100, 2)
            : 0;
        $deliveryStatusOptions = [
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
        ];
        $offerActivityGroups = [
            'upsell_accepted' => [
                'title' => 'Upsell Accepted',
                'description' => 'Customers who accepted the upsell offer.',
                'action' => 'Accepted',
                'offer_type' => 'Upsell',
                'count' => (int) ($offerCounts['upsell_accepted'] ?? 0),
                'rows' => $analytics['offer_activity']['upsell_accepted'] ?? [],
            ],
            'upsell_declined' => [
                'title' => 'Upsell Declined',
                'description' => 'Customers who declined the upsell offer.',
                'action' => 'Declined',
                'offer_type' => 'Upsell',
                'count' => (int) ($offerCounts['upsell_declined'] ?? 0),
                'rows' => $analytics['offer_activity']['upsell_declined'] ?? [],
            ],
            'downsell_accepted' => [
                'title' => 'Downsell Accepted',
                'description' => 'Customers who accepted the downsell offer.',
                'action' => 'Accepted',
                'offer_type' => 'Downsell',
                'count' => (int) ($offerCounts['downsell_accepted'] ?? 0),
                'rows' => $analytics['offer_activity']['downsell_accepted'] ?? [],
            ],
            'downsell_declined' => [
                'title' => 'Downsell Declined',
                'description' => 'Customers who declined the downsell offer.',
                'action' => 'Declined',
                'offer_type' => 'Downsell',
                'count' => (int) ($offerCounts['downsell_declined'] ?? 0),
                'rows' => $analytics['offer_activity']['downsell_declined'] ?? [],
            ],
        ];
        $offerCustomerSummary = collect($analytics['offer_customer_summary'] ?? []);
        $offerRateValues = [
            (float) ($rates['upsell_acceptance_rate'] ?? 0),
            (float) ($rates['downsell_acceptance_rate'] ?? 0),
            (float) ($rates['abandoned_checkout_rate'] ?? 0),
        ];
        $summaryRows = $isPhysicalAnalytics ? $physicalOrders : $offerCustomerSummary;
        $hasOfferData = ((int) ($offerCounts['upsell_accepted'] ?? 0) > 0)
            || ((int) ($offerCounts['upsell_declined'] ?? 0) > 0)
            || ((int) ($offerCounts['downsell_accepted'] ?? 0) > 0)
            || ((int) ($offerCounts['downsell_declined'] ?? 0) > 0)
            || ((float) ($rates['abandoned_checkout_rate'] ?? 0) > 0);
    @endphp

    <div class="analytics-shell">
        @if(session('success'))
            <div class="analytics-alert analytics-alert--success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="analytics-alert analytics-alert--error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="analytics-alert analytics-alert--error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="analytics-topbar">
            <div>
                <h1>{{ $funnel->name }} Analytics</h1>
                <p>
                    @if($isPhysicalAnalytics)
                        Manage physical-product orders here. Focus on pending orders, paid orders, product quantities, and delivery updates you can email directly to customers.
                    @else
                        Track views, opt-ins, checkout starts, revenue, drop-off, and recent funnel events in one place.
                    @endif
                </p>
            </div>
            <div class="analytics-actions">
                <a href="{{ route('funnels.index') }}" class="analytics-btn"><i class="fas fa-arrow-left"></i> Back to Funnels</a>
                <a href="{{ route('funnels.edit', $funnel) }}" class="analytics-btn primary"><i class="fas fa-pen"></i> Open Builder</a>
                <a href="{{ route('funnels.analytics.export', array_merge(['funnel' => $funnel], request()->query())) }}" class="analytics-btn"><i class="fas fa-file-export"></i> Export CSV</a>
            </div>
        </div>

        @if($isPhysicalAnalytics)
            <div class="analytics-callout">
                <strong>Physical funnel view</strong>
                This screen is organized around orders first. Focus on revenue, paid orders, pending orders, and product movement. Traffic metrics are still available, but they are secondary here.
            </div>
        @endif

        <div class="analytics-card">
            <form method="GET" action="{{ route('funnels.analytics', $funnel) }}" class="analytics-filters">
                <div class="analytics-field">
                    <label for="from">From</label>
                    <input id="from" type="date" name="from" value="{{ $filters['from'] }}">
                </div>
                <div class="analytics-field">
                    <label for="to">To</label>
                    <input id="to" type="date" name="to" value="{{ $filters['to'] }}">
                </div>
                @unless($isPhysicalAnalytics)
                    <div class="analytics-field">
                        <label for="step_id">Step</label>
                        <select id="step_id" name="step_id">
                            <option value="">All steps</option>
                            @foreach($funnel->steps->sortBy('position') as $step)
                                <option value="{{ $step->id }}" {{ (string) ($filters['step_id'] ?? '') === (string) $step->id ? 'selected' : '' }}>
                                    {{ $step->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="analytics-field">
                        <label for="event_name">Event</label>
                        <select id="event_name" name="event_name">
                            <option value="">All events</option>
                            @foreach($supportedEvents as $eventName)
                                <option value="{{ $eventName }}" {{ (string) ($filters['event_name'] ?? '') === (string) $eventName ? 'selected' : '' }}>
                                    {{ $eventName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endunless
                <button type="submit" class="analytics-btn primary"><i class="fas fa-filter"></i> Apply Filter</button>
                <a href="{{ route('funnels.analytics', $funnel) }}" class="analytics-btn">Clear</a>
            </form>
        </div>

        <div class="analytics-kpis">
            @if($isPhysicalAnalytics)
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Revenue</div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue'] ?? 0), 2) }}</div>
                    <div class="analytics-kpi-sub">Paid revenue from completed orders</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Paid Orders</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($physicalOrderTotals['paid_orders'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) $checkoutToPaidRate, 2) }}% of checkout starts became paid orders</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Pending Orders</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($physicalOrderTotals['pending_orders'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">Orders waiting for payment completion</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Units Ordered</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($physicalOrderTotals['units_ordered'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">Total item quantity across non-abandoned orders</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Abandoned Checkouts</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['abandoned_checkout_count'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) ($rates['abandoned_checkout_rate'] ?? 0), 2) }}% abandonment after checkout started</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Traffic Snapshot</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['entry_visits'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((int) ($totals['checkout_start_count'] ?? 0)) }} checkout starts | PHP {{ number_format((float) ($totals['revenue_per_visit'] ?? 0), 2) }} per visit</div>
                </div>
            @else
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Entry Visits</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['entry_visits'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">Unique first-step visits</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Opt-ins</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['opt_in_count'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) ($rates['opt_in_conversion_rate'] ?? 0), 2) }}% conversion</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Checkout Starts</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['checkout_start_count'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) ($rates['checkout_conversion_rate'] ?? 0), 2) }}% conversion</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Paid</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['paid_count'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) ($rates['paid_conversion_rate'] ?? 0), 2) }}% conversion</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Revenue</div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue'] ?? 0), 2) }}</div>
                    <div class="analytics-kpi-sub">Paid revenue tied to this funnel</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Abandoned Checkout</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['abandoned_checkout_count'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) ($rates['abandoned_checkout_rate'] ?? 0), 2) }}% abandonment</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Average Order Value</div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['average_order_value'] ?? 0), 2) }}</div>
                    <div class="analytics-kpi-sub">Average paid order amount</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Revenue Per Visit</div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue_per_visit'] ?? 0), 2) }}</div>
                    <div class="analytics-kpi-sub">Revenue divided by entry visits</div>
                </div>
            @endif
        </div>

        <div class="analytics-grid analytics-grid--summary">
            @if($isPhysicalAnalytics)
                <div class="analytics-card analytics-card--physical-overview">
                    <h3>Sales Overview</h3>
                    <ul class="analytics-list">
                        <li>
                            <div>
                                <span class="analytics-list-label">Order conversion</span>
                                <span class="analytics-list-meta">Paid orders divided by checkout starts</span>
                            </div>
                            <span class="analytics-list-value">{{ number_format((float) $checkoutToPaidRate, 2) }}%</span>
                        </li>
                        <li>
                            <div>
                                <span class="analytics-list-label">Checkout starts</span>
                                <span class="analytics-list-meta">Visitors who began the payment step</span>
                            </div>
                            <span class="analytics-list-value">{{ number_format((int) ($totals['checkout_start_count'] ?? 0)) }}</span>
                        </li>
                        <li>
                            <div>
                                <span class="analytics-list-label">Entry visits</span>
                                <span class="analytics-list-meta">Unique first-step visitors</span>
                            </div>
                            <span class="analytics-list-value">{{ number_format((int) ($totals['entry_visits'] ?? 0)) }}</span>
                        </li>
                        <li>
                            <div>
                                <span class="analytics-list-label">Revenue per visit</span>
                                <span class="analytics-list-meta">Paid revenue divided by entry visits</span>
                            </div>
                            <span class="analytics-list-value">PHP {{ number_format((float) ($totals['revenue_per_visit'] ?? 0), 2) }}</span>
                        </li>
                    </ul>
                </div>
                <div class="analytics-card analytics-card--offer-counts">
                    <h3>Order Status</h3>
                    <div class="analytics-mini-grid">
                        <div class="analytics-mini-stat">
                            <span>Pending</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['pending_orders'] ?? 0)) }}</strong>
                            <small>Orders waiting for payment confirmation</small>
                        </div>
                        <div class="analytics-mini-stat">
                            <span>Paid</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['paid_orders'] ?? 0)) }}</strong>
                            <small>Orders that completed payment</small>
                        </div>
                        <div class="analytics-mini-stat">
                            <span>Abandoned</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['abandoned_orders'] ?? 0)) }}</strong>
                            <small>Checkout starts without paid completion</small>
                        </div>
                        <div class="analytics-mini-stat">
                            <span>Units Ordered</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['units_ordered'] ?? 0)) }}</strong>
                            <small>Total quantity across active orders</small>
                        </div>
                    </div>
                </div>

                <div class="analytics-card analytics-card--offer-counts">
                    <h3>Product Breakdown</h3>
                    <div class="analytics-table-wrap">
                        <table class="analytics-table" style="min-width:420px;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Units</th>
                                    <th>Orders</th>
                                    <th>Paid Units</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($physicalProductBreakdown as $item)
                                    <tr>
                                        <td><strong>{{ $item['name'] ?? 'Product' }}</strong></td>
                                        <td>{{ number_format((int) ($item['units'] ?? 0)) }}</td>
                                        <td>{{ number_format((int) ($item['orders'] ?? 0)) }}</td>
                                        <td>{{ number_format((int) ($item['paid_units'] ?? 0)) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No product quantities have been recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="analytics-card analytics-card--step-visits">
                    <h3>Step Visits</h3>
                    <div class="analytics-chart-wrap">
                        <canvas id="stepVisitsChart"></canvas>
                    </div>
                </div>
                <div class="analytics-card analytics-card--offer-rates">
                    <h3>Offer Rates</h3>
                    @if($hasOfferData)
                        <div class="analytics-chart-wrap">
                            <canvas id="offerRatesChart"></canvas>
                        </div>
                    @else
                        <div class="analytics-chart-empty">
                            <div>
                                <strong style="display:block; margin-bottom:8px; color:var(--theme-primary, #240E35);">No offer data yet</strong>
                                Complete checkout in the public funnel and click the upsell or downsell accept/decline buttons to populate this section.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="analytics-card analytics-card--offer-counts">
                    <h3>Offer Counts</h3>
                    <div class="analytics-mini-grid">
                        @foreach($offerActivityGroups as $groupKey => $group)
                            <button
                                type="button"
                                class="analytics-mini-stat analytics-mini-stat--button"
                                data-offer-activity="{{ $groupKey }}"
                                aria-haspopup="dialog"
                                aria-controls="offerActivityModal"
                            >
                                <span>{{ $group['title'] }}</span>
                                <strong>{{ number_format($group['count']) }}</strong>
                                <small>Click to view customers</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @unless($isPhysicalAnalytics)
            <div class="analytics-grid">
                <div class="analytics-card">
                    <h3>Daily Funnel Trend</h3>
                    <div class="analytics-chart-wrap">
                        <canvas id="dailyTrendChart"></canvas>
                    </div>
                </div>

                <div class="analytics-card">
                    <h3>Conversion Path</h3>
                    <div class="analytics-chart-wrap">
                        <canvas id="conversionPathChart"></canvas>
                    </div>
                </div>
            </div>
        @endunless

        @if($isPhysicalAnalytics)
            <div class="analytics-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                    <h3 style="margin:0;">Pending Orders</h3>
                    <button type="button" id="togglePendingOrdersBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="pendingOrdersContent">Show</button>
                </div>
                <div id="pendingOrdersContent" style="display:none;">
                    <div class="analytics-table-wrap">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Order Items</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Delivery Address</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($physicalPendingOrders as $row)
                                    <tr>
                                        <td><strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong><br><span style="color:var(--theme-muted, #6B7280);font-size:12px;">{{ $row['email'] ?? 'N/A' }}</span></td>
                                        <td>{{ $row['phone'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(!empty($row['order_items']) && is_array($row['order_items']))
                                                @foreach($row['order_items'] as $item)
                                                    <div><strong>{{ $item['name'] ?? 'Product' }}</strong> x{{ max(1, (int) ($item['quantity'] ?? 1)) }}</div>
                                                @endforeach
                                            @else
                                                {{ $row['order_items_label'] ?? ($row['selected_offer'] ?? 'N/A') }}
                                            @endif
                                        </td>
                                        <td>{{ (int) ($row['order_quantity'] ?? 0) > 0 ? (int) $row['order_quantity'] : 'N/A' }}</td>
                                        <td>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</td>
                                        <td>{{ $row['delivery_address'] ?? 'N/A' }}</td>
                                        <td>{{ $row['last_activity'] ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">No pending physical-product orders right now.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="analytics-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                    <h3 style="margin:0;">Paid Orders</h3>
                    <button type="button" id="togglePaidOrdersBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="paidOrdersContent">Show</button>
                </div>
                <div id="paidOrdersContent" style="display:none;">
                    <div class="analytics-table-wrap">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Paid Order</th>
                                    <th>Amount</th>
                                    <th>Delivery Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($physicalPaidOrders as $row)
                                    <tr>
                                        <td>
                                            <strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong><br>
                                            <span style="color:var(--theme-muted, #6B7280);font-size:12px;">{{ $row['email'] ?? 'N/A' }}</span><br>
                                            <span style="color:var(--theme-muted, #6B7280);font-size:12px;">{{ $row['phone'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($row['order_items']) && is_array($row['order_items']))
                                                @foreach($row['order_items'] as $item)
                                                    <div><strong>{{ $item['name'] ?? 'Product' }}</strong> x{{ max(1, (int) ($item['quantity'] ?? 1)) }}</div>
                                                @endforeach
                                            @else
                                                {{ $row['order_items_label'] ?? ($row['selected_offer'] ?? 'N/A') }}
                                            @endif
                                            <div style="margin-top:8px;font-size:12px;color:var(--theme-muted, #6B7280);">
                                                Qty: {{ (int) ($row['order_quantity'] ?? 0) > 0 ? (int) $row['order_quantity'] : 'N/A' }}
                                            </div>
                                            <div style="margin-top:6px;font-size:12px;color:var(--theme-muted, #6B7280);">
                                                {{ $row['delivery_address'] ?? 'No delivery address recorded' }}
                                            </div>
                                        </td>
                                        <td>
                                            <strong>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</strong><br>
                                            <span class="analytics-pill" style="margin-top:8px;">{{ ucwords(str_replace('_', ' ', (string) ($row['delivery_status'] ?? 'processing'))) }}</span>
                                            @if(!empty($row['delivery_updated_label']))
                                                <div style="margin-top:8px;font-size:12px;color:var(--theme-muted, #6B7280);">Last email: {{ $row['delivery_updated_label'] }}</div>
                                            @endif
                                            @if(!empty($row['tracking_url']))
                                                <div style="margin-top:6px;"><a class="analytics-link" href="{{ $row['tracking_url'] }}" target="_blank" rel="noopener">Open tracking link</a></div>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($row['email']))
                                                <form method="POST" action="{{ route('funnels.analytics.delivery-update', $funnel) }}" class="analytics-inline-form">
                                                    @csrf
                                                    <input type="hidden" name="order_key" value="{{ $row['order_key'] ?? '' }}">
                                                    <input type="hidden" name="recipient_email" value="{{ $row['email'] ?? '' }}">
                                                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Delivery status</label>
                                                    <select name="delivery_status">
                                                        @foreach($deliveryStatusOptions as $value => $label)
                                                            <option value="{{ $value }}" {{ ($row['delivery_status'] ?? 'processing') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Courier</label>
                                                    <input type="text" name="courier_name" value="{{ $row['courier_name'] ?? 'LBC' }}" placeholder="LBC">
                                                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Tracking link</label>
                                                    <input type="url" name="tracking_url" value="{{ $row['tracking_url'] ?? '' }}" placeholder="https://www.lbcexpress.com/...">
                                                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Extra message</label>
                                                    <textarea name="custom_message" placeholder="Optional note for the customer">{{ $row['delivery_message'] ?? '' }}</textarea>
                                                    <div class="analytics-inline-form-actions">
                                                        <button type="submit" class="analytics-btn primary">Send Email Update</button>
                                                    </div>
                                                </form>
                                            @else
                                                <span style="color:var(--theme-muted, #6B7280);">No customer email available for this order.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No paid physical-product orders yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="analytics-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                <h3 style="margin:0;">{{ $summarySectionTitle }}</h3>
                <button type="button" id="toggleOfferActivityBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="offerActivityContent">Show</button>
            </div>
            <div id="offerActivityContent" style="display:none;">
                @unless($isPhysicalAnalytics)
                    <div class="analytics-section-filters">
                        <div class="analytics-field">
                            <label for="offerActivityUpsellFilter">Upsell</label>
                            <select id="offerActivityUpsellFilter">
                                <option value="">All upsell statuses</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Declined">Declined</option>
                                <option value="Did not avail">Did not avail</option>
                            </select>
                        </div>
                        <div class="analytics-field">
                            <label for="offerActivityDownsellFilter">Downsell</label>
                            <select id="offerActivityDownsellFilter">
                                <option value="">All downsell statuses</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Declined">Declined</option>
                                <option value="Did not avail">Did not avail</option>
                            </select>
                        </div>
                        <button type="button" id="clearOfferActivityFiltersBtn" class="analytics-btn">Clear</button>
                    </div>
                @endunless
                <div class="analytics-table-wrap">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                @if($isPhysicalAnalytics)
                                    <th>Phone</th>
                                    <th>Order Items</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Checkout Paid</th>
                                    <th>Delivery Address</th>
                                    <th>Order Notes</th>
                                @else
                                    <th>{{ $selectedOfferLabel }}</th>
                                    <th>Checkout Paid</th>
                                    <th>Upsell</th>
                                    <th>Downsell</th>
                                @endif
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody id="offerActivityTableBody">
                            @forelse($summaryRows as $row)
                                @if($isPhysicalAnalytics)
                                    <tr>
                                        <td><strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong></td>
                                        <td>{{ $row['email'] ?? 'N/A' }}</td>
                                        <td>{{ $row['phone'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(!empty($row['order_items']) && is_array($row['order_items']))
                                                @foreach($row['order_items'] as $item)
                                                    <div>
                                                        <strong>{{ $item['name'] ?? 'Product' }}</strong> x{{ max(1, (int) ($item['quantity'] ?? 1)) }}
                                                    </div>
                                                    @if(!empty($item['badge']) || !empty($item['price']))
                                                        <div style="font-size:12px;color:var(--theme-muted, #6B7280);margin-bottom:4px;">
                                                            {{ trim(implode(' • ', array_filter([
                                                                $item['badge'] ?? null,
                                                                $item['price'] ?? null,
                                                            ]))) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                {{ $row['order_items_label'] ?? ($row['selected_offer'] ?? 'N/A') }}
                                            @endif
                                        </td>
                                        <td>{{ (int) ($row['order_quantity'] ?? 0) > 0 ? (int) $row['order_quantity'] : 'N/A' }}</td>
                                        <td><span class="analytics-pill">{{ strtoupper(str_replace('_', ' ', (string) ($row['order_status'] ?? 'pending'))) }}</span></td>
                                        <td>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</td>
                                        <td>{{ $row['delivery_address'] ?? 'N/A' }}</td>
                                        <td>{{ $row['notes'] ?? 'N/A' }}</td>
                                        <td>{{ $row['last_activity'] ?? 'N/A' }}</td>
                                    </tr>
                                @else
                                    @php
                                        $upsellStatus = (string) ($row['upsell_status'] ?? 'Did not avail');
                                        $downsellStatus = (string) ($row['downsell_status'] ?? 'Did not avail');
                                        $upsellFilterValue = str_starts_with($upsellStatus, 'Accepted') ? 'Accepted' : $upsellStatus;
                                        $downsellFilterValue = str_starts_with($downsellStatus, 'Accepted') ? 'Accepted' : $downsellStatus;
                                    @endphp
                                    <tr data-upsell-status="{{ $upsellFilterValue }}" data-downsell-status="{{ $downsellFilterValue }}">
                                        <td><strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong></td>
                                        <td>{{ $row['email'] ?? 'N/A' }}</td>
                                        <td>{{ $row['selected_offer'] ?? 'N/A' }}</td>
                                        <td>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</td>
                                        <td>{{ $upsellStatus }}</td>
                                        <td>{{ $downsellStatus }}</td>
                                        <td>{{ $row['last_activity'] ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr id="offerActivityEmptyRow">
                                    <td colspan="{{ $isPhysicalAnalytics ? 10 : 7 }}">
                                        @if($isPhysicalAnalytics)
                                            No physical-product orders have been recorded for the current filters.
                                        @else
                                            No upsell or downsell activity has been recorded for the current filters.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                            @if(!$isPhysicalAnalytics && $offerCustomerSummary->isNotEmpty())
                                <tr id="offerActivityNoMatchRow" style="display:none;">
                                    <td colspan="7">No rows match the selected offer filters.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @unless($isPhysicalAnalytics)
            <div class="analytics-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                    <h3 style="margin:0;">Step Performance</h3>
                    <button type="button" id="toggleStepPerformanceBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="stepPerformanceContent">Show</button>
                </div>
                <div id="stepPerformanceContent" style="display:none;">
                    <div class="analytics-table-wrap">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Step</th>
                                    <th>Type</th>
                                    <th>Visits</th>
                                    <th>Drop-off</th>
                                    <th>Drop-off Rate</th>
                                    <th>Event Mix</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stepVisits as $row)
                                    @php
                                        $rowDropOff = $dropOff->firstWhere('step_id', $row['step_id']);
                                        $rowEvents = $eventBreakdown->firstWhere('step_id', $row['step_id']);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $row['step_title'] }}</strong><br>
                                            <span style="color:var(--theme-muted, #6B7280); font-size:13px;">/{{ $row['step_slug'] }}</span>
                                        </td>
                                        <td><span class="analytics-pill">{{ ucwords(str_replace('_', ' ', $row['step_type'])) }}</span></td>
                                        <td>{{ number_format((int) $row['visits']) }}</td>
                                        <td>{{ number_format((int) ($rowDropOff['drop_off'] ?? 0)) }}</td>
                                        <td>{{ number_format((float) ($rowDropOff['drop_off_rate'] ?? 0), 2) }}%</td>
                                        <td>
                                            @if(!empty($rowEvents['events']))
                                                @foreach($rowEvents['events'] as $eventName => $count)
                                                    <div style="margin-bottom:4px;">{{ $eventName }}: {{ $count }}</div>
                                                @endforeach
                                            @else
                                                <span style="color:var(--theme-muted, #6B7280);">No tracked events yet</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">No step analytics yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endunless

        <div class="analytics-card">
            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;">
                <h3 style="margin:0;">Recent Funnel Events</h3>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <a href="{{ route('funnels.events', $funnel) }}" class="analytics-btn">Open Raw Events JSON</a>
                    <button type="button" id="toggleRecentEventsBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="recentEventsContent">Show</button>
                </div>
            </div>

            <div id="recentEventsContent" style="display:none;">
                @if($events->count() > 0)
                    <div class="analytics-events">
                        @foreach($events as $event)
                            <div class="analytics-event">
                                <div class="analytics-event-head">
                                    <span class="analytics-pill">{{ $event->event_name }}</span>
                                    <strong>{{ optional($event->occurred_at)->format('M j, Y g:i A') }}</strong>
                                </div>
                                <div class="analytics-event-meta">
                                    Step: {{ $event->step->title ?? 'N/A' }}<br>
                                    Step Type: {{ ucwords(str_replace('_', ' ', data_get($event->meta, 'step_type', $event->step->type ?? 'n/a'))) }}<br>
                                    Step Slug: {{ data_get($event->meta, 'step_slug', $event->step->slug ?? 'N/A') }}<br>
                                    Session: {{ $event->session_identifier ?? 'N/A' }}<br>
                                    Lead: {{ $event->lead->email ?? ($event->lead->name ?? 'N/A') }}<br>
                                    Payment: {{ $event->payment ? ('PHP ' . number_format((float) $event->payment->amount, 2) . ' / ' . $event->payment->status) : 'N/A' }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div style="margin-top:16px;">
                        {{ $events->links('pagination::bootstrap-4') }}
                    </div>
                @else
                    <div class="analytics-empty">No funnel events have been recorded yet for the selected date range.</div>
                @endif
            </div>
        </div>
    </div>

    <div id="offerActivityModal" class="analytics-modal" hidden aria-hidden="true">
        <div class="analytics-modal-backdrop" data-offer-modal-close></div>
        <div class="analytics-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="offerActivityModalTitle">
            <div class="analytics-modal-head">
                <div>
                    <h3 id="offerActivityModalTitle">Offer Activity</h3>
                    <p id="offerActivityModalDescription">Customers tied to this offer action.</p>
                </div>
                <button type="button" class="analytics-modal-close" data-offer-modal-close aria-label="Close offer activity modal">&times;</button>
            </div>
            <div class="analytics-modal-body">
                <div class="analytics-table-wrap">
                    <table class="analytics-table" style="min-width: 720px;">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Selected Offer</th>
                                <th>Step</th>
                                <th>Paid Before Offer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="offerActivityModalRows">
                            <tr>
                                <td colspan="8">Select an offer count card to view matching customers.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const stepLabels = @json($stepLabels);
        const stepValues = @json($stepValues);
        const stepDropOff = @json($stepDropOffValues);
        const offerRateLabels = ['Upsell Acceptance', 'Downsell Acceptance', 'Abandoned Checkout'];
        const offerRateValues = @json($offerRateValues);
        const dailyLabels = @json($dailyLabels);
        const dailyVisitValues = @json($dailyVisitValues);
        const dailyOptInValues = @json($dailyOptInValues);
        const dailyCheckoutValues = @json($dailyCheckoutValues);
        const dailyPaidValues = @json($dailyPaidValues);
        const conversionLabels = @json($conversionLabels);
        const conversionValues = @json($conversionValues);
        const offerActivityGroups = @json($offerActivityGroups);
        const toggleOfferActivityBtn = document.getElementById('toggleOfferActivityBtn');
        const offerActivityContent = document.getElementById('offerActivityContent');
        const togglePendingOrdersBtn = document.getElementById('togglePendingOrdersBtn');
        const pendingOrdersContent = document.getElementById('pendingOrdersContent');
        const togglePaidOrdersBtn = document.getElementById('togglePaidOrdersBtn');
        const paidOrdersContent = document.getElementById('paidOrdersContent');
        const offerActivityUpsellFilter = document.getElementById('offerActivityUpsellFilter');
        const offerActivityDownsellFilter = document.getElementById('offerActivityDownsellFilter');
        const clearOfferActivityFiltersBtn = document.getElementById('clearOfferActivityFiltersBtn');
        const offerActivityTableBody = document.getElementById('offerActivityTableBody');
        const offerActivityTableRows = offerActivityTableBody ? Array.from(offerActivityTableBody.querySelectorAll('tr[data-upsell-status]')) : [];
        const offerActivityNoMatchRow = document.getElementById('offerActivityNoMatchRow');
        const toggleStepPerformanceBtn = document.getElementById('toggleStepPerformanceBtn');
        const stepPerformanceContent = document.getElementById('stepPerformanceContent');
        const toggleRecentEventsBtn = document.getElementById('toggleRecentEventsBtn');
        const recentEventsContent = document.getElementById('recentEventsContent');
        const offerActivityModal = document.getElementById('offerActivityModal');
        const offerActivityModalTitle = document.getElementById('offerActivityModalTitle');
        const offerActivityModalDescription = document.getElementById('offerActivityModalDescription');
        const offerActivityModalRows = document.getElementById('offerActivityModalRows');
        const offerActivityButtons = document.querySelectorAll('[data-offer-activity]');
        let lastOfferActivityTrigger = null;

        function bindCollapsibleSection(button, content) {
            if (!button || !content) {
                return;
            }

            button.addEventListener('click', function() {
                const isHidden = content.style.display === 'none';
                content.style.display = isHidden ? 'block' : 'none';
                button.textContent = isHidden ? 'Hide' : 'Show';
                button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            });
        }

        bindCollapsibleSection(toggleOfferActivityBtn, offerActivityContent);
        bindCollapsibleSection(togglePendingOrdersBtn, pendingOrdersContent);
        bindCollapsibleSection(togglePaidOrdersBtn, paidOrdersContent);
        bindCollapsibleSection(toggleStepPerformanceBtn, stepPerformanceContent);
        bindCollapsibleSection(toggleRecentEventsBtn, recentEventsContent);

        function applyOfferActivityFilters() {
            if (!offerActivityTableRows.length) {
                return;
            }

            const upsellValue = String(offerActivityUpsellFilter?.value || '').trim().toLowerCase();
            const downsellValue = String(offerActivityDownsellFilter?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            offerActivityTableRows.forEach((row) => {
                const rowUpsell = String(row.getAttribute('data-upsell-status') || '').trim().toLowerCase();
                const rowDownsell = String(row.getAttribute('data-downsell-status') || '').trim().toLowerCase();
                const visible = (upsellValue === '' || rowUpsell === upsellValue)
                    && (downsellValue === '' || rowDownsell === downsellValue);

                row.classList.toggle('analytics-table-row-hidden', !visible);
                if (visible) {
                    visibleCount += 1;
                }
            });

            if (offerActivityNoMatchRow) {
                offerActivityNoMatchRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        if (offerActivityUpsellFilter) {
            offerActivityUpsellFilter.addEventListener('change', applyOfferActivityFilters);
        }

        if (offerActivityDownsellFilter) {
            offerActivityDownsellFilter.addEventListener('change', applyOfferActivityFilters);
        }

        if (clearOfferActivityFiltersBtn) {
            clearOfferActivityFiltersBtn.addEventListener('click', function() {
                if (offerActivityUpsellFilter) {
                    offerActivityUpsellFilter.value = '';
                }
                if (offerActivityDownsellFilter) {
                    offerActivityDownsellFilter.value = '';
                }
                applyOfferActivityFilters();
            });
        }

        applyOfferActivityFilters();

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderOfferActivityRows(rows) {
            if (!offerActivityModalRows) {
                return;
            }

            if (!rows.length) {
                offerActivityModalRows.innerHTML = '<tr><td colspan="8">No customers matched this offer action for the current filters.</td></tr>';
                return;
            }

            offerActivityModalRows.innerHTML = rows.map((row) => {
                const selectedOffer = escapeHtml(row.selected_offer || 'N/A');
                const paidBeforeOffer = Number(row.paid_before_offer || 0).toFixed(2);
                const amount = Number(row.amount || 0).toFixed(2);
                const customer = escapeHtml(row.lead_name || row.lead_label || 'Anonymous visitor');
                const email = escapeHtml(row.lead_email || 'N/A');
                const step = escapeHtml(row.step_title || 'N/A');
                const payment = escapeHtml(row.payment_status || 'N/A');
                const occurredAt = escapeHtml(row.occurred_at_label || 'N/A');

                return `
                    <tr>
                        <td><strong>${customer}</strong></td>
                        <td>${email}</td>
                        <td>${selectedOffer}</td>
                        <td>${step}</td>
                        <td>PHP ${paidBeforeOffer}</td>
                        <td>PHP ${amount}</td>
                        <td>${payment}</td>
                        <td>${occurredAt}</td>
                    </tr>
                `;
            }).join('');
        }

        function closeOfferActivityModal() {
            if (!offerActivityModal) {
                return;
            }

            offerActivityModal.hidden = true;
            offerActivityModal.setAttribute('aria-hidden', 'true');

            if (lastOfferActivityTrigger) {
                lastOfferActivityTrigger.focus();
            }
        }

        function openOfferActivityModal(groupKey, trigger) {
            const group = offerActivityGroups[groupKey];
            if (!group || !offerActivityModal || !offerActivityModalTitle || !offerActivityModalDescription) {
                return;
            }

            lastOfferActivityTrigger = trigger || null;
            offerActivityModalTitle.textContent = group.title;
            offerActivityModalDescription.textContent = group.description;
            renderOfferActivityRows(Array.isArray(group.rows) ? group.rows : []);
            offerActivityModal.hidden = false;
            offerActivityModal.setAttribute('aria-hidden', 'false');
        }

        offerActivityButtons.forEach((button) => {
            button.addEventListener('click', function() {
                openOfferActivityModal(button.getAttribute('data-offer-activity'), button);
            });
        });

        document.querySelectorAll('[data-offer-modal-close]').forEach((element) => {
            element.addEventListener('click', closeOfferActivityModal);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && offerActivityModal && !offerActivityModal.hidden) {
                closeOfferActivityModal();
            }
        });

        const stepVisitsCanvas = document.getElementById('stepVisitsChart');
        if (stepVisitsCanvas) {
            new Chart(stepVisitsCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: stepLabels,
                    datasets: [
                        {
                            label: 'Visits',
                            data: stepValues,
                            backgroundColor: '#240E35',
                            borderRadius: 10,
                        },
                        {
                            label: 'Drop-off',
                            data: stepDropOff,
                            backgroundColor: '#C084FC',
                            borderRadius: 10,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        const offerRatesCanvas = document.getElementById('offerRatesChart');
        if (offerRatesCanvas) {
            new Chart(offerRatesCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: offerRateLabels,
                    datasets: [{
                        data: offerRateValues,
                        backgroundColor: ['#240E35', '#6B4A7A', '#F97316'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'start',
                            labels: {
                                boxWidth: 22,
                                boxHeight: 12,
                                padding: 14,
                                color: '#6B7280',
                                font: {
                                    size: 13
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = Number(context.raw || 0);
                                    return context.label + ': ' + value.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        const dailyTrendCanvas = document.getElementById('dailyTrendChart');
        if (dailyTrendCanvas) {
            new Chart(dailyTrendCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        { label: 'Visits', data: dailyVisitValues, borderColor: '#240E35', backgroundColor: 'rgba(36,14,53,0.10)', tension: 0.35, fill: false },
                        { label: 'Opt-ins', data: dailyOptInValues, borderColor: '#0F766E', backgroundColor: 'rgba(15,118,110,0.10)', tension: 0.35, fill: false },
                        { label: 'Checkout Starts', data: dailyCheckoutValues, borderColor: '#F97316', backgroundColor: 'rgba(249,115,22,0.10)', tension: 0.35, fill: false },
                        { label: 'Paid', data: dailyPaidValues, borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,0.10)', tension: 0.35, fill: false },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        const conversionPathCanvas = document.getElementById('conversionPathChart');
        if (conversionPathCanvas) {
            new Chart(conversionPathCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: conversionLabels,
                    datasets: [{
                        label: 'Count',
                        data: conversionValues,
                        backgroundColor: ['#240E35', '#6B4A7A', '#0F766E', '#F97316'],
                        borderRadius: 10,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    </script>
@endsection
