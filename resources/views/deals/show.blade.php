@extends('layouts.app')

@section('title', 'Deal #' . $deal->id)

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('deals.index') }}">Deals</a>
        <span class="separator">/</span>
        <span class="current">Deal #{{ $deal->id }}</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Deal #{{ $deal->id }}</h1>
        <div class="actions-group">
            @can('deals.edit')
            <a href="{{ route('deals.edit', $deal) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            @can('deals.delete')
            <form action="{{ route('deals.destroy', $deal) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this deal?')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>

<div class="content-layout">
    <div class="main-content">
        <div class="stats-grid">
            <x-stat-card
                icon="fas fa-money-bill-wave"
                value="PKR {{ number_format($deal->amount) }}"
                label="Deal Amount"
                bgColor="bg-success"
            />
            <x-stat-card
                icon="fas fa-percentage"
                value="PKR {{ number_format($deal->commission_amount ?? 0) }}"
                label="Commission"
                bgColor="bg-warning"
            />
            <x-stat-card
                icon="fas fa-calendar"
                value="{{ $deal->created_at->format('M d, Y') }}"
                label="Deal Date"
                bgColor="bg-info"
            />
            <x-stat-card
                icon="fas fa-user"
                value="{{ $deal->dealer->user->name ?? 'N/A' }}"
                label="Dealer"
                bgColor="bg-purple"
            />
        </div>

        <div class="details-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Deal Information
                </h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Deal ID</span>
                        <span class="info-value">#{{ $deal->id }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-{{ $deal->status }}">{{ ucfirst($deal->status) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Property/Plot</span>
                        <span class="info-value">
                            @if($deal->property)
                                <i class="fas fa-building"></i> {{ $deal->property->title }}
                            @elseif($deal->plot)
                                <i class="fas fa-th"></i> Plot #{{ $deal->plot->plot_number }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Client</span>
                        <span class="info-value">{{ $deal->client->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Dealer</span>
                        <span class="info-value">{{ $deal->dealer->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Deal Amount</span>
                        <span class="info-value">PKR {{ number_format($deal->amount) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Commission</span>
                        <span class="info-value">PKR {{ number_format($deal->commission_amount ?? 0) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created Date</span>
                        <span class="info-value">{{ $deal->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                @if($deal->notes)
                <div class="notes-section">
                    <h4 class="section-subtitle">Notes</h4>
                    <p class="notes-text">{{ $deal->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="timeline-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> Deal Timeline
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker status-pending"></div>
                        <div class="timeline-content">
                            <h4 class="timeline-title">Deal Created</h4>
                            <p class="timeline-date">{{ $deal->created_at->format('M d, Y h:i A') }}</p>
                            <p class="timeline-description">Deal initiated by {{ $deal->dealer->user->name ?? 'dealer' }} for {{ $deal->client->name ?? 'client' }}</p>
                        </div>
                    </div>

                    @if($deal->status == 'approved' || $deal->status == 'completed')
                    <div class="timeline-item">
                        <div class="timeline-marker status-approved"></div>
                        <div class="timeline-content">
                            <h4 class="timeline-title">Deal Approved</h4>
                            <p class="timeline-date">{{ $deal->updated_at->format('M d, Y h:i A') }}</p>
                            <p class="timeline-description">Deal approved and moving forward</p>
                        </div>
                    </div>
                    @endif

                    @if($deal->status == 'completed')
                    <div class="timeline-item">
                        <div class="timeline-marker status-completed"></div>
                        <div class="timeline-content">
                            <h4 class="timeline-title">Deal Completed</h4>
                            <p class="timeline-date">{{ $deal->updated_at->format('M d, Y h:i A') }}</p>
                            <p class="timeline-description">Transaction completed successfully</p>
                        </div>
                    </div>
                    @endif

                    @if($deal->status == 'cancelled')
                    <div class="timeline-item">
                        <div class="timeline-marker status-cancelled"></div>
                        <div class="timeline-content">
                            <h4 class="timeline-title">Deal Cancelled</h4>
                            <p class="timeline-date">{{ $deal->updated_at->format('M d, Y h:i A') }}</p>
                            <p class="timeline-description">Deal was cancelled</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-card">
            <h4 class="sidebar-title">Status</h4>
            <div class="status-display status-{{ $deal->status }}">
                <i class="fas fa-circle"></i> {{ ucfirst($deal->status) }}
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Client Details</h4>
            <div class="client-info">
                <div class="client-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="client-name">{{ $deal->client->name ?? 'N/A' }}</p>
                    @if($deal->client && $deal->client->phone)
                    <p class="client-phone"><i class="fas fa-phone"></i> {{ $deal->client->phone }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Dealer Details</h4>
            <div class="dealer-info">
                <div class="dealer-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <p class="dealer-name">{{ $deal->dealer->user->name ?? 'N/A' }}</p>
                    @if($deal->dealer && $deal->dealer->phone)
                    <p class="dealer-phone"><i class="fas fa-phone"></i> {{ $deal->dealer->phone }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if($deal->property || $deal->plot)
        <div class="sidebar-card">
            <h4 class="sidebar-title">Property Details</h4>
            <div class="property-link">
                @if($deal->property)
                    <a href="{{ route('properties.show', $deal->property) }}" class="action-link">
                        <i class="fas fa-building"></i> View Property
                    </a>
                @elseif($deal->plot)
                    <a href="{{ route('plots.show', $deal->plot) }}" class="action-link">
                        <i class="fas fa-th"></i> View Plot
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .content-layout { display: grid; grid-template-columns: 1fr 320px; gap: 25px; }
    .main-content { display: flex; flex-direction: column; gap: 25px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .details-card, .timeline-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
    .card-header { padding: 25px; border-bottom: 1px solid #e5e7eb; }
    .card-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 10px; }
    .card-body { padding: 30px; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
    .info-item { display: flex; flex-direction: column; gap: 8px; }
    .info-label { font-size: 0.875rem; font-weight: 600; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 1.1rem; font-weight: 600; color: var(--gray-900); }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .notes-section { margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e7eb; }
    .section-subtitle { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0 0 12px 0; }
    .notes-text { color: var(--gray-700); line-height: 1.7; margin: 0; }
    .timeline { position: relative; padding-left: 40px; }
    .timeline-item { position: relative; padding-bottom: 30px; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-item::before { content: ''; position: absolute; left: -29px; top: 30px; bottom: -10px; width: 2px; background: #e5e7eb; }
    .timeline-item:last-child::before { display: none; }
    .timeline-marker { position: absolute; left: -36px; top: 0; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px #e5e7eb; }
    .timeline-marker.status-pending { background: #f59e0b; }
    .timeline-marker.status-approved { background: #3b82f6; }
    .timeline-marker.status-completed { background: #10b981; }
    .timeline-marker.status-cancelled { background: #ef4444; }
    .timeline-title { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0 0 4px 0; }
    .timeline-date { font-size: 0.875rem; color: var(--gray-600); margin: 0 0 8px 0; }
    .timeline-description { font-size: 0.95rem; color: var(--gray-700); margin: 0; }
    .sidebar { display: flex; flex-direction: column; gap: 20px; }
    .sidebar-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 20px; }
    .sidebar-title { font-size: 0.95rem; font-weight: 700; color: var(--gray-900); margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-display { padding: 12px 16px; background: var(--gray-100); border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .client-info, .dealer-info { display: flex; gap: 12px; align-items: center; }
    .client-avatar, .dealer-avatar { width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
    .client-name, .dealer-name { font-weight: 600; color: var(--gray-900); margin: 0 0 4px 0; }
    .client-phone, .dealer-phone { font-size: 0.9rem; color: var(--gray-600); margin: 0; display: flex; align-items: center; gap: 6px; }
    .action-link { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--gray-50); border-radius: 8px; color: var(--gray-700); text-decoration: none; transition: all 0.3s; font-size: 0.9rem; font-weight: 500; }
    .action-link:hover { background: var(--primary); color: white; }
    @media (max-width: 1024px) {
        .content-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .info-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
@endsection
