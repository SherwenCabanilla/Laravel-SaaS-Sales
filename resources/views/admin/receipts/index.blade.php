@extends('layouts.admin')

@section('title', 'Receipt Oversight')

@php
    $emptyDash = $emptyDash ?? '—';
@endphp

@section('content')
    <div class="top-header">
        <h1>Receipt Oversight</h1>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.receipts.index') }}" class="app-form-grid app-form-grid--2" style="gap:12px;">
            <div>
                <label for="tenant_id" style="display:block;margin-bottom:6px;font-weight:700;">Tenant</label>
                <select id="tenant_id" name="tenant_id" style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
                    <option value="">All tenants</option>
                    @foreach($tenantOptions as $tenant)
                        <option value="{{ $tenant->id }}" {{ $tenantId === (int) $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="app-actions-row">
                <button type="submit" style="padding:10px 16px;border:none;border-radius:8px;background:var(--theme-primary, #240E35);color:#fff;font-weight:700;cursor:pointer;">
                    Apply Filter
                </button>
                <a href="{{ route('admin.receipts.index') }}" style="display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border-radius:8px;background:#F3F4F6;color:#111827;text-decoration:none;font-weight:700;">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="payment-summary-grid" style="margin-bottom: 20px;">
        <div class="card payment-summary-card">
            <h3>Pending</h3>
            <p class="payment-summary-card__value">{{ number_format((int) ($receiptStats['pending'] ?? 0)) }}</p>
        </div>
        <div class="card payment-summary-card">
            <h3>Auto Approved</h3>
            <p class="payment-summary-card__value">{{ number_format((int) ($receiptStats['auto_approved'] ?? 0)) }}</p>
        </div>
        <div class="card payment-summary-card">
            <h3>Approved</h3>
            <p class="payment-summary-card__value">{{ number_format((int) ($receiptStats['approved'] ?? 0)) }}</p>
        </div>
        <div class="card payment-summary-card">
            <h3>Rejected</h3>
            <p class="payment-summary-card__value">{{ number_format((int) ($receiptStats['rejected'] ?? 0)) }}</p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Upload Receipt</h3>
        <p style="margin: 0 0 14px; color: var(--theme-muted, #6B7280);">
            Super Admin can upload a receipt for any tenant payment while keeping the linked tenant as the accounting owner.
        </p>
        <form action="{{ route('admin.receipts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="app-form-grid app-form-grid--3" style="gap:12px;">
                <div class="app-form-span-full">
                    <label for="receipt_payment_id" style="display:block;margin-bottom:6px;">Payment</label>
                    <select id="receipt_payment_id" name="payment_id" required style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                        <option value="">Select payment</option>
                        @foreach($receiptOptions as $option)
                            <option value="{{ $option->id }}">
                                {{ $option->tenant->company_name ?? 'Unknown Tenant' }} | #{{ $option->id }} | {{ ucfirst(str_replace('_', ' ', $option->payment_type)) }} | PHP {{ number_format((float) $option->amount, 2) }} | {{ optional($option->payment_date)->format('Y-m-d') ?? $emptyDash }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="receipt_amount" style="display:block;margin-bottom:6px;">Receipt Amount</label>
                    <input type="number" step="0.01" min="0.01" name="receipt_amount" id="receipt_amount"
                        value="{{ old('receipt_amount') }}"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                </div>
                <div>
                    <label for="receipt_date" style="display:block;margin-bottom:6px;">Receipt Date</label>
                    <input type="date" name="receipt_date" id="receipt_date"
                        value="{{ old('receipt_date', now()->toDateString()) }}"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                </div>
                <div>
                    <label for="receipt_provider" style="display:block;margin-bottom:6px;">Provider</label>
                    <input type="text" name="provider" id="receipt_provider" value="{{ old('provider') }}"
                        placeholder="e.g. paymongo / gcash"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                </div>
                <div>
                    <label for="reference_number" style="display:block;margin-bottom:6px;">Reference Number</label>
                    <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number') }}"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                </div>
                <div>
                    <label for="receipt_file" style="display:block;margin-bottom:6px;">Receipt File</label>
                    <input type="file" name="receipt_file" id="receipt_file" required
                        accept=".jpg,.jpeg,.png,.pdf"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;background:#fff;">
                </div>
                <div class="app-form-span-full">
                    <label for="receipt_notes" style="display:block;margin-bottom:6px;">Notes</label>
                    <textarea name="notes" id="receipt_notes" rows="3"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div style="margin-top: 14px;">
                <button type="submit"
                    style="padding: 10px 18px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight:700;">
                    Upload Receipt
                </button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Receipt Review</h3>
        <div class="team-table-scroll">
            <table class="sa-table team-table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Receipt</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Automation</th>
                        <th>File</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr>
                            <td>{{ $receipt->tenant->company_name ?? $emptyDash }}</td>
                            <td>#{{ $receipt->id }}</td>
                            <td>#{{ $receipt->payment_id }} / {{ ucfirst(str_replace('_', ' ', $receipt->payment->payment_type ?? 'payment')) }}</td>
                            <td>PHP {{ number_format((float) ($receipt->receipt_amount ?? 0), 2) }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $receipt->status)) }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $receipt->automation_status)) }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $receipt->receipt_path) }}" target="_blank" rel="noopener">View</a>
                            </td>
                            <td>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <form action="{{ route('admin.receipts.review', $receipt) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" style="padding:8px 10px;border:none;border-radius:8px;background:#166534;color:#fff;cursor:pointer;font-weight:700;">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.receipts.review', $receipt) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" style="padding:8px 10px;border:none;border-radius:8px;background:#B91C1C;color:#fff;cursor:pointer;font-weight:700;">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No receipts uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 14px;">
            {{ $receipts->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <div class="card">
        <h3>Recent Finance Audit Events</h3>
        <div class="team-table-scroll">
            <table class="sa-table team-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Tenant</th>
                        <th>Actor</th>
                        <th>Event</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAuditLogs as $log)
                        <tr>
                            <td>{{ optional($log->occurred_at)->format('Y-m-d H:i') ?? $emptyDash }}</td>
                            <td>{{ $log->tenant->company_name ?? $emptyDash }}</td>
                            <td>{{ $log->actor->name ?? $emptyDash }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $log->event_type)) }}</td>
                            <td>{{ $log->message ?? $emptyDash }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No finance audit events yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
