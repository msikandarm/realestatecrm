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
    <div class="table-responsive">
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
            <tbody>
                @foreach($properties as $property)
                <tr>
                    <td>
                        <div class="table-primary">
                            <strong>{{ $property->title }}</strong>
                            <span class="table-secondary">ID: #{{ $property->id }}</span>
                        </div>
                    </td>
                    <td>{{ ucfirst($property->type) }}</td>
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
                        <span class="badge badge-{{ $property->status == 'available' ? 'success' : ($property->status == 'rented' ? 'warning' : 'info') }}">
                            {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                        </span>
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
