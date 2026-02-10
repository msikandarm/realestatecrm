@extends('layouts.app')

@section('title', 'Dealer Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('dealers.index') }}">Dealers</a>
        <span class="separator">/</span>
        <span class="current">{{ $dealer->user->name ?? 'Dealer' }}</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">{{ $dealer->user->name ?? 'Dealer Details' }}</h1>
        <div class="actions-group">
            <a href="{{ route('dealers.performance', $dealer) }}" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Performance
            </a>
            @can('users.edit')
            <a href="{{ route('dealers.edit', $dealer) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            @can('users.delete')
            <form action="{{ route('dealers.destroy', $dealer) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this dealer?')">
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
                icon="fas fa-handshake"
                value="{{ $dealer->deals_count ?? 0 }}"
                label="Total Deals"
                bgColor="bg-primary"
            />
            <x-stat-card
                icon="fas fa-money-bill-wave"
                value="PKR {{ number_format($dealer->total_commission ?? 0) }}"
                label="Total Commission"
                bgColor="bg-success"
            />
            <x-stat-card
                icon="fas fa-percentage"
                value="{{ $dealer->commission_rate }}%"
                label="Commission Rate"
                bgColor="bg-warning"
            />
            <x-stat-card
                icon="fas fa-check-circle"
                value="{{ $dealer->completed_deals ?? 0 }}"
                label="Completed Deals"
                bgColor="bg-info"
            />
        </div>

        <div class="details-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Dealer Information
                </h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value">{{ $dealer->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $dealer->user->email ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value">{{ $dealer->phone }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">CNIC</span>
                        <span class="info-value">{{ $dealer->cnic }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Commission Rate</span>
                        <span class="info-value">{{ $dealer->commission_rate }}%</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-{{ $dealer->status }}">{{ ucfirst($dealer->status) }}</span>
                    </div>
                    @if($dealer->address)
                    <div class="info-item full-width">
                        <span class="info-label">Address</span>
                        <span class="info-value">{{ $dealer->address }}</span>
                    </div>
                    @endif
                </div>

                @if($dealer->bank_name || $dealer->account_number)
                <div class="bank-section">
                    <h4 class="section-subtitle">Bank Details</h4>
                    <div class="info-grid">
                        @if($dealer->bank_name)
                        <div class="info-item">
                            <span class="info-label">Bank Name</span>
                            <span class="info-value">{{ $dealer->bank_name }}</span>
                        </div>
                        @endif
                        @if($dealer->account_number)
                        <div class="info-item">
                            <span class="info-label">Account Number</span>
                            <span class="info-value">{{ $dealer->account_number }}</span>
                        </div>
                        @endif
                        @if($dealer->account_title)
                        <div class="info-item">
                            <span class="info-label">Account Title</span>
                            <span class="info-value">{{ $dealer->account_title }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($dealer->deals && $dealer->deals->count() > 0)
        <div class="related-section">
            <div class="section-header-row">
                <h3 class="section-title">
                    <i class="fas fa-handshake"></i> Recent Deals
                </h3>
            </div>
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
                            <td>#{{ $deal->id }}</td>
                            <td>{{ $deal->property->title ?? $deal->plot->plot_number ?? 'N/A' }}</td>
                            <td>{{ $deal->client->name ?? 'N/A' }}</td>
                            <td>PKR {{ number_format($deal->amount) }}</td>
                            <td><strong class="text-success">PKR {{ number_format($deal->commission_amount ?? 0) }}</strong></td>
                            <td><span class="status-badge status-{{ $deal->status }}">{{ ucfirst($deal->status) }}</span></td>
                            <td>{{ $deal->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="sidebar">
        <div class="sidebar-card">
            <h4 class="sidebar-title">Status</h4>
            <div class="status-display status-{{ $dealer->status }}">
                <i class="fas fa-circle"></i> {{ ucfirst($dealer->status) }}
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Performance Summary</h4>
            <div class="quick-stats">
                <div class="stat-row">
                    <span class="stat-label">Total Deals</span>
                    <span class="stat-value">{{ $dealer->deals_count ?? 0 }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Total Commission</span>
                    <span class="stat-value">PKR {{ number_format($dealer->total_commission ?? 0) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Commission Rate</span>
                    <span class="stat-value">{{ $dealer->commission_rate }}%</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Joined</span>
                    <span class="stat-value">{{ $dealer->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Quick Actions</h4>
            <div class="quick-actions">
                <a href="{{ route('dealers.performance', $dealer) }}" class="action-link">
                    <i class="fas fa-chart-line"></i> View Performance
                </a>
                @can('users.edit')
                <a href="{{ route('dealers.edit', $dealer) }}" class="action-link">
                    <i class="fas fa-edit"></i> Edit Dealer
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .content-layout { display: grid; grid-template-columns: 1fr 320px; gap: 25px; }
    .main-content { display: flex; flex-direction: column; gap: 25px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .details-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
    .card-header { padding: 25px; border-bottom: 1px solid #e5e7eb; }
    .card-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 10px; }
    .card-body { padding: 30px; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
    .info-item { display: flex; flex-direction: column; gap: 8px; }
    .info-item.full-width { grid-column: 1 / -1; }
    .info-label { font-size: 0.875rem; font-weight: 600; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 1.1rem; font-weight: 600; color: var(--gray-900); }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-completed { background: #dbeafe; color: #1e40af; }
    .bank-section { margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e7eb; }
    .section-subtitle { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0 0 20px 0; }
    .related-section { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 25px; }
    .section-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .section-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 10px; }
    .sidebar { display: flex; flex-direction: column; gap: 20px; }
    .sidebar-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 20px; }
    .sidebar-title { font-size: 0.95rem; font-weight: 700; color: var(--gray-900); margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-display { padding: 12px 16px; background: var(--gray-100); border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .quick-stats { display: flex; flex-direction: column; gap: 12px; }
    .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
    .stat-row:last-child { border-bottom: none; }
    .stat-label { font-size: 0.875rem; color: var(--gray-600); font-weight: 500; }
    .stat-value { font-size: 0.95rem; color: var(--gray-900); font-weight: 700; }
    .quick-actions { display: flex; flex-direction: column; gap: 10px; }
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
