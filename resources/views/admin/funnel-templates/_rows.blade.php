@php
    $emptyMessage = $emptyMessage ?? 'No funnel templates found.';
@endphp

@forelse($templates as $template)
    <tr>
        <td>{{ $template->name }}</td>
        <td>{{ $template->templateTypeLabel() }}</td>
        <td>{{ ucfirst($template->status) }}</td>
        <td>{{ $template->steps_count }}</td>
        <td>{{ $template->slug }}</td>
        <td style="display:flex;gap:14px;align-items:center;white-space:nowrap;">
            <a href="{{ route('admin.funnel-templates.edit', $template) }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--theme-primary, #240E35);text-decoration:none;font-weight:600;white-space:nowrap;">
                <i class="fas fa-pen"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.funnel-templates.destroy', $template) }}" style="display:inline-flex;align-items:center;" data-delete-template-form data-template-name="{{ $template->name }}">
                @csrf
                @method('DELETE')
                <button type="submit" style="display:inline-flex;align-items:center;gap:6px;background:none;border:none;color:#DC2626;cursor:pointer;padding:0;font-weight:600;white-space:nowrap;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align:center; color:#64748b;">{{ $emptyMessage }}</td>
    </tr>
@endforelse
