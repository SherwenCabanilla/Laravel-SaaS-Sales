@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="top-header">
        <h1>Notifications</h1>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="app-grid app-grid--3" style="gap:12px;">
            <div style="padding:16px;border:1px solid var(--theme-border, #E6E1EF);border-radius:14px;background:var(--theme-surface, #FFFFFF);">
                <div style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);text-transform:uppercase;letter-spacing:.06em;">Total</div>
                <div style="margin-top:8px;font-size:28px;font-weight:800;color:#0F172A;">{{ number_format((int) data_get($summary, 'total', 0)) }}</div>
            </div>
            <div style="padding:16px;border:1px solid rgba(217, 119, 6, 0.25);border-radius:14px;background:rgba(245, 158, 11, 0.08);">
                <div style="font-size:12px;font-weight:800;color:#92400E;text-transform:uppercase;letter-spacing:.06em;">Unread</div>
                <div style="margin-top:8px;font-size:28px;font-weight:800;color:#92400E;">{{ number_format((int) data_get($summary, 'unread', 0)) }}</div>
            </div>
            <div style="padding:16px;border:1px solid rgba(22, 163, 74, 0.22);border-radius:14px;background:rgba(22, 163, 74, 0.08);">
                <div style="font-size:12px;font-weight:800;color:#166534;text-transform:uppercase;letter-spacing:.06em;">Read</div>
                <div style="margin-top:8px;font-size:28px;font-weight:800;color:#166534;">{{ number_format((int) data_get($summary, 'read', 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px;">
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $filterKey => $label)
                    <a href="{{ route('notifications.index', ['status' => $filterKey]) }}"
                        style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:999px;border:1px solid {{ $statusFilter === $filterKey ? 'transparent' : 'var(--theme-border, #E6E1EF)' }};background:{{ $statusFilter === $filterKey ? 'var(--theme-primary, #240E35)' : '#fff' }};color:{{ $statusFilter === $filterKey ? '#fff' : '#0F172A' }};font-weight:700;text-decoration:none;">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit"
                    style="padding:10px 14px;border:none;border-radius:10px;background:var(--theme-accent, #6B4A7A);color:#fff;font-weight:700;cursor:pointer;">
                    Mark All Read
                </button>
            </form>
        </div>

        <div style="display:grid;gap:14px;">
            @forelse($notifications as $notification)
                @php
                    $levelTone = match ($notification->level) {
                        'error' => ['bg' => 'rgba(220, 38, 38, 0.08)', 'border' => 'rgba(220, 38, 38, 0.2)', 'text' => '#B91C1C'],
                        'warning' => ['bg' => 'rgba(245, 158, 11, 0.08)', 'border' => 'rgba(245, 158, 11, 0.2)', 'text' => '#92400E'],
                        'success' => ['bg' => 'rgba(22, 163, 74, 0.08)', 'border' => 'rgba(22, 163, 74, 0.2)', 'text' => '#166534'],
                        default => ['bg' => 'rgba(59, 130, 246, 0.08)', 'border' => 'rgba(59, 130, 246, 0.2)', 'text' => '#1D4ED8'],
                    };
                @endphp
                <article style="padding:16px 18px;border:1px solid {{ $levelTone['border'] }};border-radius:16px;background:{{ $notification->read_at ? '#fff' : $levelTone['bg'] }};">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div>
                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;background:{{ $levelTone['bg'] }};color:{{ $levelTone['text'] }};font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;">
                                    {{ $notification->level }}
                                </span>
                                <span style="display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;background:var(--theme-surface-soft, #F3EEF7);color:var(--theme-muted, #6B7280);font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;">
                                    {{ $notification->event_name }}
                                </span>
                                <span style="font-size:12px;font-weight:700;color:var(--theme-muted, #6B7280);">
                                    {{ optional($notification->occurred_at)->format('Y-m-d H:i:s') ?? $emptyDash }}
                                </span>
                            </div>
                            <h3 style="margin:12px 0 8px;font-size:18px;color:#0F172A;">{{ $notification->title }}</h3>
                            <p style="margin:0;color:#334155;line-height:1.7;">{{ $notification->message }}</p>
                        </div>

                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                            @if($notification->action_url)
                                <a href="{{ $notification->action_url }}"
                                    style="display:inline-flex;align-items:center;justify-content:center;padding:10px 12px;border-radius:10px;background:var(--theme-primary, #240E35);color:#fff;text-decoration:none;font-weight:700;">
                                    Open
                                </a>
                            @endif

                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit"
                                        style="padding:10px 12px;border:none;border-radius:10px;background:var(--theme-accent, #6B4A7A);color:#fff;font-weight:700;cursor:pointer;">
                                        Mark Read
                                    </button>
                                </form>
                            @else
                                <span style="font-size:12px;font-weight:800;color:#166534;">Read</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div style="padding:24px;border:1px dashed var(--theme-border, #E6E1EF);border-radius:16px;text-align:center;color:var(--theme-muted, #6B7280);font-weight:700;">
                    No notifications found for this filter yet.
                </div>
            @endforelse
        </div>

        <div style="margin-top:18px;">
            {{ $notifications->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
