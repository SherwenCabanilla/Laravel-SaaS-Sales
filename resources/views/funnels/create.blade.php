@extends('layouts.admin')

@section('title', 'Create Funnel')

@section('content')
    <div class="top-header">
        <h1>Create Funnel</h1>
    </div>

    <div class="card" style="max-width: 820px; margin: 0 auto;">
        <form method="POST" action="{{ route('funnels.store') }}">
            @csrf

            @if(!empty($templateAccessSummary))
                <div style="margin-bottom: 18px; padding: 14px 16px; border-radius: 12px; background: #fbf9fd; border: 1px solid #ece2f5; color: #475569; line-height: 1.55;">
                    <strong style="display:block; margin-bottom:6px; color:#240E35;">Shared template access</strong>
                    @if(!empty($templateAccessSummary['is_unlimited']))
                        Your {{ $templateAccessSummary['plan_name'] }} plan can use the full Super Admin template library.
                    @else
                        Your {{ $templateAccessSummary['plan_name'] }} plan can use up to {{ $templateAccessSummary['limit'] }} Super Admin shared template{{ (int) ($templateAccessSummary['limit'] ?? 0) === 1 ? '' : 's' }}.
                    @endif
                    Templates shown below already reflect what your current subscription can access.
                </div>
            @endif

            <div style="margin-bottom: 16px;">
                <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                @error('name')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:8px; font-weight:700;">Description</label>
                <textarea id="description" name="description" rows="4"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">{{ old('description') }}</textarea>
                @error('description')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Funnel Style</label>
                <input type="hidden" name="template_type" value="step_by_step">
                <div style="padding:12px 14px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fbf9fd; font-weight:700; color:#240E35;">
                    Step-by-Step Page
                </div>
                @error('template_type')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="funnel_purpose" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Purpose</label>
                <select id="funnel_purpose" name="funnel_purpose" required
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                    @foreach(($funnelPurposeOptions ?? []) as $value => $label)
                        <option value="{{ $value }}" {{ old('funnel_purpose', 'service') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="margin-top:6px; color:#64748b; font-size:12px;">
                    Choose whether this funnel is for Services or Physical Product sales.
                </div>
                @error('funnel_purpose')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Start From</label>
                <div style="display:grid; gap:10px;" id="templateChoiceList">
                    <label data-template-card data-purpose="manual" style="display:block; border:1px solid var(--theme-border, #E6E1EF); border-radius:8px; padding:14px; cursor:pointer; background:#fff;">
                        <input type="radio" name="template_id" value="" {{ old('template_id') ? '' : 'checked' }} style="margin-right:8px;">
                        <strong>Manual build</strong>
                        <span style="display:block; margin-top:4px; color:#64748b; font-size:12px;">Create the standard step-by-step pages, then design them in the drag-and-drop builder.</span>
                    </label>

                    @foreach(($availableTemplates ?? []) as $template)
                        <label data-template-card data-purpose="{{ $template['funnel_purpose'] }}" style="display:block; border:1px solid var(--theme-border, #E6E1EF); border-radius:8px; padding:14px; cursor:pointer; background:#fff;">
                            <input type="radio" name="template_id" value="{{ $template['id'] }}" {{ (string) old('template_id') === (string) $template['id'] ? 'checked' : '' }} style="margin-right:8px;">
                            <strong>{{ $template['name'] }}</strong>
                            <span style="display:block; margin-top:4px; color:#64748b; font-size:12px;">
                                {{ $template['description'] ?: 'Super Admin step-by-step template.' }}
                            </span>
                            <span style="display:block; margin-top:8px; color:#475569; font-size:12px;">
                                {{ $template['steps_count'] }} pages
                                @if(!empty($template['tags']))
                                    - {{ implode(' - ', $template['tags']) }}
                                @endif
                            </span>
                        </label>
                    @endforeach

                    @if(empty($availableTemplates))
                        <div style="padding:14px; border:1px dashed var(--theme-border, #E6E1EF); border-radius:8px; background:#fff; color:#64748b; font-size:13px; line-height:1.55;">
                            No Super Admin shared templates are available on your current plan right now. You can still start with a manual funnel build.
                        </div>
                    @endif
                </div>
                @error('template_id')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin:18px 0; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.55;">
                Build setup:
                <br><strong>Funnel Style</strong>: Step-by-Step Page.
                <br><strong>Funnel Purpose</strong>: Services or Physical Product.
                <br><br>Only the Super Admin templates allowed by your current plan are shown here.
            </div>

            <div style="display:flex; gap:10px; margin-top:18px;">
                <button type="submit" class="btn-create">Create Funnel</button>
                <a href="{{ route('funnels.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const purposeSelect = document.getElementById('funnel_purpose');
            const cards = Array.from(document.querySelectorAll('[data-template-card]'));

            function syncTemplateCards() {
                const purpose = purposeSelect ? purposeSelect.value : 'service';
                cards.forEach(function (card) {
                    const cardPurpose = card.getAttribute('data-purpose') || '';
                    const visible = cardPurpose === 'manual' || cardPurpose === purpose;
                    card.style.display = visible ? 'block' : 'none';

                    const input = card.querySelector('input[type="radio"]');
                    if (!visible && input && input.checked) {
                        const manualInput = document.querySelector('[data-purpose="manual"] input[type="radio"]');
                        if (manualInput) {
                            manualInput.checked = true;
                        }
                    }
                });
            }

            if (purposeSelect) {
                purposeSelect.addEventListener('change', syncTemplateCards);
            }
            syncTemplateCards();
        });
    </script>
@endsection
