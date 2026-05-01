@extends('layouts.admin')

@section('title', 'Platform Finance Admin')

@section('content')
    <div class="top-header">
        <h1>Platform Finance Admin</h1>
        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:999px;background:#E0F2FE;color:#075985;font-weight:800;">
            <i class="fas fa-scale-balanced"></i>
            <span>Payout Review Queue</span>
        </div>
    </div>

    <div class="admin-kpi-board">
        <section class="admin-kpi-group" aria-label="Platform Payout Overview">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Platform Payout Overview</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--3">
                <article class="admin-kpi-card admin-kpi-card--warning">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Pending Review</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format((int) ($statusCounts['pending'] ?? 0)) }}</div>
                    <div class="admin-kpi-card__meta">Saved payout destinations waiting for platform review</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--success">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Approved</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format((int) ($statusCounts['approved'] ?? 0)) }}</div>
                    <div class="admin-kpi-card__meta">Tenant payout destinations cleared for payout operations</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--danger">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Rejected</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-circle-xmark" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format((int) ($statusCounts['rejected'] ?? 0)) }}</div>
                    <div class="admin-kpi-card__meta">Tenant payout destinations that need a corrected resubmission</div>
                </article>
            </div>
        </section>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div>
                <h3 style="margin:0;">Review Filters</h3>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $filterKey => $filterLabel)
                    <a href="{{ route('platform.payouts.index', ['status' => $filterKey]) }}"
                        style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:999px;text-decoration:none;font-weight:700;border:1px solid var(--theme-border, #E6E1EF);{{ $statusFilter === $filterKey ? 'background:var(--theme-primary, #240E35);color:#fff;border-color:var(--theme-primary, #240E35);' : 'background:#fff;color:var(--theme-muted, #6B7280);' }}">
                        {{ $filterLabel }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="app-stack">
        @forelse($payoutAccounts as $payoutAccount)
            @php
                $tenant = $payoutAccount->tenant;
                $owner = $ownerMap->get($payoutAccount->tenant_id);
                $companyName = $tenant?->company_name ?? 'Unknown Tenant';
                $companyInitials = collect(preg_split('/\s+/', trim($companyName)))
                    ->filter()
                    ->take(2)
                    ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                    ->implode('');
                $companyInitials = $companyInitials !== '' ? $companyInitials : 'TT';
                $statusTone = $payoutAccount->isApproved()
                    ? ['bg' => '#DCFCE7', 'text' => '#166534']
                    : ($payoutAccount->isRejected()
                        ? ['bg' => '#FEE2E2', 'text' => '#B91C1C']
                        : ['bg' => '#FFEDD5', 'text' => '#C2410C']);
            @endphp
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                    <div style="display:flex;gap:14px;align-items:flex-start;">
                        <div style="width:54px;height:54px;border-radius:16px;background:#0F172A;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;overflow:hidden;">
                            @if($tenant?->logo_path)
                                <img src="{{ asset('storage/' . $tenant->logo_path) }}" alt="Tenant Logo" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                {{ $companyInitials }}
                            @endif
                        </div>
                        <div>
                            <h3 style="margin:0 0 6px;">{{ $companyName }}</h3>
                            <div style="display:flex;flex-wrap:wrap;gap:8px 10px;">
                                <div style="display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:#F8FAFC;border:1px solid #E2E8F0;">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;padding:4px 8px;border-radius:999px;background:#0F172A;color:#fff;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;">Owner</span>
                                    <span style="font-size:13px;font-weight:800;line-height:1.2;color:#334155;">{{ $owner?->name ?? 'Not found' }}</span>
                                </div>
                                @if($owner?->email)
                                    <div style="display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:#F8FAFC;border:1px solid #E2E8F0;">
                                        <span style="display:inline-flex;align-items:center;justify-content:center;padding:4px 8px;border-radius:999px;background:#334155;color:#fff;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;">Email</span>
                                        <span style="font-size:13px;font-weight:800;line-height:1.2;color:#475569;">{{ $owner->email }}</span>
                                    </div>
                                @endif
                                <div style="display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:#F8FAFC;border:1px solid #E2E8F0;">
                                    <span style="display:inline-flex;align-items:center;justify-content:center;padding:4px 8px;border-radius:999px;background:#475569;color:#fff;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;">Submitted</span>
                                    <span style="font-size:13px;font-weight:800;line-height:1.2;color:#0F172A;">{{ optional($payoutAccount->updated_at)->format('Y-m-d H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding:10px 14px;border-radius:999px;background:{{ $statusTone['bg'] }};color:{{ $statusTone['text'] }};font-weight:800;">
                        {{ $payoutAccount->reviewStatusLabel() }}
                    </div>
                </div>

                <div class="app-grid app-grid--4" style="gap:12px;margin-top:18px;">
                    <div style="padding:14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:14px;background:#fff;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);margin-bottom:6px;">Destination type</div>
                        <div style="font-size:15px;font-weight:800;color:#0F172A;">{{ ucwords(str_replace('_', ' ', $payoutAccount->destination_type ?? 'gcash')) }}</div>
                    </div>
                    <div style="padding:14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:14px;background:#fff;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);margin-bottom:6px;">Account name</div>
                        <div style="font-size:15px;font-weight:800;color:#0F172A;">{{ $payoutAccount->account_name ?: '-' }}</div>
                    </div>
                    <div style="padding:14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:14px;background:#fff;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);margin-bottom:6px;">Masked destination</div>
                        <div style="font-size:15px;font-weight:800;color:#0F172A;">{{ $payoutAccount->masked_destination ?: '-' }}</div>
                    </div>
                    <div style="padding:14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:14px;background:#fff;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);margin-bottom:6px;">Provider reference</div>
                        <div style="font-size:15px;font-weight:800;color:#0F172A;">{{ $payoutAccount->provider_destination_reference ?: '-' }}</div>
                    </div>
                </div>

                <div style="margin-top:14px;padding:14px;border-radius:14px;background:#F8FAFC;border:1px solid var(--theme-border, #E6E1EF);">
                    <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);margin-bottom:6px;">Current notes</div>
                    <div style="font-size:14px;font-weight:600;color:#334155;line-height:1.6;">
                        {{ data_get($payoutAccount, 'meta.notes', $payoutAccount->review_notes ?: '-') }}
                    </div>
                </div>

                @if($payoutAccount->reviewer || $payoutAccount->review_notes)
                    <div style="margin-top:12px;color:var(--theme-muted, #6B7280);font-size:13px;font-weight:600;line-height:1.6;">
                        Last review:
                        {{ $payoutAccount->reviewer?->name ?? 'Not yet reviewed' }}
                        @if($payoutAccount->reviewed_at)
                            on {{ $payoutAccount->reviewed_at->format('Y-m-d H:i') }}
                        @endif
                        @if($payoutAccount->review_notes)
                            . Notes: {{ $payoutAccount->review_notes }}
                        @endif
                    </div>
                @endif

                <div class="app-form-grid app-form-grid--2" style="gap:14px;margin-top:18px;">
                    <form method="POST" action="{{ route('platform.payouts.review', $payoutAccount) }}" style="padding:16px;border:1px solid #D1FAE5;border-radius:16px;background:#F0FDF4;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="decision" value="approve">
                        <label for="approve_notes_{{ $payoutAccount->id }}" style="display:block;margin-bottom:8px;font-weight:800;color:#14532D;">Approval notes</label>
                        <textarea id="approve_notes_{{ $payoutAccount->id }}" name="review_notes" rows="3"
                            style="width:100%;padding:12px;border:1px solid #BBF7D0;border-radius:10px;background:#fff;">{{ $payoutAccount->isApproved() ? ($payoutAccount->review_notes ?? '') : '' }}</textarea>
                        <button type="submit"
                            style="margin-top:12px;padding:12px 16px;border:none;border-radius:10px;background:#166534;color:#fff;font-weight:700;cursor:pointer;">
                            Approve Payout Account
                        </button>
                    </form>

                    <form method="POST" action="{{ route('platform.payouts.review', $payoutAccount) }}" style="padding:16px;border:1px solid #FECACA;border-radius:16px;background:#FEF2F2;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="decision" value="reject">
                        <label for="reject_notes_{{ $payoutAccount->id }}" style="display:block;margin-bottom:8px;font-weight:800;color:#991B1B;">Rejection notes</label>
                        <textarea id="reject_notes_{{ $payoutAccount->id }}" name="review_notes" rows="3"
                            placeholder="Explain what the account owner needs to update before resubmitting."
                            style="width:100%;padding:12px;border:1px solid #FCA5A5;border-radius:10px;background:#fff;">{{ $payoutAccount->isRejected() ? ($payoutAccount->review_notes ?? '') : '' }}</textarea>
                        <button type="submit"
                            style="margin-top:12px;padding:12px 16px;border:none;border-radius:10px;background:#B91C1C;color:#fff;font-weight:700;cursor:pointer;">
                            Reject Payout Account
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card">
                <h3 style="margin-top:0;">No payout accounts in this queue.</h3>
                <p style="margin:0;color:var(--theme-muted, #6B7280);font-size:13px;font-weight:800;">
                    There are no payout accounts matching the current filter.
                </p>
            </div>
        @endforelse
    </div>

    <div style="margin-top:16px;">
        {{ $payoutAccounts->links('pagination::bootstrap-4') }}
    </div>
@endsection
