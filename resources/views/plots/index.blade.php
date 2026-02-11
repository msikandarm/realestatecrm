@extends('layouts.app')

@section('title', 'Plots')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Plots</span>
    </div>
    <h1 class="page-title">Plots Management</h1>
    <p class="page-subtitle">Manage plots across societies</p>
</div>
    <!-- Filters and Search -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Plots</h3>
            <div class="card-actions">
                <form method="GET" action="{{ route('plots.index') }}" class="filters-form">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search plots..." value="{{ request('search') }}">
                    </div>

                    <select name="society_id" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Societies</option>
                        @foreach($societies ?? [] as $society)
                            <option value="{{ $society->id }}" {{ request('society_id') == $society->id ? 'selected' : '' }}>{{ $society->name }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                        <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                    </select>

                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>

                    @if(request()->hasAny(['search', 'status', 'society_id']))
                        <a href="{{ route('plots.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card-body">
            @if($plots->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3>No Plots Found</h3>
                    <p>Start by adding your first plot</p>
                    @can('plots.create')
                        <a href="{{ route('plots.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Plot
                        </a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Plot #</th>
                                <th>Society</th>
                                <th>Block</th>
                                <th>Street</th>
                                <th>Size</th>
                                <th>Price (PKR)</th>
                                <th>Status</th>
                                <th>Owner</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plots as $plot)
                                <tr>
                                    <td><strong>{{ $plot->plot_number }}</strong></td>
                                    <td>{{ $plot->street->block->society->name ?? 'N/A' }}</td>
                                    <td>{{ $plot->street->block->name ?? 'N/A' }}</td>
                                    <td>{{ $plot->street->name ?? 'N/A' }}</td>
                                    <td>
                                        {{ $plot->area }} {{ $plot->area_unit }}
                                    </td>
                                    <td><strong>{{ number_format($plot->total_price ?? $plot->price ?? 0, 0) }}</strong></td>
                                    <td>
                                        @php $sclass = $plot->status === 'available' ? 'success' : ($plot->status === 'booked' ? 'warning' : 'primary'); @endphp
                                        <span class="badge badge-{{ $sclass }}">{{ ucfirst($plot->status) }}</span>
                                    </td>
                                    <td>
                                        @if($plot->current_owner_id)
                                            <div class="table-primary">{{ $plot->owner->name ?? 'N/A' }}</div>
                                            <div class="table-secondary">{{ $plot->owner->phone ?? '' }}</div>
                                        @else
                                            <span class="table-secondary">No Owner</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('plots.show', $plot) }}" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('plots.edit', $plot) }}" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('plots.destroy', $plot) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper">
                    {{ $plots->links() }}
                </div>
            @endif
        </div>
    </div>

<style>
    .card-title { font-size: 1.125rem; font-weight: 600; color: var(--gray-900); }
    .card-actions { display: flex; gap: 10px; align-items: center; }
    .filters-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .search-box { position: relative; min-width: 250px; }
    .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400); }
    .search-box input { width: 100%; padding: 8px 12px 8px 36px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 0.875rem; }
    .search-box input:focus { outline: none; border-color: var(--primary); }
    .filter-select { padding: 8px 12px; border: 1px solid var(--gray-300); border-radius: 8px; font-size: 0.875rem; background: white; cursor: pointer; }
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap; }
    .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-secondary { background: var(--gray-100); color: var(--gray-700); }
    .btn-secondary:hover { background: var(--gray-200); }
    .btn-light { background: white; color: var(--gray-700); border: 1px solid var(--gray-300); }
    .btn-sm { padding: 6px 14px; font-size: 0.8125rem; }
    .card-body { padding: 0; }
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-icon { width: 80px; height: 80px; margin: 0 auto 20px; background: var(--gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--gray-400); }
    .empty-state h3 { font-size: 1.25rem; font-weight: 600; color: var(--gray-900); margin-bottom: 8px; }
    .empty-state p { color: var(--gray-600); margin-bottom: 20px; }
    .table-responsive { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead { background: var(--gray-50); }
    .data-table th { padding: 14px 20px; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-600); border-bottom: 1px solid var(--gray-200); }
    .data-table td { padding: 16px 20px; border-bottom: 1px solid var(--gray-100); font-size: 0.875rem; }
    .data-table tbody tr:hover { background: var(--gray-50); }
    .table-primary { display: flex; flex-direction: column; }
    .table-primary strong { color: var(--gray-900); margin-bottom: 2px; }
    .table-secondary { font-size: 0.8125rem; color: var(--gray-500); }
    .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
    .badge-success { background: var(--success); color: white; }
    .badge-warning { background: var(--warning); color: white; }
    .badge-info { background: var(--info); color: white; }
    .badge-primary { background: var(--primary); color: white; }
    .action-buttons { display: flex; gap: 8px; }
    .btn-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: var(--gray-100); color: var(--gray-600); border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-icon:hover { background: var(--primary); color: white; }
    .btn-icon.btn-danger:hover { background: var(--danger); color: white; }
    .pagination-wrapper { padding: 20px 24px; border-top: 1px solid var(--gray-200); }
    @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } .card-header { flex-direction: column; align-items: flex-start; } .filters-form { width: 100%; } .search-box { width: 100%; } }
</style>

@endsection
