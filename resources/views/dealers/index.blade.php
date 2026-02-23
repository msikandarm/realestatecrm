@extends('layouts.app')

@section('title', 'Dealers')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Dealers</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
        <div>
            <h1 class="page-title">Dealers Management</h1>
            <p class="page-subtitle">Manage dealers and their commissions</p>
        </div>
        @can('users.create')
            <a href="{{ route('dealers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Dealer
            </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-users',
        'value' => $stats['total'] ?? 0,
        'label' => 'Total Dealers',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-user-check',
        'value' => $stats['active'] ?? 0,
        'label' => 'Active Dealers',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-handshake',
        'value' => $stats['total_deals'] ?? 0,
        'label' => 'Total Deals',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-money-bill-wave',
        'value' => 'PKR ' . number_format($stats['total_commission'] ?? 0),
        'label' => 'Total Commission',
        'bgColor' => 'warning'
    ])
</div>

<!-- Filters and Search -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Dealers</h3>
        <div class="card-actions">
            <form method="GET" action="{{ route('dealers.index') }}" class="filters-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search dealers..." value="{{ request('search') }}">
                </div>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('dealers.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($dealers->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>No Dealers Found</h3>
                <p>Start by adding your first dealer</p>
                @can('users.create')
                    <a href="{{ route('dealers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Dealer
                    </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Dealer Info</th>
                            <th>Contact</th>
                            <th>Commission Rate</th>
                            <th>Total Deals</th>
                            <th>Total Commission</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dealers as $dealer)
                            <tr>
                                <td>
                                    <div class="table-primary">
                                        <strong>{{ $dealer->user->name ?? $dealer->name ?? 'N/A' }}</strong>
                                        <span class="table-secondary">ID: #{{ $dealer->id }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-primary">
                                        @if($dealer->user && $dealer->user->email)
                                            <i class="fas fa-envelope" style="color: var(--primary); margin-right: 5px;"></i>
                                            {{ $dealer->user->email }}
                                        @endif
                                        @if($dealer->user && $dealer->user->phone)
                                            <span class="table-secondary">
                                                <i class="fas fa-phone"></i> {{ $dealer->user->phone }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $dealer->default_commission_rate }}%</span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $dealer->deals_count ?? 0 }} Deals
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">PKR {{ number_format($dealer->total_commission ?? 0) }}</strong>
                                </td>
                                <td>
                                    @if($dealer->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dealers.show', $dealer) }}" class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('dealers.performance', $dealer) }}" class="btn-icon" title="Performance">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                        @can('users.edit')
                                            <a href="{{ route('dealers.edit', $dealer) }}" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('users.delete')
                                            <button onclick="deleteDealer({{ $dealer->id }})" class="btn-icon btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $dealers->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .card-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filters-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        min-width: 250px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .search-box input {
        width: 100%;
        padding: 8px 12px 8px 36px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
        background: white;
        cursor: pointer;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary {
        background: var(--gray-100);
        color: var(--gray-700);
    }

    .btn-secondary:hover {
        background: var(--gray-200);
    }

    .btn-light {
        background: white;
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 0.8125rem;
    }

    .card-body {
        padding: 0;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: var(--gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--gray-400);
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 8px;
    }

    .empty-state p {
        color: var(--gray-600);
        margin-bottom: 20px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: var(--gray-50);
    }

    .data-table th {
        padding: 14px 20px;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-600);
        border-bottom: 1px solid var(--gray-200);
    }

    .data-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--gray-100);
        font-size: 0.875rem;
    }

    .data-table tbody tr:hover {
        background: var(--gray-50);
    }

    .table-primary {
        display: flex;
        flex-direction: column;
    }

    .table-primary strong {
        color: var(--gray-900);
        margin-bottom: 2px;
    }

    .table-secondary {
        font-size: 0.8125rem;
        color: var(--gray-500);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-success {
        background: var(--success);
        color: white;
    }

    .badge-warning {
        background: var(--warning);
        color: white;
    }

    .badge-info {
        background: var(--info);
        color: white;
    }

    .badge-primary {
        background: var(--primary);
        color: white;
    }

    .badge-danger {
        background: var(--danger);
        color: white;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: var(--gray-100);
        color: var(--gray-600);
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-icon:hover {
        background: var(--primary);
        color: white;
    }

    .btn-icon.btn-danger:hover {
        background: var(--danger);
        color: white;
    }

    .pagination-wrapper {
        padding: 20px 24px;
        border-top: 1px solid var(--gray-200);
    }

    .text-success {
        color: var(--success);
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .filters-form {
            width: 100%;
        }

        .search-box {
            width: 100%;
        }
    }
</style>

@can('users.delete')
<script>
    function deleteDealer(id) {
        if (confirm('Are you sure you want to delete this dealer? This action cannot be undone.')) {
            fetch(`/dealers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete dealer');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the dealer');
                console.error(error);
            });
        }
    }
</script>
@endcan
@endsection
