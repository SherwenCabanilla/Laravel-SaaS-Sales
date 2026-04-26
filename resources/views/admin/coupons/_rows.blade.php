@forelse($coupons as $coupon)
    <tr>
        <td style="font-weight:800;letter-spacing:.08em;">{{ $coupon->code }}</td>
        <td>{{ $coupon->title ?: 'Untitled coupon' }}</td>
        <td>
            @if($coupon->discount_type === \App\Models\Coupon::DISCOUNT_PERCENT)
                {{ number_format((float) $coupon->discount_value, 2) }}%
            @else
                PHP {{ number_format((float) $coupon->discount_value, 2) }}
            @endif
        </td>
        <td>
            {{ $coupon->usage_mode === \App\Models\Coupon::USAGE_SINGLE ? 'Single use' : 'Multi use' }}
            @if($coupon->max_total_uses)
                <div style="font-size:12px;color:#64748b;">Max {{ $coupon->max_total_uses }} total</div>
            @endif
        </td>
        <td>{{ ucfirst($coupon->status) }}</td>
        <td>
            @if($coupon->assignedTenants->isEmpty())
                <span style="color:#64748b;">None</span>
            @else
                {{ $coupon->assignedTenants->pluck('company_name')->implode(', ') }}
            @endif
        </td>
        <td>{{ (int) $coupon->times_used }}</td>
        <td style="white-space:nowrap;">
            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn-create" style="padding:8px 12px;">Edit</a>
            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" style="display:inline-block;" onsubmit="return confirm('Delete this coupon?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-create" style="padding:8px 12px;background:#991b1b;">Delete</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align:center;color:#64748b;">No platform coupons yet.</td>
    </tr>
@endforelse
