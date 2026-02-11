@extends('layouts.app')

@section('title', 'Cities')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span>Cities</span>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
        <div>
            <h1 class="page-title">Cities</h1>
            <p class="page-subtitle">Manage cities used across the system</p>
        </div>
        @can('cities.create')
            <a href="{{ route('cities.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add City</a>
        @endcan
    </div>
</div>

<!-- Cities list card (styled like Societies) -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Cities</h3>
        <div class="card-actions">
            <form method="GET" action="{{ route('cities.index') }}" class="filters-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search cities..." value="{{ request('search') }}">
                </div>

                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->has('search'))
                    <a href="{{ route('cities.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($cities->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-map-pin"></i>
                </div>
                <h3>No Cities</h3>
                <p>Add cities to make them available across the system</p>
                @can('cities.create')
                    <a href="{{ route('cities.create') }}" class="btn btn-primary">Add City</a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Province</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cities as $city)
                            <tr>
                                <td>
                                    <div class="table-primary">
                                        <strong>{{ $city->name }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="table-secondary">{{ $city->province }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @can('cities.edit')
                                            <a href="{{ route('cities.edit', $city) }}" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('cities.delete')
                                            <form action="{{ route('cities.destroy', $city) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn-icon btn-danger" onclick="return confirm('Delete city?')" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $cities->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    /* Reuse Societies page styles for consistency */
    .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
    .card-header { padding: 20px 24px; border-bottom: 1px solid var(--gray-200); display:flex; justify-content:space-between; align-items:center; }
    .card-title { font-size:1.125rem; font-weight:600; color:var(--gray-900); }
    .card-actions { display:flex; gap:10px; align-items:center; }
    .filters-form { display:flex; gap:10px; align-items:center; }
    .search-box { position:relative; min-width:240px; }
    .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--gray-400); }
    .search-box input { padding:8px 12px 8px 36px; border:1px solid var(--gray-300); border-radius:8px; }
    .btn { display:inline-flex; align-items:center; gap:8px; padding:8px 14px; border-radius:8px; cursor:pointer; text-decoration:none; }
    .btn-primary { background: linear-gradient(135deg,var(--primary),var(--secondary)); color:white; }
    .btn-secondary { background: var(--gray-100); color:var(--gray-700); }
    .btn-light { background:white; color:var(--gray-700); border:1px solid var(--gray-300); }
    .card-body { padding: 0; }
    .data-table { width:100%; border-collapse:collapse; }
    .data-table thead { background: var(--gray-50); }
    .data-table th { padding:14px 20px; text-align:left; font-size:0.75rem; font-weight:700; text-transform:uppercase; color:var(--gray-600); border-bottom:1px solid var(--gray-200); }
    .data-table td { padding:16px 20px; border-bottom:1px solid var(--gray-100); font-size:0.95rem; }
    .table-primary strong { color:var(--gray-900); }
    .table-secondary { color:var(--gray-500); }
    .action-buttons { display:flex; gap:8px; }
    .btn-icon { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:6px; background:var(--gray-100); color:var(--gray-600); text-decoration:none; }
    .btn-icon:hover { background:var(--primary); color:white; }
    .btn-icon.btn-danger:hover { background:var(--danger); }
    .pagination-wrapper { padding:20px 24px; border-top:1px solid var(--gray-200); }
    .empty-state { text-align:center; padding:60px 20px; }
    .empty-icon { width:80px; height:80px; margin:0 auto 20px; background:var(--gray-100); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; color:var(--gray-400); }

    @media (max-width:768px) {
        .filters-form { flex-direction:column; align-items:flex-start; }
        .search-box { min-width: 100%; }
    }
</style>

@endsection
