@extends('layouts.app')

@section('title', 'Leads')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Leads</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
        <div>
            <h1 class="page-title">Leads Management</h1>
            <p class="page-subtitle">Manage leads and follow-ups</p>
        </div>
        @can('leads.create')
            <a href="{{ route('leads.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Lead
            </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-user-plus',
        'value' => $stats['new'] ?? 0,
        'label' => 'New Leads',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-phone',
        'value' => $stats['contacted'] ?? 0,
        'label' => 'Contacted',
        'bgColor' => 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-check-circle',
        'value' => $stats['converted'] ?? 0,
        'label' => 'Converted',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-chart-line',
        'value' => ($stats['conversion_rate'] ?? 0) . '%',
        'label' => 'Conversion Rate',
        'bgColor' => 'info'
    ])
</div>

<!-- Filters and Search -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Leads</h3>
        <div class="card-actions">
            <form method="GET" action="{{ route('leads.index') }}" class="filters-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search leads..." value="{{ request('search') }}">
                </div>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                    <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                    <option value="negotiation" {{ request('status') == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                    <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                </select>

                <select name="priority" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'status', 'priority']))
                    <a href="{{ route('leads.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if(empty($leads) || $leads->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>No Leads Found</h3>
                <p>Start by adding your first lead</p>
                @can('leads.create')
                    <a href="{{ route('leads.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Lead
                    </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Lead Details</th>
                            <th>Contact</th>
                            <th>Source</th>
                            <th>Assigned To</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                            <tr>
                                <td>
                                    <div class="table-primary">
                                        <strong>{{ $lead->name }}</strong>
                                        <span class="table-secondary">{{ $lead->email ?? 'No email' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="table-primary">
                                        <i class="fas fa-phone" style="color: var(--primary); margin-right: 5px;"></i>
                                        {{ $lead->phone }}
                                        @if($lead->phone_secondary)
                                            <span class="table-secondary">{{ $lead->phone_secondary }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ ucfirst($lead->source ?? 'Direct') }}
                                    </span>
                                </td>
                                <td>
                                    @if($lead->assignedTo)
                                        <div class="table-primary">
                                            <strong>{{ $lead->assignedTo->name }}</strong>
                                        </div>
                                    @else
                                        <span class="badge badge-warning">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->budget_range)
                                        <span class="badge badge-primary">
                                            {{ $lead->budget_range }}
                                        </span>
                                    @else
                                        <span style="color: var(--gray-500);">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->status === 'converted')
                                        <span class="badge badge-success">Converted</span>
                                    @elseif($lead->status === 'new')
                                        <span class="badge badge-primary">New</span>
                                    @elseif($lead->status === 'lost')
                                        <span class="badge badge-danger">Lost</span>
                                    @else
                                        <span class="badge badge-warning">{{ ucfirst($lead->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->priority === 'urgent')
                                        <span class="badge badge-danger">Urgent</span>
                                    @elseif($lead->priority === 'high')
                                        <span class="badge badge-warning">High</span>
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($lead->priority) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('leads.show', $lead) }}" class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('leads.edit')
                                            <a href="{{ route('leads.edit', $lead) }}" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @if($lead->status !== 'converted')
                                            @can('leads.convert')
                                            <a href="{{ route('leads.show', $lead) }}#convert" class="btn-icon btn-success" title="Convert to Client">
                                                <i class="fas fa-user-check"></i>
                                            </a>
                                            @endcan
                                        @endif
                                        @can('leads.delete')
                                            <button onclick="deleteLead({{ $lead->id }})" class="btn-icon btn-danger" title="Delete">
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
                {{ $leads->links() }}
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

    .btn-icon.btn-success:hover {
        background: var(--success);
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

@can('leads.delete')
<script>
    function deleteLead(id) {
        if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
            fetch(`/leads/${id}`, {
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
                    alert(data.message || 'Failed to delete lead');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the lead');
                console.error(error);
            });
        }
    }
</script>
@endcan
@endsection
