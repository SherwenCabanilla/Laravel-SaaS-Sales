@extends('layouts.admin')

@section('title', 'Manage Leads')

@section('content')
    <div class="top-header">
        <h1>Manage Leads</h1>
    </div>

    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('leads.create') }}" class="btn-create"><i class="fas fa-plus"></i> Add New Lead</a>
                <button type="button" id="togglePipelineBtn" class="btn-create" style="background-color: #0EA5E9;">
                    <i class="fas fa-columns"></i> View Lead Pipeline
                </button>
                <button type="button" id="toggleAssignBtn" class="btn-create" style="background-color: #14B8A6;">
                    <i class="fas fa-user-check"></i> Assign Lead
                </button>
            </div>
        @else
            <div></div>
        @endif

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search leads..."
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 300px;">
        </div>
    </div>

    <div id="pipelineContainer" class="card" style="overflow-x: auto; margin-bottom: 20px; display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
            <h3 style="margin: 0;">Lead Pipeline</h3>
            <form method="GET" action="{{ route('leads.index') }}" style="display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="text" name="pipeline_search" value="{{ $pipelineSearch }}" placeholder="Filter pipeline by lead name"
                    style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 260px;">
                <button type="submit" class="btn-create" style="padding: 8px 12px;">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <p style="font-size: 12px; color: #64748B; margin-bottom: 12px; font-weight: 600;">
            Showing the latest 12 leads per stage. Use search to filter large pipelines.
        </p>

        <div style="display: grid; grid-template-columns: repeat(5, minmax(220px, 1fr)); gap: 12px; min-width: 1140px;">
            @foreach($pipelineStatuses as $status => $label)
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px; max-height: 460px; overflow-y: auto;">
                    <h4 style="margin: 0 0 8px; font-size: 13px; color: #1E3A8A;">
                        {{ $label }} ({{ $pipelineLeads[$status]->count() }})
                    </h4>
                    @forelse($pipelineLeads[$status] as $pipelineLead)
                        <div style="padding: 8px; border-radius: 6px; border: 1px solid #E5E7EB; background: white; margin-bottom: 8px;">
                            <strong style="display: block; font-size: 13px;">{{ $pipelineLead->name }}</strong>
                            <small style="color: #64748B; font-weight: 700;">{{ $pipelineLead->assignedAgent->name ?? 'Unassigned' }}</small>
                        </div>
                    @empty
                        <p style="font-size: 12px; color: #94A3B8; margin: 0; font-weight: 700;">No leads</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Leads List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @include('leads._rows', ['leads' => $leads])
            </tbody>
        </table>

        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $leads->links('pagination::bootstrap-4') }}
        </div>
    </div>

    @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
        <div id="quickAssignContainer" class="card" style="max-width: 500px; display: none;">
            <h3>Quick Assignment</h3>
            <form method="POST" id="quickAssignForm">
                @csrf
                <div style="margin-bottom: 10px;">
                    <label for="leadSelect">Lead</label>
                    <select id="leadSelect" style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        @forelse($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->assignedAgent->name ?? 'Unassigned' }})</option>
                        @empty
                            <option value="">No leads available</option>
                        @endforelse
                    </select>
                </div>
                <div style="margin-bottom: 10px;">
                    <label for="agentSelect">Sales Agent</label>
                    <select id="agentSelect" name="assigned_to" style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        <option value="">Unassigned</option>
                        @foreach($assignableAgents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    style="padding: 8px 16px; background-color: #0EA5E9; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Save Assignment
                </button>
            </form>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const paginationLinks = document.getElementById('paginationLinks');
            const quickAssignForm = document.getElementById('quickAssignForm');
            const leadSelect = document.getElementById('leadSelect');
            const pipelineContainer = document.getElementById('pipelineContainer');
            const quickAssignContainer = document.getElementById('quickAssignContainer');
            const togglePipelineBtn = document.getElementById('togglePipelineBtn');
            const toggleAssignBtn = document.getElementById('toggleAssignBtn');

            let timeout = null;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(timeout);
                const query = searchInput.value;
                if (query.length > 0 && query.length < 2) return;

                timeout = setTimeout(() => {
                    fetch(`{{ route('leads.index') }}?search=${encodeURIComponent(query)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(response => response.text())
                        .then(html => {
                            tableBody.innerHTML = html;
                            if (query.length > 0) {
                                paginationLinks.style.display = 'none';
                            } else {
                                paginationLinks.style.display = 'block';
                                if (query === '') window.location.reload();
                            }
                        })
                        .catch(error => console.error('Search error:', error));
                }, 300);
            });

            if (quickAssignForm && leadSelect) {
                quickAssignForm.addEventListener('submit', function(event) {
                    if (!leadSelect.value) {
                        event.preventDefault();
                        return;
                    }

                    event.preventDefault();
                    quickAssignForm.action = `/leads/${leadSelect.value}/assign`;
                    quickAssignForm.submit();
                });
            }

            if (togglePipelineBtn && pipelineContainer) {
                togglePipelineBtn.addEventListener('click', function() {
                    const isVisible = pipelineContainer.style.display === 'block';
                    pipelineContainer.style.display = isVisible ? 'none' : 'block';
                });
            }

            if (toggleAssignBtn && quickAssignContainer) {
                toggleAssignBtn.addEventListener('click', function() {
                    const isVisible = quickAssignContainer.style.display === 'block';
                    quickAssignContainer.style.display = isVisible ? 'none' : 'block';
                });
            }
        });
    </script>
@endsection
