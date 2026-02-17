@extends('layouts.admin')

@section('title', 'Manage Tenants')

@section('content')
    <div class="top-header">
        <h1>Manage Tenants</h1>
        <div class="header-right">
            <div class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </div>
        </div>
    </div>

    <div class="actions">
        <a href="{{ route('admin.tenants.create') }}" class="btn-create">
            <button><i class="fas fa-plus"></i> Add New Tenant</button>
        </a>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <h3>Tenant List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Subscription Plan</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                    <tr>
                        <td>{{ $tenant->id }}</td>
                        <td>{{ $tenant->company_name }}</td>
                        <td>{{ $tenant->subscription_plan }}</td>
                        <td>
                            @if($tenant->status == 'active')
                                <span style="color: green; font-weight: bold;">Active</span>
                            @elseif($tenant->status == 'inactive')
                                <span style="color: red; font-weight: bold;">Inactive</span>
                            @else
                                <span style="color: orange; font-weight: bold;">Trial</span>
                            @endif
                        </td>
                        <td>{{ $tenant->created_at->format('Y-m-d') }}</td>
                        <td style="display: flex; gap: 10px;">
                            <a href="{{ route('admin.tenants.edit', $tenant->id) }}" style="color: #2563EB; text-decoration: none;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.tenants.destroy', $tenant->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No tenants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="margin-top: 20px;">
            {{ $tenants->links('pagination::bootstrap-4') }} 
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .btn-create button {
            /* Inherit styles from .actions button but remove default link styles if wrapper */
        }
        .pagination {
            display: flex;
            list-style: none;
            gap: 5px;
        }
        .page-item .page-link {
            padding: 8px 12px;
            border: 1px solid #DBEAFE;
            color: #2563EB;
            text-decoration: none;
            border-radius: 4px;
        }
        .page-item.active .page-link {
            background-color: #2563EB;
            color: white;
        }
    </style>
@endsection
