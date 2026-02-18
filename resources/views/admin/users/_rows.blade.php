@forelse($users as $user)
    <tr>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
            @if($user->tenant)
                <span style="background-color: #F3F4F6; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                    {{ $user->tenant->company_name }}
                </span>
            @else
                <span style="color: #9CA3AF; font-size: 12px;">N/A</span>
            @endif
        </td>
        <td>
            @foreach($user->roles as $role)
                <span style="background-color: #EFF6FF; color: #1E40AF; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-right: 4px; font-weight: 700;">
                    {{ $role->name }}
                </span>
            @endforeach
        </td>
        <td>
            @if($user->status === 'active')
                <span style="color: #047857; font-weight: 700;">Active</span>
            @else
                <span style="color: #B91C1C; font-weight: 700;">Suspended</span>
            @endif
        </td>
        <td>{{ $user->created_at->format('Y-m-d') }}</td>
        <td>
            @if($user->hasRole('account-owner'))
                <form action="{{ route('admin.users.status', $user->id) }}" method="POST"
                    onsubmit="if('{{ $user->status }}' === 'active'){ const reason = prompt('Reason for suspending Account:'); if(!reason){ return false; } this.querySelector('input[name=suspension_reason]').value = reason; } return true;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="suspension_reason" value="">
                    <button type="submit"
                        style="background: none; border: none; color: {{ $user->status === 'active' ? '#B91C1C' : '#047857' }}; cursor: pointer; padding: 0; font-weight: 700;">
                        <i class="fas {{ $user->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                        {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                    </button>
                </form>
            @else
                <span style="color: #64748B; font-size: 12px; font-weight: 700;">N/A</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center;">No users found.</td>
    </tr>
@endforelse
