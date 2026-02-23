@extends('layouts.app')

@section('title', 'Deal #' . $deal->id)

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <a href="{{ route('deals.index') }}">Deals</a>
        <i class="fas fa-chevron-right"></i>
        <span>Deal #{{ $deal->id }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Deal #{{ $deal->id }}</h1>
            <p class="page-subtitle">
                @if($deal->property)
                    <i class="fas fa-building"></i> {{ $deal->property->title }}
                @elseif($deal->plot)
                    <i class="fas fa-th"></i> Plot #{{ $deal->plot->plot_number }}
                @endif
                | {{ $deal->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="header-actions">
            @can('deals.edit')
                <a href="{{ route('deals.edit', $deal) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Deal
                </a>
            @endcan
            @can('deals.delete')
                <button onclick="deleteDeal({{ $deal->id }})" class="btn btn-danger">
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
        'icon' => 'fas fa-money-bill-wave',
        'value' => 'PKR ' . number_format($deal->amount),
        'label' => 'Deal Amount',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-percentage',
        'value' => 'PKR ' . number_format($deal->commission_amount ?? 0),
        'label' => 'Commission',
        'bgColor' => 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-calendar',
        'value' => $deal->created_at->format('M d, Y'),
        'label' => 'Deal Date',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-user-tie',
        'value' => $deal->dealer->user->name ?? 'N/A',
        'label' => 'Dealer',
        'bgColor' => 'primary'
    ])
</div>

