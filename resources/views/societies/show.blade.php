@extends('layouts.app')

@section('title', 'Society Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('societies.index') }}">Societies</a>
        <span class="separator">/</span>
        <span class="current">{{ $society->name }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">{{ $society->name }}</h1>
            <p class="page-subtitle">
                <i class="fas fa-map-marker-alt"></i>
                {{ $society->city->name ?? 'N/A' }} | Code: <strong>{{ $society->code }}</strong>
            </p>
        </div>
        <div class="header-actions">
            @can('societies.edit')
                <a href="{{ route('societies.edit', $society) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Society
                </a>
            @endcan
            @can('societies.delete')
                <button onclick="deleteSociety({{ $society->id }})" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="stats-grid" style="margin-bottom: 30px;">
    @include('components.stat-card', [
        'icon' => 'fas fa-building',
        'value' => $society->blocks_count ?? 0,
        'label' => 'Total Blocks',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-road',
        'value' => $society->streets_count ?? 0,
        'label' => 'Total Streets',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-th',
        'value' => $society->plots_count ?? 0,
        'label' => 'Total Plots',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-home',
        'value' => $society->properties_count ?? 0,
        'label' => 'Properties',
        'bgColor' => 'warning'
    ])
</div>

<div class="details-layout">
    <!-- Main Column -->
    <div class="main-column">
        <!-- Basic Information Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                <span class="badge badge-{{ $society->status == 'active' ? 'success' : ($society->status == 'inactive' ? 'warning' : 'info') }}">
                    {{ ucfirst($society->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Society Name</label>
                        <value>{{ $society->name }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Society Code</label>
                        <value><span class="code-badge">{{ $society->code }}</span></value>
                    </div>
                    <div class="detail-item">
                        <label>City</label>
                        <value><i class="fas fa-map-marker-alt text-primary"></i> {{ $society->city->name ?? 'N/A' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Total Area</label>
                        <value>{{ $society->total_area ?? 'Not specified' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Developer</label>
                        <value>{{ $society->developer_name ?? 'Not specified' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Launch Date</label>
                        <value>
                            @if($society->launch_date)
                                <i class="fas fa-calendar-alt text-success"></i>
                                {{ $society->launch_date->format('M d, Y') }}
                            @else
                                Not specified
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Possession Date</label>
                        <value>
                            @if($society->possession_date)
                                <i class="fas fa-calendar-check text-info"></i>
                                {{ $society->possession_date->format('M d, Y') }}
                            @else
                                Not specified
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Created</label>
                        <value>{{ $society->created_at->format('M d, Y') }}</value>
                    </div>
                </div>

                @if($society->address)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-map-marked-alt"></i> Address</label>
                        <value>{{ $society->address }}</value>
                    </div>
                @endif

                @if($society->description)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-align-left"></i> Description</label>
                        <value>{{ $society->description }}</value>
                    </div>
                @endif
            </div>
        </div>

        <!-- Amenities Card -->
        @php
            $amenities = is_array($society->amenities) ? $society->amenities : json_decode($society->amenities ?? '[]', true);
        @endphp
        @if(!empty($amenities))
            <div class="details-card">
                <div class="card-header">
                    <h3><i class="fas fa-star"></i> Amenities & Features</h3>
                    <span class="amenity-count">{{ count($amenities) }} amenities</span>
                </div>
                <div class="card-body">
                    <div class="amenities-display">
                        @php
                            $amenityIcons = [
                                'Electricity' => 'fa-bolt',
                                'Gas' => 'fa-fire',
                                'Water' => 'fa-tint',
                                'Mosque' => 'fa-mosque',
                                'School' => 'fa-school',
                                'Park' => 'fa-tree',
                                'Hospital' => 'fa-hospital',
                                'Shopping Mall' => 'fa-shopping-cart',
                                'Security' => 'fa-shield-alt',
                                'Gated Community' => 'fa-lock',
                                'Gym' => 'fa-dumbbell',
                                'Community Center' => 'fa-users',
                            ];
                        @endphp
                        @foreach($amenities as $amenity)
                            <div class="amenity-tag">
                                <i class="fas {{ $amenityIcons[$amenity] ?? 'fa-check' }}"></i>
                                <span>{{ $amenity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Map & Documents Card -->
        @if($society->map_file || $society->noc_file)
            <div class="details-card">
                <div class="card-header">
                    <h3><i class="fas fa-file-alt"></i> Documents & Map</h3>
                </div>
                <div class="card-body">
                    <div class="documents-grid">
                        @if($society->map_file)
                            <div class="document-item">
                                <div class="document-icon">
                                    <i class="fas fa-map"></i>
                                </div>
                                <div class="document-info">
                                    <h4>Society Map</h4>
                                    <p>Master plan and layout</p>
                                </div>
                                <a href="{{ asset('storage/' . $society->map_file) }}" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        @endif

                        @if($society->noc_file)
                            <div class="document-item">
                                <div class="document-icon noc">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="document-info">
                                    <h4>NOC Document</h4>
                                    <p>No Objection Certificate</p>
                                </div>
                                <a href="{{ asset('storage/' . $society->noc_file) }}" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Blocks List Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-building"></i> Blocks</h3>
                @can('blocks.create')
                    <a href="{{ route('blocks.create', ['society_id' => $society->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Block
                    </a>
                @endcan
            </div>
            <div class="card-body">
                @if($society->blocks && $society->blocks->count() > 0)
                    <div class="blocks-grid">
                        @foreach($society->blocks as $block)
                            <div class="block-card">
                                <div class="block-header">
                                    <div class="block-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="block-info">
                                        <h4>{{ $block->name }}</h4>
                                        <span class="block-code">{{ $block->code }}</span>
                                    </div>
                                    <span class="badge badge-{{ $block->status == 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($block->status) }}
                                    </span>
                                </div>
                                <div class="block-stats">
                                    <div class="block-stat">
                                        <i class="fas fa-road"></i>
                                        <span>{{ $block->streets_count ?? 0 }} Streets</span>
                                    </div>
                                    <div class="block-stat">
                                        <i class="fas fa-th"></i>
                                        <span>{{ $block->plots_count ?? 0 }} Plots</span>
                                    </div>
                                </div>
                                <div class="block-actions">
                                    <a href="{{ route('blocks.show', $block) }}" class="btn-icon">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('blocks.edit')
                                        <a href="{{ route('blocks.edit', $block) }}" class="btn-icon">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-small">
                        <i class="fas fa-building"></i>
                        <p>No blocks added yet</p>
                        @can('blocks.create')
                            <a href="{{ route('blocks.create', ['society_id' => $society->id]) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add First Block
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="sidebar-column">
        <!-- Status Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Status</h4>
            </div>
            <div class="sidebar-body">
                <div class="status-item {{ $society->status }}">
                    <i class="fas fa-circle"></i>
                    <span>{{ ucfirst($society->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Quick Stats</h4>
            </div>
            <div class="sidebar-body">
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-building text-primary"></i>
                        <span>Blocks</span>
                    </div>
                    <div class="stat-value">{{ $society->blocks_count ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-road text-info"></i>
                        <span>Streets</span>
                    </div>
                    <div class="stat-value">{{ $society->streets_count ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-th text-success"></i>
                        <span>Plots</span>
                    </div>
                    <div class="stat-value">{{ $society->plots_count ?? 0 }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-home text-warning"></i>
                        <span>Properties</span>
                    </div>
                    <div class="stat-value">{{ $society->properties_count ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Recent Activity</h4>
            </div>
            <div class="sidebar-body">
                <div class="activity-item">
                    <i class="fas fa-plus-circle text-success"></i>
                    <div class="activity-content">
                        <p>Society created</p>
                        <span class="activity-time">{{ $society->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @if($society->updated_at != $society->created_at)
                    <div class="activity-item">
                        <i class="fas fa-edit text-info"></i>
                        <div class="activity-content">
                            <p>Last updated</p>
                            <span class="activity-time">{{ $society->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endif
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

    .header-left {
        flex: 1;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .page-subtitle {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
        color: var(--gray-600);
        margin-top: 8px;
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

    .card-header i {
        color: var(--primary);
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

    .amenity-count {
        font-size: 0.85rem;
        color: var(--gray-600);
        font-weight: 500;
    }

    .amenities-display {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
    }

    .amenity-tag {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        transition: all 0.3s;
    }

    .amenity-tag i {
        color: var(--primary);
        font-size: 1.1rem;
    }

    .amenity-tag span {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    .amenity-tag:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .documents-grid {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .document-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 18px;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        transition: all 0.3s;
    }

    .document-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .document-icon {
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

    .document-icon.noc {
        background: var(--danger);
    }

    .document-info {
        flex: 1;
    }

    .document-info h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0 0 4px 0;
    }

    .document-info p {
        font-size: 0.85rem;
        color: var(--gray-600);
        margin: 0;
    }

    .blocks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
    }

    .block-card {
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        padding: 18px;
        background: var(--gray-50);
        transition: all 0.3s;
    }

    .block-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .block-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 15px;
    }

    .block-icon {
        width: 40px;
        height: 40px;
        background: var(--primary);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .block-info {
        flex: 1;
        min-width: 0;
    }

    .block-info h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 4px 0;
    }

    .block-code {
        font-size: 0.8rem;
        color: var(--gray-600);
    }

    .block-stats {
        display: flex;
        gap: 15px;
        padding: 12px 0;
        border-top: 1px solid var(--gray-200);
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 12px;
    }

    .block-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: var(--gray-700);
    }

    .block-stat i {
        color: var(--primary);
    }

    .block-actions {
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

    .status-item.upcoming {
        background: #dbeafe;
        color: #1e40af;
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

    .activity-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-200);
    }

    .activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .activity-item:first-child {
        padding-top: 0;
    }

    .activity-item i {
        font-size: 1.1rem;
        margin-top: 2px;
    }

    .activity-content {
        flex: 1;
    }

    .activity-content p {
        font-size: 0.9rem;
        color: var(--gray-700);
        margin: 0 0 4px 0;
    }

    .activity-time {
        font-size: 0.8rem;
        color: var(--gray-500);
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
@can('societies.delete')
<script>
function deleteSociety(id) {
    if (confirm('Are you sure you want to delete this society? This action cannot be undone and will also delete all associated blocks, streets, and plots.')) {
        fetch(`/societies/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) {
                window.location.href = '{{ route("societies.index") }}';
            } else {
                alert('Error deleting society: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting society. Please try again.');
        });
    }
}
</script>
@endcan
@endpush
@endsection
