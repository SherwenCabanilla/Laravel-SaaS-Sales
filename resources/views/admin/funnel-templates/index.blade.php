@extends('layouts.admin')

@section('title', 'Funnel Templates')

@section('content')
    <div class="top-header">
        <h1>Shared Funnel Templates</h1>
    </div>

    <div class="actions" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.funnel-templates.create') }}" class="btn-create"><i class="fas fa-plus"></i> New Template</a>
            <a href="{{ route('admin.funnel-templates.import') }}" class="btn-create" style="background:#fff; color:var(--theme-primary, #240E35); border:1px solid var(--theme-border, #E6E1EF);"><i class="fas fa-file-import"></i> Import JSON Template</a>
        </div>
        <form method="GET" action="{{ route('admin.funnel-templates.index') }}">
            @if(!empty($showLegacy))
                <input type="hidden" name="legacy" value="1">
            @endif
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search templates..."
                style="width:min(320px, 100%); padding:10px 12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff;">
        </form>
    </div>

    <div class="card" style="margin-top: 16px;">
        <div style="margin-bottom: 12px; color:#64748b; font-size:13px;">
            Super admins can build, import, and publish templates here. Published templates appear in builder mode for reusable application.
        </div>
        <div style="margin-bottom: 14px; color:#64748b; font-size:13px;">
            @if(!empty($showLegacy))
                Showing uncategorized legacy templates too.
                <a href="{{ route('admin.funnel-templates.index', array_filter(['search' => $search ?? null])) }}" style="font-weight:700;">Hide legacy templates</a>
            @else
                Legacy templates without a purpose are hidden from this list and from the builder libraries.
                <a href="{{ route('admin.funnel-templates.index', array_filter(['search' => $search ?? null, 'legacy' => 1])) }}" style="font-weight:700;">Show legacy templates</a>
            @endif
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Pages</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.funnel-templates._rows', ['templates' => $templates])
            </tbody>
        </table>

        <div style="margin-top:18px;">
            {{ $templates->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <div id="deleteTemplateModal" class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="deleteTemplateModalTitle" style="width:100%;max-width:460px;background:#fff;border-radius:10px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.15);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:8px;">
                <h3 id="deleteTemplateModalTitle" style="margin:0;">Confirm Template Deletion</h3>
                <button type="button" id="closeDeleteTemplateModal" class="modal-close-btn" style="background:none;border:none;font-size:28px;cursor:pointer;color:var(--theme-muted, #6B7280);line-height:1;padding:0 4px;">&times;</button>
            </div>
            <p style="margin:0 0 18px;color:var(--theme-muted, #6B7280);line-height:1.6;">
                Delete <strong id="deleteTemplateName">this template</strong>? This cannot be undone.
            </p>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="cancelDeleteTemplateBtn" class="btn-create" style="background:#64748B;">Cancel</button>
                <button type="button" id="confirmDeleteTemplateBtn" class="btn-create" style="background:#DC2626;">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteTemplateModal = document.getElementById('deleteTemplateModal');
            const deleteTemplateName = document.getElementById('deleteTemplateName');
            const closeDeleteTemplateModal = document.getElementById('closeDeleteTemplateModal');
            const cancelDeleteTemplateBtn = document.getElementById('cancelDeleteTemplateBtn');
            const confirmDeleteTemplateBtn = document.getElementById('confirmDeleteTemplateBtn');
            let pendingDeleteForm = null;

            const closeTemplateDeleteModal = () => {
                if (!deleteTemplateModal) return;
                deleteTemplateModal.style.display = 'none';
                pendingDeleteForm = null;
            };

            const openTemplateDeleteModal = (form) => {
                if (!deleteTemplateModal) {
                    if (window.confirm('Delete this template? This cannot be undone.')) {
                        form.submit();
                    }
                    return;
                }
                pendingDeleteForm = form;
                const name = form.getAttribute('data-template-name') || 'this template';
                if (deleteTemplateName) {
                    deleteTemplateName.textContent = name;
                }
                deleteTemplateModal.style.display = 'flex';
            };

            document.addEventListener('submit', function(event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || !form.matches('form[data-delete-template-form]')) {
                    return;
                }

                event.preventDefault();
                openTemplateDeleteModal(form);
            });

            if (closeDeleteTemplateModal) {
                closeDeleteTemplateModal.addEventListener('click', closeTemplateDeleteModal);
            }
            if (cancelDeleteTemplateBtn) {
                cancelDeleteTemplateBtn.addEventListener('click', closeTemplateDeleteModal);
            }
            if (confirmDeleteTemplateBtn) {
                confirmDeleteTemplateBtn.addEventListener('click', function() {
                    if (pendingDeleteForm) {
                        pendingDeleteForm.submit();
                    }
                });
            }
            if (deleteTemplateModal) {
                deleteTemplateModal.addEventListener('click', function(event) {
                    if (event.target === deleteTemplateModal) {
                        closeTemplateDeleteModal();
                    }
                });
            }
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && deleteTemplateModal && deleteTemplateModal.style.display === 'flex') {
                    closeTemplateDeleteModal();
                }
            });
        });
    </script>
@endsection
