@extends('layouts.app')

@section('title', 'Societies')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Societies</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
        <div>
            <h1 class="page-title">Societies Management</h1>
            <p class="page-subtitle">Manage housing societies and schemes</p>
        </div>
        @can('societies.create')
            <a href="{{ route('societies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Society
            </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-city',
        'value' => $stats['total'] ?? 0,
        'label' => 'Total Societies',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-check-circle',
        'value' => $stats['active'] ?? 0,
        'label' => 'Active Societies',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-pause-circle',
        'value' => $stats['inactive'] ?? 0,
        'label' => 'Inactive Societies',
        'bgColor' => 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-map-marked-alt',
        'value' => $stats['total_plots'] ?? 0,
        'label' => 'Total Plots',
        'bgColor' => 'info'
    ])
</div>

<!-- Filters and Search -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Societies</h3>
        <div class="card-actions">
            <form method="GET" action="{{ route('societies.index') }}" class="filters-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search societies..." value="{{ request('search') }}">
                </div>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('societies.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($societies->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-city"></i>
                </div>
                <h3>No Societies Found</h3>
                <p>Start by adding your first housing society</p>
                @can('societies.create')
                    <a href="{{ route('societies.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Society
                    </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Society Details</th>
                            <th>City</th>
                            <th>Blocks</th>
                            <th>Plots</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($societies as $society)
                            <tr>
                                <td>
                                    <div class="table-primary">
                                        <strong>{{ $society->name }}</strong>
                                        <span class="table-secondary">Code: {{ $society->code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt" style="color: var(--primary);"></i>
                                    {{ $society->city }}
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $society->blocks_count ?? 0 }} Blocks
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $society->plots_count ?? 0 }} Plots
                                    </span>
                                </td>
                                <td>
                                    @if($society->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($society->status === 'inactive')
                                        <span class="badge badge-warning">Inactive</span>
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($society->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('societies.show', $society) }}" class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('societies.edit')
                                            <a href="{{ route('societies.edit', $society) }}" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('societies.delete')
                                            <button onclick="deleteSociety({{ $society->id }})" class="btn-icon btn-danger" title="Delete">
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
                {{ $societies->links() }}
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

@can('societies.delete')
<script>
    function deleteSociety(id) {
        if (confirm('Are you sure you want to delete this society? This action cannot be undone.')) {
            fetch(`/societies/${id}`, {
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
                    alert(data.message || 'Failed to delete society');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the society');
                console.error(error);
            });
        }
    }
</script>
@endcan
@endsection
