@extends('layouts.app')

@section('title', 'Account Payments')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Account Payments</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Account Payments</h1>
        @can('payments.create')
        <a href="{{ route('account-payments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Payment
        </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <x-stat-card icon="fas fa-money-bill-wave" :value="'PKR ' . number_format($stats['total'] ?? 0)" label="Total Payments" bgColor="bg-primary" />
    <x-stat-card icon="fas fa-arrow-up" :value="'PKR ' . number_format($stats['paid'] ?? 0)" label="Paid Out" bgColor="bg-danger" />
    <x-stat-card icon="fas fa-arrow-down" :value="'PKR ' . number_format($stats['received'] ?? 0)" label="Received" bgColor="bg-success" />
    <x-stat-card icon="fas fa-calendar" :value="'PKR ' . number_format($stats['this_month'] ?? 0)" label="This Month" bgColor="bg-info" />
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> All Payments</h3>
        <div class="card-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search payments..." onkeyup="filterTable()">
            </div>
            <select id="typeFilter" onchange="filterTable()">
                <option value="">All Types</option>
                <option value="commission">Commission</option>
                <option value="refund">Refund</option>
                <option value="salary">Salary</option>
                <option value="other">Other</option>
            </select>
            <select id="methodFilter" onchange="filterTable()">
                <option value="">All Methods</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="online">Online</option>
            </select>
        </div>
    </div>

    <div class="card-body">
        @if($payments->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-wallet"></i></div>
                <h3 class="empty-title">No Payments Found</h3>
                <p class="empty-description">Start recording account payments</p>
                @can('payments.create')
                <a href="{{ route('account-payments.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add First Payment</a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Payer/Payee</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        @foreach($payments as $payment)
                        <tr data-type="{{ $payment->type }}" data-method="{{ $payment->payment_method }}">
                            <td>{{ date('M d, Y', strtotime($payment->date)) }}</td>
                            <td><span class="type-badge type-{{ $payment->type }}">{{ ucfirst($payment->type) }}</span></td>
                            <td>
                                @if($payment->payable_type == 'App\Models\Dealer')
                                    <i class="fas fa-user-tie text-primary"></i> {{ $payment->payable->name ?? 'N/A' }}
                                @elseif($payment->payable_type == 'App\Models\Client')
                                    <i class="fas fa-user text-success"></i> {{ $payment->payable->name ?? 'N/A' }}
                                @else
                                    <i class="fas fa-user-circle text-info"></i> {{ $payment->payee_name ?? 'N/A' }}
                                @endif
                            </td>
                            <td><strong>PKR {{ number_format($payment->amount) }}</strong></td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td>{{ $payment->reference ?? '-' }}</td>
                            <td><span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('account-payments.show', $payment) }}" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                    @can('payments.delete')
                                    <button onclick="deletePayment({{ $payment->id }})" class="btn-icon text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">{{ $payments->links() }}</div>
        @endif
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
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const typeValue = document.getElementById('typeFilter').value;
    const methodValue = document.getElementById('methodFilter').value;
    const rows = document.querySelectorAll('#paymentsTableBody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const type = row.getAttribute('data-type');
        const method = row.getAttribute('data-method');

        const matchesSearch = text.includes(searchValue);
        const matchesType = !typeValue || type === typeValue;
        const matchesMethod = !methodValue || method === methodValue;

        row.style.display = (matchesSearch && matchesType && matchesMethod) ? '' : 'none';
    });
}

function deletePayment(id) {
    if (!confirm('Delete this payment record?')) return;
    fetch(`/account-payments/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(response => response.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to delete');
    }).catch(() => alert('An error occurred'));
}
</script>
@endpush
@endsection
