@extends('layouts.app')

@section('title', 'Property File Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('files.index') }}">Files</a>
        <span class="separator">/</span>
        <span class="current">File #{{ $file->id }}</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Property File #{{ $file->id }}</h1>
        <div class="action-buttons">
            <a href="{{ route('files.statement', $file) }}" class="btn btn-info"><i class="fas fa-file-invoice"></i> Statement</a>
            @can('files.edit')
            <a href="{{ route('files.edit', $file) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
            @endcan
        </div>
    </div>
</div>

<div class="stats-grid">
    <x-stat-card icon="fas fa-money-bill-wave" :value="'PKR ' . number_format($file->total_amount)" label="Total Amount" bgColor="bg-primary" />
    <x-stat-card icon="fas fa-hand-holding-usd" :value="'PKR ' . number_format($file->paid_amount ?? 0)" label="Paid Amount" bgColor="bg-success" />
    <x-stat-card icon="fas fa-wallet" :value="'PKR ' . number_format($file->total_amount - ($file->paid_amount ?? 0))" label="Balance" bgColor="bg-warning" />
    <x-stat-card icon="fas fa-percentage" :value="number_format((($file->paid_amount ?? 0) / $file->total_amount) * 100, 1) . '%'" label="Completion" bgColor="bg-info" />
</div>

<div class="details-grid">
    <div class="details-main">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> File Information</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">File Number:</span>
                        <span class="info-value">#{{ $file->id }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><span class="status-badge status-{{ $file->status }}">{{ ucfirst($file->status) }}</span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Property/Plot:</span>
                        <span class="info-value">
                            @if($file->property)
                                <i class="fas fa-building text-primary"></i> {{ $file->property->title }}
                            @elseif($file->plot)
                                <i class="fas fa-th text-success"></i> Plot #{{ $file->plot->plot_number }}
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Client:</span>
                        <span class="info-value">{{ $file->client->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value text-primary">PKR {{ number_format($file->total_amount) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Down Payment:</span>
                        <span class="info-value">PKR {{ number_format($file->down_payment) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Installments:</span>
                        <span class="info-value">{{ $file->total_installments }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Per Installment:</span>
                        <span class="info-value text-success">PKR {{ number_format($file->installment_amount) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Frequency:</span>
                        <span class="info-value">{{ ucfirst($file->frequency) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Start Date:</span>
                        <span class="info-value">{{ date('M d, Y', strtotime($file->start_date)) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value">{{ $file->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                @if($file->notes)
                <div class="section-divider"></div>
                <div class="notes-section">
                    <h4 class="section-subtitle"><i class="fas fa-sticky-note"></i> Notes</h4>
                    <p>{{ $file->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Installment Schedule</h3>
            </div>
            <div class="card-body">
                @if($file->installments && $file->installments->isNotEmpty())
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Paid Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($file->installments as $index => $installment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ date('M d, Y', strtotime($installment->due_date)) }}</td>
                                    <td><strong>PKR {{ number_format($installment->amount) }}</strong></td>
                                    <td><span class="status-badge status-{{ $installment->status }}">{{ ucfirst($installment->status) }}</span></td>
                                    <td>{{ $installment->paid_date ? date('M d, Y', strtotime($installment->paid_date)) : '-' }}</td>
                                    <td>
                                        @if($installment->status == 'pending' || $installment->status == 'overdue')
                                            <button class="btn-sm btn-success" onclick="markPaid({{ $installment->id }})"><i class="fas fa-check"></i> Mark Paid</button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state-small">
                        <p>No installment schedule generated yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="details-sidebar">
        <div class="card">
            <div class="card-body">
                <div class="status-display">
                    <div class="status-icon status-icon-{{ $file->status }}">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="status-text">
                        <span class="status-label">Status</span>
                        <span class="status-value">{{ ucfirst($file->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="sidebar-title"><i class="fas fa-chart-line"></i> Payment Progress</h4>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ (($file->paid_amount ?? 0) / $file->total_amount) * 100 }}%"></div>
                    </div>
                    <span class="progress-text">{{ number_format((($file->paid_amount ?? 0) / $file->total_amount) * 100, 1) }}% Complete</span>
                </div>
                <div class="sidebar-stats">
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Paid:</span>
                        <span class="sidebar-stat-value text-success">PKR {{ number_format($file->paid_amount ?? 0) }}</span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Remaining:</span>
                        <span class="sidebar-stat-value text-danger">PKR {{ number_format($file->total_amount - ($file->paid_amount ?? 0)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="sidebar-title"><i class="fas fa-link"></i> Related</h4>
                @if($file->property)
                    <a href="{{ route('properties.show', $file->property) }}" class="sidebar-link">
                        <i class="fas fa-building"></i> View Property
                    </a>
                @elseif($file->plot)
                    <a href="{{ route('plots.show', $file->plot) }}" class="sidebar-link">
                        <i class="fas fa-th"></i> View Plot
                    </a>
                @endif
                <a href="{{ route('files.statement', $file) }}" class="sidebar-link">
                    <i class="fas fa-file-invoice"></i> Print Statement
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .status-badge { display: inline-flex; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-overdue { background: #fee2e2; color: #991b1b; }
    .status-paid { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #dbeafe; color: #1e40af; }
    .status-cancelled { background: #f3f4f6; color: #6b7280; }
    .progress-bar-wrapper { margin: 20px 0; }
    .progress-bar { height: 10px; background: #e5e7eb; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transition: width 0.3s ease; }
    .progress-text { display: block; margin-top: 8px; font-size: 0.875rem; color: #6b7280; }
</style>
@endpush

@push('scripts')
<script>
function markPaid(installmentId) {
    if (!confirm('Mark this installment as paid?')) return;
    fetch(`/installments/${installmentId}/mark-paid`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(response => response.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to update');
    }).catch(() => alert('An error occurred'));
}
</script>
@endpush
@endsection
