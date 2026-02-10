@extends('layouts.app')

@section('title', 'Properties')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Properties</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Properties</h1>
        @can('properties.create')
        <a href="{{ route('properties.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Property
        </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <x-stat-card
        icon="fas fa-building"
        value="{{ $stats['total'] ?? 0 }}"
        label="Total Properties"
        bgColor="bg-primary"
    />
    <x-stat-card
        icon="fas fa-check-circle"
        value="{{ $stats['for_sale'] ?? 0 }}"
        label="For Sale"
        bgColor="bg-success"
    />
    <x-stat-card
        icon="fas fa-key"
        value="{{ $stats['rented'] ?? 0 }}"
        label="Rented"
        bgColor="bg-warning"
    />
    <x-stat-card
        icon="fas fa-hourglass-half"
        value="{{ $stats['pending'] ?? 0 }}"
        label="Pending"
        bgColor="bg-info"
    />
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> All Properties
        </h3>
        <div class="card-actions">
            <div class="view-toggle">
                <button class="view-btn active" onclick="switchView('grid')">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" onclick="switchView('list')">
                    <i class="fas fa-list"></i>
                </button>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search properties..." onkeyup="filterProperties()">
            </div>
            <select id="typeFilter" onchange="filterProperties()">
                <option value="">All Types</option>
                <option value="house">House</option>
                <option value="apartment">Apartment</option>
                <option value="commercial">Commercial</option>
                <option value="plot">Plot</option>
            </select>
            <select id="statusFilter" onchange="filterProperties()">
                <option value="">All Status</option>
                <option value="for_sale">For Sale</option>
                <option value="rented">Rented</option>
                <option value="sold">Sold</option>
                <option value="pending">Pending</option>
            </select>
        </div>
    </div>

    <div class="card-body">
        @if($properties->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="empty-title">No Properties Found</h3>
                <p class="empty-description">Start adding properties to your inventory</p>
                @can('properties.create')
                <a href="{{ route('properties.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Property
                </a>
                @endcan
            </div>
        @else
            <div id="gridView" class="properties-grid">
                @foreach($properties as $property)
                <div class="property-card" data-type="{{ $property->type }}" data-status="{{ $property->status }}">
                    <div class="property-image">
                        @if($property->images && count($property->images) > 0)
                            <img src="{{ asset('storage/' . $property->images[0]) }}" alt="{{ $property->title }}">
                        @else
                            <div class="no-image">
                                <i class="fas fa-building"></i>
                            </div>
                        @endif
                        <span class="property-badge badge-{{ $property->status }}">
                            {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                        </span>
                    </div>
                    <div class="property-content">
                        <h4 class="property-title">{{ $property->title }}</h4>
                        <div class="property-location">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $property->location }}
                        </div>
                        <div class="property-features">
                            @if($property->bedrooms)
                                <span><i class="fas fa-bed"></i> {{ $property->bedrooms }} Beds</span>
                            @endif
                            @if($property->bathrooms)
                                <span><i class="fas fa-bath"></i> {{ $property->bathrooms }} Baths</span>
                            @endif
                            @if($property->area)
                                <span><i class="fas fa-ruler-combined"></i> {{ $property->area }} {{ $property->area_unit }}</span>
                            @endif
                        </div>
                        <div class="property-footer">
                            <div class="property-price">PKR {{ number_format($property->price) }}</div>
                            <div class="property-actions">
                                <a href="{{ route('properties.show', $property) }}" class="btn-icon" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('properties.edit')
                                <a href="{{ route('properties.edit', $property) }}" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('properties.delete')
                                <button onclick="deleteProperty({{ $property->id }})" class="btn-icon text-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div id="listView" class="properties-list" style="display: none;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Details</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="propertiesTableBody">
                        @foreach($properties as $property)
                        <tr data-type="{{ $property->type }}" data-status="{{ $property->status }}">
                            <td>
                                <div class="table-item">
                                    <div class="item-image">
                                        @if($property->images && count($property->images) > 0)
                                            <img src="{{ asset('storage/' . $property->images[0]) }}" alt="{{ $property->title }}">
                                        @else
                                            <i class="fas fa-building"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="item-title">{{ $property->title }}</div>
                                        <div class="item-subtitle">ID: #{{ $property->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="type-badge">{{ ucfirst($property->type) }}</span></td>
                            <td>
                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                                {{ $property->location }}
                            </td>
                            <td>
                                <div class="property-details-cell">
                                    @if($property->bedrooms)
                                        <span><i class="fas fa-bed"></i> {{ $property->bedrooms }}</span>
                                    @endif
                                    @if($property->bathrooms)
                                        <span><i class="fas fa-bath"></i> {{ $property->bathrooms }}</span>
                                    @endif
                                    @if($property->area)
                                        <span><i class="fas fa-ruler-combined"></i> {{ $property->area }} {{ $property->area_unit }}</span>
                                    @endif
                                </div>
                            </td>
                            <td><strong>PKR {{ number_format($property->price) }}</strong></td>
                            <td><span class="status-badge status-{{ $property->status }}">{{ ucfirst(str_replace('_', ' ', $property->status)) }}</span></td>
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
                                    <button onclick="deleteProperty({{ $property->id }})" class="btn-icon text-danger" title="Delete">
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

            <div class="pagination-wrapper">
                {{ $properties->links() }}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .view-toggle { display: flex; gap: 4px; background: var(--gray-100); padding: 4px; border-radius: 8px; margin-right: 15px; }
    .view-btn { padding: 8px 12px; background: transparent; border: none; border-radius: 6px; cursor: pointer; color: var(--gray-600); transition: all 0.3s; }
    .view-btn.active { background: white; color: var(--primary); box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
    .properties-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
    .property-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); transition: transform 0.3s, box-shadow 0.3s; }
    .property-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); }
    .property-image { position: relative; height: 200px; overflow: hidden; background: var(--gray-100); }
    .property-image img { width: 100%; height: 100%; object-fit: cover; }
    .no-image { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--gray-400); font-size: 3rem; }
    .property-badge { position: absolute; top: 12px; right: 12px; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: rgba(255, 255, 255, 0.95); }
    .badge-for_sale { color: #059669; }
    .badge-rented { color: #d97706; }
    .badge-sold { color: #2563eb; }
    .badge-pending { color: #6366f1; }
    .property-content { padding: 20px; }
    .property-title { font-size: 1.1rem; font-weight: 700; color: var(--gray-900); margin: 0 0 10px 0; }
    .property-location { color: var(--gray-600); font-size: 0.9rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .property-features { display: flex; flex-wrap: wrap; gap: 12px; padding: 12px 0; border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200); margin-bottom: 12px; }
    .property-features span { display: flex; align-items: center; gap: 6px; font-size: 0.875rem; color: var(--gray-700); }
    .property-features i { color: var(--gray-500); }
    .property-footer { display: flex; justify-content: space-between; align-items: center; }
    .property-price { font-size: 1.25rem; font-weight: 700; color: var(--success); }
    .property-actions { display: flex; gap: 8px; }
    .table-item { display: flex; align-items: center; gap: 12px; }
    .item-image { width: 50px; height: 50px; border-radius: 8px; overflow: hidden; background: var(--gray-100); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .item-image img { width: 100%; height: 100%; object-fit: cover; }
    .item-image i { color: var(--gray-400); font-size: 1.5rem; }
    .type-badge { padding: 4px 10px; background: var(--gray-100); color: var(--gray-700); border-radius: 6px; font-size: 0.85rem; font-weight: 600; }
    .property-details-cell { display: flex; gap: 15px; flex-wrap: wrap; }
    .property-details-cell span { display: flex; align-items: center; gap: 5px; font-size: 0.9rem; }
    .property-details-cell i { color: var(--gray-500); }
    @media (max-width: 768px) {
        .properties-grid { grid-template-columns: 1fr; }
        .card-actions { flex-direction: column; width: 100%; }
        .view-toggle { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
function switchView(view) {
    document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
    event.target.closest('.view-btn').classList.add('active');

    if (view === 'grid') {
        document.getElementById('gridView').style.display = 'grid';
        document.getElementById('listView').style.display = 'none';
    } else {
        document.getElementById('gridView').style.display = 'none';
        document.getElementById('listView').style.display = 'block';
    }
}

function filterProperties() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const typeValue = document.getElementById('typeFilter').value;
    const statusValue = document.getElementById('statusFilter').value;

    const gridCards = document.querySelectorAll('.property-card');
    const listRows = document.querySelectorAll('#propertiesTableBody tr');

    [...gridCards, ...listRows].forEach(item => {
        const title = item.querySelector('.property-title, .item-title')?.textContent.toLowerCase() || '';
        const type = item.getAttribute('data-type');
        const status = item.getAttribute('data-status');

        const matchesSearch = title.includes(searchValue);
        const matchesType = !typeValue || type === typeValue;
        const matchesStatus = !statusValue || status === statusValue;

        item.style.display = (matchesSearch && matchesType && matchesStatus) ? '' : 'none';
    });
}

function deleteProperty(id) {
    if (!confirm('Are you sure you want to delete this property?')) return;

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
        console.error('Error:', error);
        alert('An error occurred while deleting the property');
    });
}
</script>
@endpush
@endsection
