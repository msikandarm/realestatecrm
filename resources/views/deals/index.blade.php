@extends('layouts.app')

@section('title', 'Deals')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Deals</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Deals</h1>
        @can('deals.create')
        <a href="{{ route('deals.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Deal
        </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <x-stat-card
        icon="fas fa-handshake"
        value="{{ $stats['total'] ?? 0 }}"
        label="Total Deals"
        bgColor="bg-primary"
    />
    <x-stat-card
        icon="fas fa-clock"
        value="{{ $stats['pending'] ?? 0 }}"
        label="Pending"
        bgColor="bg-warning"
    />
    <x-stat-card
        icon="fas fa-check-circle"
        value="{{ $stats['completed'] ?? 0 }}"
        label="Completed"
        bgColor="bg-success"
    />
    <x-stat-card
        icon="fas fa-money-bill-wave"
        value="PKR {{ number_format($stats['total_amount'] ?? 0) }}"
        label="Total Value"
        bgColor="bg-info"
    />
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> All Deals
        </h3>
        <div class="card-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search deals..." onkeyup="filterTable()">
            </div>
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div class="card-body">
        @if($deals->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 class="empty-title">No Deals Found</h3>
                <p class="empty-description">Start creating deals to track your transactions</p>
                @can('deals.create')
                <a href="{{ route('deals.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Deal
                </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Deal ID</th>
                            <th>Property/Plot</th>
                            <th>Client</th>
                            <th>Dealer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="dealsTableBody">
                        @foreach($deals as $deal)
                        <tr data-status="{{ $deal->status }}">
                            <td><strong>#{{ $deal->id }}</strong></td>
                            <td>
                                <div class="deal-property">
                                    @if($deal->property)
                                        <i class="fas fa-building text-primary"></i>
                                        {{ $deal->property->title }}
                                    @elseif($deal->plot)
                                        <i class="fas fa-th text-success"></i>
                                        Plot #{{ $deal->plot->plot_number }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </td>
                            <td>{{ $deal->client->name ?? 'N/A' }}</td>
                            <td>{{ $deal->dealer->user->name ?? 'N/A' }}</td>
                            <td><strong class="text-success">PKR {{ number_format($deal->amount) }}</strong></td>
                            <td><span class="status-badge status-{{ $deal->status }}">{{ ucfirst($deal->status) }}</span></td>
                            <td>{{ $deal->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('deals.show', $deal) }}" class="btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('deals.edit')
                                    <a href="{{ route('deals.edit', $deal) }}" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('deals.delete')
                                    <button onclick="deleteDeal({{ $deal->id }})" class="btn-icon text-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $deals->links() }}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .deal-property { display: flex; align-items: center; gap: 8px; }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const statusValue = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#dealsTableBody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const status = row.getAttribute('data-status');

        const matchesSearch = text.includes(searchValue);
        const matchesStatus = !statusValue || status === statusValue;

        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

function deleteDeal(id) {
    if (!confirm('Are you sure you want to delete this deal?')) return;

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
            location.reload();
        } else {
            alert(data.message || 'Failed to delete deal');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>
@endpush
@endsection
