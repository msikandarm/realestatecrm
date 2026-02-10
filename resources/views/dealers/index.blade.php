@extends('layouts.app')

@section('title', 'Dealers')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Dealers</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Dealers</h1>
        @can('users.create')
        <a href="{{ route('dealers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Dealer
        </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <x-stat-card
        icon="fas fa-users"
        value="{{ $stats['total'] ?? 0 }}"
        label="Total Dealers"
        bgColor="bg-primary"
    />
    <x-stat-card
        icon="fas fa-check-circle"
        value="{{ $stats['active'] ?? 0 }}"
        label="Active Dealers"
        bgColor="bg-success"
    />
    <x-stat-card
        icon="fas fa-handshake"
        value="{{ $stats['total_deals'] ?? 0 }}"
        label="Total Deals"
        bgColor="bg-info"
    />
    <x-stat-card
        icon="fas fa-money-bill-wave"
        value="PKR {{ number_format($stats['total_commission'] ?? 0) }}"
        label="Total Commission"
        bgColor="bg-warning"
    />
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> All Dealers
        </h3>
        <div class="card-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search dealers..." onkeyup="filterTable()">
            </div>
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="card-body">
        @if($dealers->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="empty-title">No Dealers Found</h3>
                <p class="empty-description">Start adding dealers to manage your sales team</p>
                @can('users.create')
                <a href="{{ route('dealers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Dealer
                </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Dealer Info</th>
                            <th>Contact</th>
                            <th>Commission Rate</th>
                            <th>Total Deals</th>
                            <th>Total Commission</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="dealersTableBody">
                        @foreach($dealers as $dealer)
                        <tr data-status="{{ $dealer->status }}">
                            <td>
                                <div class="table-item">
                                    <div class="item-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="item-title">{{ $dealer->user->name ?? $dealer->name }}</div>
                                        <div class="item-subtitle">ID: #{{ $dealer->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    @if($dealer->user && $dealer->user->email)
                                        <div><i class="fas fa-envelope"></i> {{ $dealer->user->email }}</div>
                                    @endif
                                    @if($dealer->phone)
                                        <div><i class="fas fa-phone"></i> {{ $dealer->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td><strong>{{ $dealer->commission_rate }}%</strong></td>
                            <td>{{ $dealer->deals_count ?? 0 }}</td>
                            <td><strong class="text-success">PKR {{ number_format($dealer->total_commission ?? 0) }}</strong></td>
                            <td><span class="status-badge status-{{ $dealer->status }}">{{ ucfirst($dealer->status) }}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('dealers.show', $dealer) }}" class="btn-icon" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('dealers.performance', $dealer) }}" class="btn-icon text-info" title="Performance">
                                        <i class="fas fa-chart-line"></i>
                                    </a>
                                    @can('users.edit')
                                    <a href="{{ route('dealers.edit', $dealer) }}" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('users.delete')
                                    <button onclick="deleteDealer({{ $dealer->id }})" class="btn-icon text-danger" title="Delete">
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
                {{ $dealers->links() }}
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .table-item { display: flex; align-items: center; gap: 12px; }
    .item-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0; }
    .contact-info { display: flex; flex-direction: column; gap: 6px; }
    .contact-info div { display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: var(--gray-700); }
    .contact-info i { color: var(--gray-500); width: 14px; }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const statusValue = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#dealersTableBody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const status = row.getAttribute('data-status');

        const matchesSearch = text.includes(searchValue);
        const matchesStatus = !statusValue || status === statusValue;

        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

function deleteDealer(id) {
    if (!confirm('Are you sure you want to delete this dealer?')) return;

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
            location.reload();
        } else {
            alert(data.message || 'Failed to delete dealer');
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
