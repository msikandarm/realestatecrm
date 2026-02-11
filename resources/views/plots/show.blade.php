@extends('layouts.app')

@section('title', 'Plot #' . $plot->plot_number)

@section('content')
    <div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('plots.index') }}">Plots</a>
        <span class="separator">/</span>
        <span class="current">Plot #{{ $plot->plot_number }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Plot #{{ $plot->plot_number }}</h1>
            <p class="page-subtitle">
                <i class="fas fa-map-marker-alt"></i>
                {{ $plot->street->name ?? 'N/A' }} | Block: <strong>{{ $plot->street->block->name ?? 'N/A' }}</strong>
            </p>
        </div>
        <div class="header-actions">
            @can('plots.edit')
                <a href="{{ route('plots.edit', $plot) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
            @endcan
            @can('plots.delete')
                <form action="{{ route('plots.destroy', $plot) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this plot?')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>

<div class="details-layout">
    <div class="main-column">
        <div class="stats-grid">
            @include('components.stat-card', [
                'icon' => 'fas fa-th',
                'value' => $plot->area . ' ' . $plot->area_unit,
                'label' => 'Plot Size',
                'bgColor' => 'info'
            ])

            @include('components.stat-card', [
                'icon' => 'fas fa-money-bill-wave',
                'value' => 'PKR ' . number_format($plot->total_price ?? 0),
                'label' => 'Price',
                'bgColor' => 'success'
            ])

            @include('components.stat-card', [
                'icon' => 'fas fa-tag',
                'value' => ucfirst($plot->status),
                'label' => 'Status',
                'bgColor' => 'warning'
            ])

            @if($plot->type)
                @include('components.stat-card', [
                    'icon' => 'fas fa-building',
                    'value' => ucfirst($plot->type),
                    'label' => 'Type',
                    'bgColor' => 'primary'
                ])
            @endif
        </div>

        <div class="details-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Plot Information
                </h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Plot Number</span>
                        <span class="info-value">{{ $plot->plot_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Size</span>
                        <span class="info-value">{{ $plot->area }} {{ strtoupper($plot->area_unit) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Price</span>
                        <span class="info-value">PKR {{ number_format($plot->total_price ?? 0) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-{{ $plot->status }}">{{ ucfirst($plot->status) }}</span>
                    </div>
                    @if($plot->type)
                    <div class="info-item">
                        <span class="info-label">Type</span>
                        <span class="info-value">{{ ucfirst($plot->type) }}</span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="info-label">Created</span>
                        <span class="info-value">{{ $plot->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                @if($plot->description)
                <div class="description-section">
                    <h4 class="section-subtitle">Description</h4>
                    <p class="description-text">{{ $plot->description }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($plot->deals && $plot->deals->count() > 0)
        <div class="related-section">
            <div class="section-header-row">
                <h3 class="section-title">
                    <i class="fas fa-handshake"></i> Deal History
                </h3>
            </div>
            <div class="deals-list">
                @foreach($plot->deals as $deal)
                <div class="deal-card">
                    <div class="deal-header">
                        <div>
                            <h4 class="deal-title">Deal #{{ $deal->id }}</h4>
                            <p class="deal-date">{{ $deal->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="status-badge status-{{ $deal->status }}">{{ ucfirst($deal->status) }}</span>
                    </div>
                    <div class="deal-info">
                        <div class="deal-item">
                            <i class="fas fa-user"></i>
                            <span>{{ $deal->client->name ?? 'N/A' }}</span>
                        </div>
                        <div class="deal-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>PKR {{ number_format($deal->amount) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="sidebar-column">
        <div class="sidebar-card">
            <h4 class="sidebar-title">Status</h4>
            <div class="status-display status-{{ $plot->status }}">
                <i class="fas fa-circle"></i> {{ ucfirst($plot->status) }}
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Quick Stats</h4>
                <div class="quick-stats">
                <div class="stat-row">
                    <span class="stat-label">Size</span>
                    <span class="stat-value">{{ $plot->area }} {{ strtoupper($plot->area_unit) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Price</span>
                    <span class="stat-value">PKR {{ number_format($plot->total_price ?? 0) }}</span>
                </div>
                @if($plot->deals)
                <div class="stat-row">
                    <span class="stat-label">Total Deals</span>
                    <span class="stat-value">{{ $plot->deals->count() }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Location</h4>
            <div class="location-hierarchy">
                <div class="location-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <p class="location-type">Street</p>
                        <a href="{{ route('streets.show', $plot->street) }}" class="location-link">
                            {{ $plot->street->name }}
                        </a>
                    </div>
                </div>
                <div class="location-item">
                    <i class="fas fa-th-large"></i>
                    <div>
                        <p class="location-type">Block</p>
                        <a href="{{ route('blocks.show', $plot->street->block) }}" class="location-link">
                            {{ $plot->street->block->name }}
                        </a>
                    </div>
                </div>
                <div class="location-item">
                    <i class="fas fa-building"></i>
                    <div>
                        <p class="location-type">Society</p>
                        <a href="{{ route('societies.show', $plot->street->block->society) }}" class="location-link">
                            {{ $plot->street->block->society->name }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Page layout: left column = page sidebar, right column = main content (keeps global app sidebar on far left) */
    .details-layout { display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
    .main-column { display: flex; flex-direction: column; gap: 20px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .details-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
    .card-header { padding: 25px; border-bottom: 1px solid #e5e7eb; }
    .card-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 10px; }
    .card-body { padding: 30px; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
    .info-item { display: flex; flex-direction: column; gap: 8px; }
    .info-label { font-size: 0.875rem; font-weight: 600; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 1.1rem; font-weight: 600; color: var(--gray-900); }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-available { background: #d1fae5; color: #065f46; }
    .status-reserved { background: #fef3c7; color: #92400e; }
    .status-sold { background: #dbeafe; color: #1e40af; }
    .status-blocked { background: #fee2e2; color: #991b1b; }
    .description-section { margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e7eb; }
    .section-subtitle { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0 0 12px 0; }
    .description-text { color: var(--gray-700); line-height: 1.7; margin: 0; }
    .related-section { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 25px; }
    .section-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .section-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 10px; }
    .deals-list { display: grid; gap: 15px; }
    .deal-card { background: var(--gray-50); border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; }
    .deal-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; }
    .deal-title { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0; }
    .deal-date { font-size: 0.875rem; color: var(--gray-600); margin: 4px 0 0 0; }
    .deal-info { display: flex; gap: 20px; }
    .deal-item { display: flex; align-items: center; gap: 8px; color: var(--gray-700); font-size: 0.95rem; }
    .sidebar-column { display: flex; flex-direction: column; gap: 20px; }
    .sidebar-column .sidebar-card { position: sticky; top: calc(var(--header-height) + 20px); }
    .sidebar-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 20px; }
    .sidebar-title { font-size: 0.95rem; font-weight: 700; color: var(--gray-900); margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-display { padding: 12px 16px; background: var(--gray-100); border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .status-display.status-available { background: #d1fae5; color: #065f46; }
    .status-display.status-reserved { background: #fef3c7; color: #92400e; }
    .status-display.status-sold { background: #dbeafe; color: #1e40af; }
    .status-display.status-blocked { background: #fee2e2; color: #991b1b; }
    .quick-stats { display: flex; flex-direction: column; gap: 12px; }
    .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
    .stat-row:last-child { border-bottom: none; }
    .stat-label { font-size: 0.875rem; color: var(--gray-600); font-weight: 500; }
    .stat-value { font-size: 0.95rem; color: var(--gray-900); font-weight: 700; }
    .location-hierarchy { display: flex; flex-direction: column; gap: 15px; }
    .location-item { display: flex; gap: 12px; align-items: start; }
    .location-item i { width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.875rem; flex-shrink: 0; margin-top: 2px; }
    .location-type { font-size: 0.8rem; color: var(--gray-600); margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .location-link { font-size: 0.95rem; font-weight: 600; color: var(--primary); text-decoration: none; }
    .location-link:hover { text-decoration: underline; }
    @media (max-width: 1024px) {
        .details-layout { grid-template-columns: 1fr; }
        .sidebar-column { order: 2; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .info-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection
