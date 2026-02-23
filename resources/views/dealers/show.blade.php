@extends('layouts.app')

@section('title', 'Dealer Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <a href="{{ route('dealers.index') }}">Dealers</a>
        <i class="fas fa-chevron-right"></i>
        <span>{{ $dealer->user->name ?? 'Dealer' }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">{{ $dealer->user->name ?? 'Dealer Details' }}</h1>
            <p class="page-subtitle">
                <i class="fas fa-phone"></i>
                {{ $dealer->user->email ?? 'N/A' }} | Commission: <strong>{{ $dealer->default_commission_rate }}%</strong>
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('dealers.performance', $dealer) }}" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Performance
            </a>
            @can('users.edit')
                <a href="{{ route('dealers.edit', $dealer) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Dealer
                </a>
            @endcan
            @can('users.delete')
                <button onclick="deleteDealer({{ $dealer->id }})" class="btn btn-danger">
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

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-user-check',
        'value' => ucfirst($dealer->status ?? 'Active'),
        'label' => 'Dealer Status',
        'bgColor' => ($dealer->status ?? 'active') === 'active' ? 'success' : 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-handshake',
        'value' => $dealer->deals_count ?? 0,
        'label' => 'Total Deals',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-money-bill-wave',
        'value' => 'PKR ' . number_format($dealer->total_commission ?? 0),
        'label' => 'Total Commission',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-percentage',
        'value' => $dealer->default_commission_rate . '%',
        'label' => 'Commission Rate',
        'bgColor' => 'info'
    ])
</div>

