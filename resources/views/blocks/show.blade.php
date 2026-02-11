@extends('layouts.app')

@section('title', 'Block Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('blocks.index') }}">Blocks</a>
        <span class="separator">/</span>
        <span class="current">{{ $block->name }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">{{ $block->name }}</h1>
            <p class="page-subtitle">
                <i class="fas fa-city"></i>
                {{ $block->society->name ?? 'N/A' }} | Code: <strong>{{ $block->code }}</strong>
            </p>
        </div>
        <div class="header-actions">
            @can('blocks.edit')
                <a href="{{ route('blocks.edit', $block) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Block
                </a>
            @endcan
            @can('blocks.delete')
                <button onclick="deleteBlock({{ $block->id }})" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
        </div>
    </div>
</div>

<div class="stats-grid" style="margin-bottom: 30px;">
    @include('components.stat-card', [
        'icon' => 'fas fa-road',
        'value' => $block->streets_count ?? 0,
        'label' => 'Total Streets',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-th',
        'value' => $block->plots_count ?? 0,
        'label' => 'Total Plots',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-home',
        'value' => $block->properties_count ?? 0,
        'label' => 'Properties',
        'bgColor' => 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-handshake',
        'value' => $block->deals_count ?? 0,
        'label' => 'Total Deals',
        'bgColor' => 'primary'
    ])
</div>

<div class="details-layout">
    <div class="main-column">
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Block Information</h3>
                <span class="badge badge-{{ $block->status == 'active' ? 'success' : 'warning' }}">
                    {{ ucfirst($block->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Block Name</label>
                        <value>{{ $block->name }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Block Code</label>
                        <value><span class="code-badge">{{ $block->code }}</span></value>
                    </div>
                    <div class="detail-item">
                        <label>Society</label>
                        <value>
                            <a href="{{ route('societies.show', $block->society) }}" class="society-link">
                                <i class="fas fa-city"></i> {{ $block->society->name }}
                            </a>
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Created</label>
                        <value>{{ $block->created_at->format('M d, Y') }}</value>
                    </div>
                </div>

                @if($block->description)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-align-left"></i> Description</label>
                        <value>{{ $block->description }}</value>
                    </div>
                @endif
            </div>
        </div>

        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-road"></i> Streets</h3>
                @can('streets.create')
                    <a href="{{ route('streets.create', ['block_id' => $block->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Street
                    </a>
                @endcan
            </div>
            <div class="card-body">
                @if($block->streets && $block->streets->count() > 0)
                    <div class="streets-grid">
                        @foreach($block->streets as $street)
                            <div class="street-card">
                                <div class="street-header">
                                    <div class="street-icon">
                                        <i class="fas fa-road"></i>
                                    </div>
                                    <div class="street-info">
                                        <h4>{{ $street->name }}</h4>
                                        <span class="street-code">{{ $street->code }}</span>
                                    </div>
                                    <span class="badge badge-{{ $street->status == 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($street->status) }}
                                    </span>
                                </div>
                                <div class="street-stats">
                                    <div class="street-stat">
                                        <i class="fas fa-th"></i>
                                        <span>{{ $street->plots_count ?? 0 }} Plots</span>
                                    </div>
                                    @if($street->type)
                                        <div class="street-stat">
                                            <i class="fas fa-tag"></i>
                                            <span>{{ ucfirst($street->type) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="street-actions">
                                    <a href="{{ route('streets.show', $street) }}" class="btn-icon">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('streets.edit')
                                        <a href="{{ route('streets.edit', $street) }}" class="btn-icon">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-small">
                        <i class="fas fa-road"></i>
                        <p>No streets added yet</p>
                        @can('streets.create')
                            <a href="{{ route('streets.create', ['block_id' => $block->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add First Street
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
                <div class="status-item {{ $block->status }}">
                    <i class="fas fa-circle"></i>
                    <span>{{ ucfirst($block->status) }}</span>
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
                        <i class="fas fa-road text-info"></i>
                        <span>Streets</span>
                    </div>
                    <div class="stat-value">{{ $block->streets_count ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-th text-success"></i>
                        <span>Plots</span>
                    </div>
                    <div class="stat-value">{{ $block->plots_count ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-home text-warning"></i>
                        <span>Properties</span>
                    </div>
                    <div class="stat-value">{{ $block->properties_count ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-handshake text-primary"></i>
                        <span>Deals</span>
                    </div>
                    <div class="stat-value">{{ $block->deals_count ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Parent Society</h4>
            </div>
            <div class="sidebar-body">
                <div class="society-detail">
                    <div class="society-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="society-content">
                        <h4>{{ $block->society->name }}</h4>
                        <p>{{ $block->society->code }}</p>
                        <a href="{{ route('societies.show', $block->society) }}" class="view-link">
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
    /* Match Societies show compact stats and header spacing */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        padding: 14px 18px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(16,24,40,0.04);
    }

    .stat-icon { width: 56px; height: 56px; display:flex; align-items:center; justify-content:center; }
    .stat-icon-inner { width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; }
    .stat-value { font-size: 1.5rem; }

    .page-title { font-size: 1.9rem; font-weight: 800; }
    .page-subtitle { font-size: 0.95rem; color: var(--gray-600); }
    .header-actions .btn { margin-left: 8px; }

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
        background: var(--primary);
        color: white;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .society-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }

    .society-link:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .streets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
    }

    .street-card {
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        padding: 18px;
        background: var(--gray-50);
        transition: all 0.3s;
    }

    .street-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .street-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 15px;
    }

    .street-icon {
        width: 40px;
        height: 40px;
        background: var(--info);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .street-info {
        flex: 1;
        min-width: 0;
    }

    .street-info h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 4px 0;
    }

    .street-code {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    .street-stats {
        display: flex;
        gap: 15px;
        padding: 12px 0;
        border-top: 1px solid var(--gray-200);
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 12px;
    }

    .street-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: var(--gray-700);
    }

    .street-stat i {
        color: var(--info);
    }

    .street-actions {
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

    .society-detail {
        display: flex;
        gap: 15px;
    }

    .society-icon {
        width: 48px;
        height: 48px;
        background: var(--primary);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .society-content h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 4px 0;
    }

    .society-content p {
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
    }
</style>
@endpush

@push('scripts')
@can('blocks.delete')
<script>
function deleteBlock(id) {
    if (confirm('Are you sure you want to delete this block? This will also delete all streets and plots in this block.')) {
        fetch(`/blocks/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) {
                window.location.href = '{{ route("blocks.index") }}';
            } else {
                alert('Error: ' + (data.error || 'Unable to delete block'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting block. Please try again.');
        });
    }
}
</script>
@endcan
@endpush
@endsection