<div class="details-layout">
    <!-- Main Column -->
    <div class="main-column">
        <!-- Deal Information Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Deal Information</h3>
                <span class="badge badge-{{ $deal->status == 'completed' ? 'success' : ($deal->status == 'approved' ? 'info' : ($deal->status == 'pending' ? 'warning' : 'danger')) }}">
                    {{ ucfirst($deal->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Deal ID</label>
                        <value><span class="code-badge">#{{ $deal->id }}</span></value>
                    </div>
                    <div class="detail-item">
                        <label>Deal Type</label>
                        <value>
                            @if($deal->property)
                                <i class="fas fa-building text-primary"></i> Property
                            @elseif($deal->plot)
                                <i class="fas fa-th text-success"></i> Plot
                            @else
                                N/A
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Property/Plot</label>
                        <value>
                            @if($deal->property)
                                {{ $deal->property->title }}
                            @elseif($deal->plot)
                                Plot #{{ $deal->plot->plot_number }}
                            @else
                                N/A
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Client</label>
                        <value><i class="fas fa-user text-primary"></i> {{ $deal->client->name ?? 'N/A' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Dealer</label>
                        <value><i class="fas fa-user-tie text-info"></i> {{ $deal->dealer->user->name ?? 'N/A' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Deal Amount</label>
                        <value class="text-success"><strong>PKR {{ number_format($deal->amount) }}</strong></value>
                    </div>
                    <div class="detail-item">
                        <label>Commission Amount</label>
                        <value class="text-warning"><strong>PKR {{ number_format($deal->commission_amount ?? 0) }}</strong></value>
                    </div>
                    <div class="detail-item">
                        <label>Created Date</label>
                        <value>
                            <i class="fas fa-calendar-alt text-info"></i>
                            {{ $deal->created_at->format('M d, Y h:i A') }}
                        </value>
                    </div>
                </div>

                @if($deal->notes)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-sticky-note"></i> Notes</label>
                        <value>{{ $deal->notes }}</value>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Deal Timeline</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker pending"></div>
                        <div class="timeline-content">
                            <h4 class="timeline-title">Deal Created</h4>
                            <p class="timeline-date">{{ $deal->created_at->format('M d, Y h:i A') }}</p>
                            <p class="timeline-description">Deal initiated by {{ $deal->dealer->user->name ?? 'dealer' }} for {{ $deal->client->name ?? 'client' }}</p>
                        </div>
                    </div>

                    @if($deal->status == 'approved' || $deal->status == 'completed')
                    <div class="timeline-item">
                        <div class="timeline-marker approved"></div>
                        <div class="timeline-content">
                            <h4 class="timeline-title">Deal Approved</h4>
                            <p class="timeline-date">{{ $deal->updated_at->format('M d, Y h:i A') }}</p>
                            <p class="timeline-description">Deal approved and moving forward</p>
                        </div>
                    </div>
                    @endif

                    @if($deal->status == 'completed')
                    <div class="timeline-item">
                        <div class="timeline-marker completed"></div>
                        <div class="timeline-content">
                            <h4 class="timeline-title">Deal Completed</h4>
                            <p class="timeline-date">{{ $deal->updated_at->format('M d, Y h:i A') }}</p>
                            <p class="timeline-description">Transaction completed successfully</p>
                        </div>
                    </div>
                    @endif

                    @if($deal->status == 'cancelled')
                    <div class="timeline-item">
                        <div class="timeline-marker cancelled"></div>
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

    <!-- Sidebar Column -->
    <div class="sidebar-column">
        <!-- Status Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Status</h4>
            </div>
            <div class="sidebar-body">
                <div class="status-item {{ $deal->status }}">
                    <i class="fas fa-circle"></i>
                    <span>{{ ucfirst($deal->status) }}</span>
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
                        <i class="fas fa-money-bill-wave text-success"></i>
                        <span>Amount</span>
                    </div>
                    <div class="stat-value">PKR {{ number_format($deal->amount) }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-percentage text-warning"></i>
                        <span>Commission</span>
                    </div>
                    <div class="stat-value">PKR {{ number_format($deal->commission_amount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Client Details Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Client Details</h4>
            </div>
            <div class="sidebar-body">
                <div class="person-info">
                    <div class="person-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="person-details">
                        <p class="person-name">{{ $deal->client->name ?? 'N/A' }}</p>
                        @if($deal->client && $deal->client->phone)
                        <p class="person-meta"><i class="fas fa-phone"></i> {{ $deal->client->phone }}</p>
                        @endif
                        @if($deal->client && $deal->client->email)
                        <p class="person-meta"><i class="fas fa-envelope"></i> {{ $deal->client->email }}</p>
                        @endif
                    </div>
                </div>
                @if($deal->client)
                <a href="{{ route('clients.show', $deal->client) }}" class="sidebar-link">
                    <i class="fas fa-external-link-alt"></i> View Client Profile
                </a>
                @endif
            </div>
        </div>

        <!-- Dealer Details Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Dealer Details</h4>
            </div>
            <div class="sidebar-body">
                <div class="person-info">
                    <div class="person-avatar dealer">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="person-details">
                        <p class="person-name">{{ $deal->dealer->user->name ?? 'N/A' }}</p>
                        @if($deal->dealer && $deal->dealer->user && $deal->dealer->user->phone)
                        <p class="person-meta"><i class="fas fa-phone"></i> {{ $deal->dealer->user->phone }}</p>
                        @endif
                    </div>
                </div>
                @if($deal->dealer)
                <a href="{{ route('dealers.show', $deal->dealer) }}" class="sidebar-link">
                    <i class="fas fa-external-link-alt"></i> View Dealer Profile
                </a>
                @endif
            </div>
        </div>

        <!-- Quick Actions Card -->
        @if($deal->property || $deal->plot)
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Quick Actions</h4>
            </div>
            <div class="sidebar-body">
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

        <!-- Recent Activity Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Recent Activity</h4>
            </div>
            <div class="sidebar-body">
                <div class="activity-item">
                    <i class="fas fa-plus-circle text-success"></i>
                    <div class="activity-content">
                        <p>Deal created</p>
                        <span class="activity-time">{{ $deal->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @if($deal->updated_at != $deal->created_at)
                    <div class="activity-item">
                        <i class="fas fa-edit text-info"></i>
                        <div class="activity-content">
                            <p>Last updated</p>
                            <span class="activity-time">{{ $deal->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-top: 12px;
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
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
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
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-item value {
        font-size: 0.95rem;
        color: var(--gray-900);
        font-weight: 500;
    }

    .code-badge {
        background: var(--gray-100);
        padding: 4px 8px;
        border-radius: 6px;
        font-family: monospace;
        font-weight: 600;
        color: var(--primary);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-success { background: var(--success); color: white; }
    .badge-warning { background: var(--warning); color: white; }
    .badge-info { background: var(--info); color: white; }
    .badge-primary { background: var(--primary); color: white; }
    .badge-danger { background: var(--danger); color: white; }

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
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-danger {
        background: transparent;
        color: var(--danger);
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .btn-danger:hover {
        background: var(--danger);
        color: white;
    }

    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--gray-200);
    }

    .timeline-item {
        position: relative;
        padding-bottom: 25px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -26px;
        top: 0;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 2px var(--gray-200);
    }

    .timeline-marker.pending { background: var(--warning); box-shadow: 0 0 0 2px var(--warning); }
    .timeline-marker.approved { background: var(--info); box-shadow: 0 0 0 2px var(--info); }
    .timeline-marker.completed { background: var(--success); box-shadow: 0 0 0 2px var(--success); }
    .timeline-marker.cancelled { background: var(--danger); box-shadow: 0 0 0 2px var(--danger); }

    .timeline-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0 0 4px 0;
    }

    .timeline-date {
        font-size: 0.8125rem;
        color: var(--gray-500);
        margin: 0 0 6px 0;
    }

    .timeline-description {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin: 0;
    }

    /* Sidebar Styles */
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
        padding: 12px 15px;
        border-radius: 8px;
        font-weight: 600;
    }

    .status-item.pending { background: #fef3c7; color: #92400e; }
    .status-item.approved { background: #dbeafe; color: #1e40af; }
    .status-item.completed { background: #d1fae5; color: #065f46; }
    .status-item.cancelled { background: #fee2e2; color: #991b1b; }

    .status-item i { font-size: 0.5rem; }

    .sidebar-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .sidebar-stat:last-child {
        border-bottom: none;
    }

    .stat-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .stat-value {
        font-weight: 700;
        color: var(--gray-900);
        font-size: 0.875rem;
    }

    .person-info {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .person-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    .person-avatar.dealer {
        background: linear-gradient(135deg, var(--info), #0284c7);
    }

    .person-name {
        font-weight: 600;
        color: var(--gray-900);
        margin: 0 0 4px 0;
    }

    .person-meta {
        font-size: 0.8125rem;
        color: var(--gray-500);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sidebar-link,
    .action-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px;
        background: var(--gray-50);
        border-radius: 8px;
        color: var(--primary);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .sidebar-link:hover,
    .action-link:hover {
        background: var(--primary);
        color: white;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item i {
        margin-top: 2px;
    }

    .activity-content p {
        font-size: 0.875rem;
        color: var(--gray-900);
        margin: 0;
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .alert {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    @media (max-width: 1024px) {
        .details-layout {
            grid-template-columns: 1fr;
        }

        .sidebar-column {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@can('deals.delete')
<script>
    function deleteDeal(id) {
        if (confirm('Are you sure you want to delete this deal? This action cannot be undone.')) {
            fetch(`/deals/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("deals.index") }}';
                } else {
                    alert(data.message || 'Failed to delete deal');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the deal');
                console.error(error);
            });
        }
    }
</script>
@endcan
@endsection