<div class="details-layout">
    <!-- Main Column -->
    <div class="main-column">
        <!-- Basic Information Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Dealer Information</h3>
                <span class="badge badge-{{ ($dealer->status ?? 'active') === 'active' ? 'success' : 'warning' }}">
                    {{ ucfirst($dealer->status ?? 'Active') }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Full Name</label>
                        <value>{{ $dealer->user->name ?? 'N/A' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Email Address</label>
                        <value>
                            @if($dealer->user && $dealer->user->email)
                                <i class="fas fa-envelope text-info"></i> {{ $dealer->user->email }}
                            @else
                                Not provided
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Phone Number</label>
                        <value>
                            @if($dealer->user && $dealer->user->phone)
                                <i class="fas fa-phone text-primary"></i> {{ $dealer->user->phone }}
                            @else
                                Not provided
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>CNIC</label>
                        <value>{{ $dealer->cnic ?? 'Not provided' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Commission Rate</label>
                        <value><span class="code-badge">{{ $dealer->default_commission_rate }}%</span></value>
                    </div>
                    <div class="detail-item">
                        <label>Created Date</label>
                        <value>
                            <i class="fas fa-calendar-alt text-success"></i>
                            {{ $dealer->created_at->format('M d, Y') }}
                        </value>
                    </div>
                </div>

                @if($dealer->address)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-map-marked-alt"></i> Address</label>
                        <value>{{ $dealer->address }}</value>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bank Details Card -->
        @if($dealer->bank_name || $dealer->account_number)
            <div class="details-card">
                <div class="card-header">
                    <h3><i class="fas fa-university"></i> Bank Details</h3>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        @if($dealer->bank_name)
                            <div class="detail-item">
                                <label>Bank Name</label>
                                <value>{{ $dealer->bank_name }}</value>
                            </div>
                        @endif
                        @if($dealer->account_number)
                            <div class="detail-item">
                                <label>Account Number</label>
                                <value>{{ $dealer->account_number }}</value>
                            </div>
                        @endif
                        @if($dealer->account_title)
                            <div class="detail-item">
                                <label>Account Title</label>
                                <value>{{ $dealer->account_title }}</value>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Deals Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-handshake"></i> Recent Deals</h3>
                <span class="badge badge-info">{{ $dealer->deals->count() ?? 0 }} Records</span>
            </div>
            <div class="card-body">
                @if(!$dealer->deals || $dealer->deals->isEmpty())
                    <div class="empty-state-small">
                        <i class="fas fa-handshake"></i>
                        <p>No deals found for this dealer</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Deal ID</th>
                                    <th>Property/Plot</th>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Commission</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dealer->deals->take(10) as $deal)
                                    <tr>
                                        <td>
                                            <a href="{{ route('deals.show', $deal) }}">#{{ $deal->id }}</a>
                                        </td>
                                        <td>{{ $deal->property->title ?? $deal->plot->plot_number ?? 'N/A' }}</td>
                                        <td>{{ $deal->client->name ?? 'N/A' }}</td>
                                        <td>PKR {{ number_format($deal->amount ?? $deal->deal_amount ?? 0) }}</td>
                                        <td><strong class="text-success">PKR {{ number_format($deal->commission_amount ?? 0) }}</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $deal->status === 'completed' ? 'success' : ($deal->status === 'pending' ? 'warning' : 'info') }}">
                                                {{ ucfirst($deal->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $deal->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($dealer->deals->count() > 10)
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="{{ route('deals.index', ['dealer_id' => $dealer->id]) }}" class="btn btn-light btn-sm">
                                View All {{ $dealer->deals->count() }} Deals
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="sidebar-column">
        <!-- Quick Stats Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Quick Stats</h3>
            </div>
            <div class="card-body">
                <div class="quick-stats">
                    <div class="stat-row">
                        <span class="stat-label">Total Deals</span>
                        <span class="stat-value text-primary">{{ $dealer->deals_count ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Completed Deals</span>
                        <span class="stat-value text-success">{{ $dealer->completed_deals ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Total Commission</span>
                        <span class="stat-value text-success">PKR {{ number_format($dealer->total_commission ?? 0) }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Commission Rate</span>
                        <span class="stat-value text-info">{{ $dealer->default_commission_rate }}%</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Joined</span>
                        <span class="stat-value">{{ $dealer->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="{{ route('dealers.performance', $dealer) }}" class="action-btn">
                        <i class="fas fa-chart-line"></i>
                        <span>View Performance</span>
                    </a>
                    @can('deals.create')
                        <a href="{{ route('deals.create', ['dealer_id' => $dealer->id]) }}" class="action-btn">
                            <i class="fas fa-handshake"></i>
                            <span>New Deal</span>
                        </a>
                    @endcan
                    @can('users.edit')
                        <a href="{{ route('dealers.edit', $dealer) }}" class="action-btn">
                            <i class="fas fa-edit"></i>
                            <span>Edit Dealer</span>
                        </a>
                    @endcan
                    @if($dealer->user && $dealer->user->phone)
                        <a href="tel:{{ $dealer->user->phone }}" class="action-btn">
                            <i class="fas fa-phone"></i>
                            <span>Call Dealer</span>
                        </a>
                    @endif
                    @if($dealer->user && $dealer->user->email)
                        <a href="mailto:{{ $dealer->user->email }}" class="action-btn">
                            <i class="fas fa-envelope"></i>
                            <span>Email Dealer</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .details-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .details-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .details-card .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .details-card .card-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-800);
    }

    .details-card .card-header h3 i {
        margin-right: 8px;
        color: var(--primary);
    }

    .details-card .card-body {
        padding: 20px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detail-item.full-width {
        grid-column: span 2;
    }

    .detail-item label {
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-item value {
        font-size: 14px;
        color: var(--gray-800);
        font-weight: 500;
    }

    .code-badge {
        display: inline-block;
        padding: 4px 10px;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-700);
    }

    .quick-stats .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .quick-stats .stat-row:last-child {
        border-bottom: none;
    }

    .quick-stats .stat-label {
        font-size: 13px;
        color: var(--gray-600);
    }

    .quick-stats .stat-value {
        font-size: 14px;
        font-weight: 600;
    }

    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 8px;
        color: var(--gray-700);
        text-decoration: none;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: var(--primary);
        color: white;
    }

    .action-btn i {
        width: 20px;
        text-align: center;
    }

    .empty-state-small {
        text-align: center;
        padding: 30px 20px;
        color: var(--gray-500);
    }

    .empty-state-small i {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-state-small p {
        margin: 0;
        font-size: 14px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .data-table th {
        font-size: 12px;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        background: #f9fafb;
    }

    .data-table td {
        font-size: 14px;
        color: var(--gray-700);
    }

    .data-table a {
        color: var(--primary);
        text-decoration: none;
    }

    .data-table a:hover {
        text-decoration: underline;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success { background: var(--success); color: white; }
    .badge-warning { background: var(--warning); color: white; }
    .badge-info { background: var(--info); color: white; }
    .badge-primary { background: var(--primary); color: white; }

    .text-success { color: var(--success); }
    .text-primary { color: var(--primary); }
    .text-info { color: var(--info); }

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

    .btn-info {
        background: var(--info);
        color: white;
    }

    .btn-danger {
        background: var(--danger);
        color: white;
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

    @media (max-width: 1024px) {
        .details-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-item.full-width {
            grid-column: span 1;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@can('users.delete')
<script>
    function deleteDealer(id) {
        if (confirm('Are you sure you want to delete this dealer? This action cannot be undone.')) {
            fetch(`/dealers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("dealers.index") }}';
                } else {
                    alert(data.message || 'Failed to delete dealer');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the dealer');
                console.error(error);
            });
        }
    }
</script>
@endcan
@endsection
