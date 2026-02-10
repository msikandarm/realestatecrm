@extends('layouts.app')

@section('title', 'Street Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('streets.index') }}">Streets</a>
        <span class="separator">/</span>
        <span class="current">{{ $street->name }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">{{ $street->name }}</h1>
            <p class="page-subtitle">
                <i class="fas fa-building"></i> {{ $street->block->name }} |
                <i class="fas fa-city"></i> {{ $street->block->society->name }}
            </p>
        </div>
        <div class="header-actions">
            @can('streets.edit')
                <a href="{{ route('streets.edit', $street) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Street
                </a>
            @endcan
            @can('streets.delete')
                <button onclick="deleteStreet({{ $street->id }})" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
        </div>
    </div>
</div>

<div class="stats-grid" style="margin-bottom: 30px;">
    @include('components.stat-card', [
        'icon' => 'fas fa-th',
        'value' => $street->plots_count ?? 0,
        'label' => 'Total Plots',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-check-square',
        'value' => $street->available_plots ?? 0,
        'label' => 'Available Plots',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-handshake',
        'value' => $street->sold_plots ?? 0,
        'label' => 'Sold Plots',
        'bgColor' => 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-home',
        'value' => $street->properties_count ?? 0,
        'label' => 'Properties',
        'bgColor' => 'primary'
    ])
</div>

<div class="details-layout">
    <div class="main-column">
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Street Information</h3>
                <span class="badge badge-{{ $street->status == 'active' ? 'success' : 'warning' }}">
                    {{ ucfirst($street->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Street Name</label>
                        <value>{{ $street->name }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Street Code</label>
                        <value><span class="code-badge">{{ $street->code }}</span></value>
                    </div>
                    <div class="detail-item">
                        <label>Block</label>
                        <value>
                            <a href="{{ route('blocks.show', $street->block) }}" class="link-primary">
                                <i class="fas fa-building"></i> {{ $street->block->name }}
                            </a>
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Society</label>
                        <value>
                            <a href="{{ route('societies.show', $street->block->society) }}" class="link-primary">
                                <i class="fas fa-city"></i> {{ $street->block->society->name }}
                            </a>
                        </value>
                    </div>
                    @if($street->type)
                        <div class="detail-item">
                            <label>Street Type</label>
                            <value>
                                <span class="badge badge-secondary">{{ ucfirst($street->type) }}</span>
                            </value>
                        </div>
                    @endif
                    @if($street->width)
                        <div class="detail-item">
                            <label>Width</label>
                            <value><strong>{{ $street->width }} feet</strong></value>
                        </div>
                    @endif
                    <div class="detail-item">
                        <label>Created</label>
                        <value>{{ $street->created_at->format('M d, Y') }}</value>
                    </div>
                </div>

                @if($street->description)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-align-left"></i> Description</label>
                        <value>{{ $street->description }}</value>
                    </div>
                @endif
            </div>
        </div>

        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-th"></i> Plots</h3>
                @can('plots.create')
                    <a href="{{ route('plots.create', ['street_id' => $street->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Plot
                    </a>
                @endcan
            </div>
            <div class="card-body">
                @if($street->plots && $street->plots->count() > 0)
                    <div class="plots-grid">
                        @foreach($street->plots as $plot)
                            <div class="plot-card">
                                <div class="plot-header">
                                    <div class="plot-number">{{ $plot->plot_number }}</div>
                                    <span class="badge badge-{{ $plot->status == 'available' ? 'success' : ($plot->status == 'sold' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($plot->status) }}
                                    </span>
                                </div>
                                <div class="plot-details">
                                    <div class="plot-detail">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span>{{ $plot->size }} {{ $plot->unit }}</span>
                                    </div>
                                    @if($plot->price)
                                        <div class="plot-detail">
                                            <i class="fas fa-tag"></i>
                                            <span>PKR {{ number_format($plot->price) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="plot-actions">
                                    <a href="{{ route('plots.show', $plot) }}" class="btn-icon">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('plots.edit')
                                        <a href="{{ route('plots.edit', $plot) }}" class="btn-icon">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-small">
                        <i class="fas fa-th"></i>
                        <p>No plots added yet</p>
                        @can('plots.create')
                            <a href="{{ route('plots.create', ['street_id' => $street->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add First Plot
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="sidebar-column">
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Status</h4>
            </div>
            <div class="sidebar-body">
                <div class="status-item {{ $street->status }}">
                    <i class="fas fa-circle"></i>
                    <span>{{ ucfirst($street->status) }}</span>
                </div>
            </div>
        </div>

        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Quick Stats</h4>
            </div>
            <div class="sidebar-body">
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-th text-success"></i>
                        <span>Total Plots</span>
                    </div>
                    <div class="stat-value">{{ $street->plots_count ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-check-square text-info"></i>
                        <span>Available</span>
                    </div>
                    <div class="stat-value">{{ $street->available_plots ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-handshake text-warning"></i>
                        <span>Sold</span>
                    </div>
                    <div class="stat-value">{{ $street->sold_plots ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-home text-primary"></i>
                        <span>Properties</span>
                    </div>
                    <div class="stat-value">{{ $street->properties_count ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Parent Block</h4>
            </div>
            <div class="sidebar-body">
                <div class="parent-detail">
                    <div class="parent-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="parent-content">
                        <h4>{{ $street->block->name }}</h4>
                        <p>{{ $street->block->code }}</p>
                        <a href="{{ route('blocks.show', $street->block) }}" class="view-link">
                            View Block <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="parent-detail" style="margin-top: 15px;">
                    <div class="parent-icon" style="background: var(--primary);">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="parent-content">
                        <h4>{{ $street->block->society->name }}</h4>
                        <p>{{ $street->block->society->code }}</p>
                        <a href="{{ route('societies.show', $street->block->society) }}" class="view-link">
                            View Society <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .details-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 30px;
    }

    .main-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .details-card,
    .sidebar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .card-header h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body {
        padding: 25px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-item label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-item value {
        font-size: 1rem;
        color: var(--gray-900);
        font-weight: 500;
    }

    .code-badge {
        display: inline-block;
        padding: 4px 12px;
        background: var(--info);
        color: white;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .link-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }

    .link-primary:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .plots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .plot-card {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 15px;
        background: var(--gray-50);
        transition: all 0.3s;
    }

    .plot-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        border-color: var(--primary);
    }

    .plot-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .plot-number {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .plot-details {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 12px 0;
        border-top: 1px solid var(--gray-200);
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 12px;
    }

    .plot-detail {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: var(--gray-700);
    }

    .plot-detail i {
        color: var(--primary);
        width: 16px;
    }

    .plot-actions {
        display: flex;
        gap: 8px;
    }

    .empty-state-small {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-state-small i {
        font-size: 3rem;
        color: var(--gray-400);
        margin-bottom: 15px;
    }

    .empty-state-small p {
        color: var(--gray-600);
        margin-bottom: 15px;
    }

    .sidebar-header {
        padding: 15px 20px;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .sidebar-header h4 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .sidebar-body {
        padding: 20px;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: var(--gray-50);
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .status-item.active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-item.inactive {
        background: #fef3c7;
        color: #92400e;
    }

    .status-item i {
        font-size: 0.7rem;
    }

    .sidebar-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-200);
    }

    .sidebar-stat:last-child {
        border-bottom: none;
    }

    .stat-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: var(--gray-700);
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
    }

    .parent-detail {
        display: flex;
        gap: 15px;
    }

    .parent-icon {
        width: 48px;
        height: 48px;
        background: var(--info);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .parent-content h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 4px 0;
    }

    .parent-content p {
        font-size: 0.85rem;
        color: var(--gray-600);
        margin: 0 0 10px 0;
    }

    .view-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--primary);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: color 0.3s;
    }

    .view-link:hover {
        color: var(--primary-dark);
    }

    @media (max-width: 1024px) {
        .details-layout {
            grid-template-columns: 1fr;
        }

        .header-content {
            flex-direction: column;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions .btn {
            flex: 1;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .plots-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }
</style>
@endpush

@push('scripts')
@can('streets.delete')
<script>
function deleteStreet(id) {
    if (confirm('Are you sure you want to delete this street? This will also delete all plots on this street.')) {
        fetch(`/streets/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) {
                window.location.href = '{{ route("streets.index") }}';
            } else {
                alert('Error: ' + (data.error || 'Unable to delete street'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting street. Please try again.');
        });
    }
}
</script>
@endcan
@endpush
@endsection
