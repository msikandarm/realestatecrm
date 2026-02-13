@extends('layouts.app')

@section('title', 'Properties')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Properties</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
        <div>
            <h1 class="page-title">Properties</h1>
            <p class="page-subtitle">Manage property listings and inventory</p>
        </div>
        @can('properties.create')
            <a href="{{ route('properties.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Property
            </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-building',
        'value' => $stats['total'] ?? 0,
        'label' => 'Total Properties',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-check-circle',
        'value' => $stats['available'] ?? 0,
        'label' => 'Available',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-dollar-sign',
        'value' => $stats['sold'] ?? 0,
        'label' => 'Sold',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-home',
        'value' => $stats['rented'] ?? 0,
        'label' => 'Rented',
        'bgColor' => 'warning'
    ])
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Properties</h3>
        <div class="card-actions">
            <form method="GET" action="{{ route('properties.index') }}" class="filters-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search properties..." value="{{ request('search') }}">
                </div>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                    <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                </select>

                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('properties.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($properties->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>No Properties Found</h3>
                <p>Start by adding your first property</p>
                @can('properties.create')
                    <a href="{{ route('properties.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Property
                    </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Property Details</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Details</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($properties as $property)
                            <tr>
                                <td>
                                    <div class="table-primary">
                                        <strong>{{ $property->title }}</strong>
                                        <span class="table-secondary">Code: {{ $property->reference_code ?? '#'.$property->id }}</span>
                                    </div>
                                </td>
                                <td>
                                    {{ ucfirst($property->type) }}
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt" style="color: var(--primary);"></i>
                                    {{ $property->location }}
                                </td>
                                <td>
                                    <div class="property-details-cell">
                                        @if($property->bedrooms)
                                            <span><i class="fas fa-bed"></i> {{ $property->bedrooms }} Beds</span>
                                        @endif
                                        @if($property->bathrooms)
                                            <span><i class="fas fa-bath"></i> {{ $property->bathrooms }} Baths</span>
                                        @endif
                                        @if($property->size)
                                            <span><i class="fas fa-ruler-combined"></i> {{ $property->size }} {{ $property->size_unit }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong>PKR {{ number_format($property->price) }}</strong>
                                </td>
                                <td>
                                    @if($property->status === 'available')
                                        <span class="badge badge-success">Available</span>
                                    @elseif($property->status === 'rented')
                                        <span class="badge badge-warning">Rented</span>
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($property->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('properties.show', $property) }}" class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('properties.edit')
                                            <a href="{{ route('properties.edit', $property) }}" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('properties.delete')
                                            <button onclick="deleteProperty({{ $property->id }})" class="btn-icon btn-danger" title="Delete">
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
                {{ $properties->links() }}
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

    .btn-secondary {
        background: var(--gray-100);
        color: var(--gray-700);
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

    .action-buttons { display: flex; gap: 8px; }

    .btn-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: var(--gray-100); color: var(--gray-600); border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }

    .btn-icon:hover { background: var(--primary); color: white; }

    .pagination-wrapper { padding: 20px 24px; border-top: 1px solid var(--gray-200); }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .card-header { flex-direction: column; align-items: flex-start; }
        .filters-form { width: 100%; }
    }
</style>

@can('properties.delete')
<script>
    function deleteProperty(id) {
        if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
            fetch(`/properties/${id}`, {
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
                    alert(data.message || 'Failed to delete property');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the property');
                console.error(error);
            });
        }
    }
</script>
@endcan
@endsection
