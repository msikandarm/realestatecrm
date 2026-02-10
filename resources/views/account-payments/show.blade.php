@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('account-payments.index') }}">Account Payments</a>
        <span class="separator">/</span>
        <span class="current">Details</span>
    </div>
    <h1 class="page-title">Payment Details</h1>
</div>

<div class="details-grid">
    <div class="details-main">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Payment Information</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Payment ID:</span>
                        <span class="info-value">#{{ $payment->id }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Type:</span>
                        <span class="info-value"><span class="type-badge type-{{ $payment->type }}">{{ ucfirst($payment->type) }}</span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date:</span>
                        <span class="info-value">{{ date('F d, Y', strtotime($payment->date)) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Amount:</span>
                        <span class="info-value text-primary"><strong>PKR {{ number_format($payment->amount, 2) }}</strong></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">{{ ucfirst($payment->payment_method) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Reference:</span>
                        <span class="info-value">{{ $payment->reference ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value">{{ $payment->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                @if($payment->notes)
                <div class="section-divider"></div>
                <div class="notes-section">
                    <h4 class="section-subtitle"><i class="fas fa-sticky-note"></i> Notes</h4>
                    <p>{{ $payment->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($payment->payable)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    @if($payment->payable_type == 'App\Models\Dealer')
                        <i class="fas fa-user-tie"></i> Dealer Details
                    @elseif($payment->payable_type == 'App\Models\Client')
                        <i class="fas fa-user"></i> Client Details
                    @else
                        <i class="fas fa-user-circle"></i> Payee Details
                    @endif
                </h3>
            </div>
            <div class="card-body">
                <div class="entity-card">
                    <div class="entity-avatar">
                        @if($payment->payable_type == 'App\Models\Dealer')
                            <i class="fas fa-user-tie"></i>
                        @else
                            <i class="fas fa-user"></i>
                        @endif
                    </div>
                    <div class="entity-info">
                        <h4 class="entity-name">{{ $payment->payable->name ?? 'N/A' }}</h4>
                        @if($payment->payable->email)
                            <p class="entity-detail"><i class="fas fa-envelope"></i> {{ $payment->payable->email }}</p>
                        @endif
                        @if($payment->payable->phone)
                            <p class="entity-detail"><i class="fas fa-phone"></i> {{ $payment->payable->phone }}</p>
                        @endif
                        @if($payment->payable_type == 'App\Models\Dealer' && isset($payment->payable->commission_rate))
                            <p class="entity-detail"><i class="fas fa-percentage"></i> Commission Rate: {{ $payment->payable->commission_rate }}%</p>
                        @endif
                    </div>
                </div>
                <div class="entity-actions">
                    @if($payment->payable_type == 'App\Models\Dealer')
                        <a href="{{ route('dealers.show', $payment->payable) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt"></i> View Dealer Profile
                        </a>
                    @elseif($payment->payable_type == 'App\Models\Client')
                        <a href="{{ route('clients.show', $payment->payable) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt"></i> View Client Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="details-sidebar">
        <div class="card">
            <div class="card-body">
                <div class="status-display">
                    <div class="status-icon status-icon-{{ $payment->status }}">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="status-text">
                        <span class="status-label">Payment Status</span>
                        <span class="status-value">{{ ucfirst($payment->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="sidebar-title"><i class="fas fa-info-circle"></i> Payment Summary</h4>
                <div class="sidebar-stats">
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Type:</span>
                        <span class="sidebar-stat-value">{{ ucfirst($payment->type) }}</span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Amount:</span>
                        <span class="sidebar-stat-value text-primary">PKR {{ number_format($payment->amount) }}</span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Method:</span>
                        <span class="sidebar-stat-value">{{ ucfirst($payment->payment_method) }}</span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Date:</span>
                        <span class="sidebar-stat-value">{{ date('M d, Y', strtotime($payment->date)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="sidebar-title"><i class="fas fa-link"></i> Quick Links</h4>
                <div class="sidebar-links">
                    <a href="{{ route('account-payments.index') }}" class="sidebar-link">
                        <i class="fas fa-list"></i> All Payments
                    </a>
                    @can('payments.create')
                    <a href="{{ route('account-payments.create') }}" class="sidebar-link">
                        <i class="fas fa-plus"></i> Add New Payment
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .type-badge { display: inline-flex; padding: 6px 12px; border-radius: 16px; font-size: 0.875rem; font-weight: 600; }
    .type-commission { background: #dbeafe; color: #1e40af; }
    .type-refund { background: #fee2e2; color: #991b1b; }
    .type-salary { background: #d1fae5; color: #065f46; }
    .type-other { background: #f3f4f6; color: #6b7280; }
    .status-badge { display: inline-flex; padding: 6px 12px; border-radius: 16px; font-size: 0.875rem; font-weight: 600; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .entity-card { display: flex; align-items: center; gap: 20px; padding: 20px; background: #f9fafb; border-radius: 8px; margin-bottom: 15px; }
    .entity-avatar { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; flex-shrink: 0; }
    .entity-info { flex: 1; }
    .entity-name { margin: 0 0 10px 0; font-size: 1.125rem; font-weight: 600; color: #111827; }
    .entity-detail { margin: 5px 0; font-size: 0.875rem; color: #6b7280; display: flex; align-items: center; gap: 8px; }
    .entity-detail i { color: #667eea; }
    .entity-actions { display: flex; gap: 10px; }
</style>
@endpush
@endsection
