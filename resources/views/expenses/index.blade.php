@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Expenses</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Expenses</h1>
        @can('expenses.create')
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Expense
        </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <x-stat-card icon="fas fa-dollar-sign" :value="'PKR ' . number_format($stats['total'] ?? 0)" label="Total Expenses" bgColor="bg-primary" />
    <x-stat-card icon="fas fa-calendar-alt" :value="'PKR ' . number_format($stats['this_month'] ?? 0)" label="This Month" bgColor="bg-success" />
    <x-stat-card icon="fas fa-chart-line" :value="'PKR ' . number_format($stats['this_year'] ?? 0)" label="This Year" bgColor="bg-info" />
    <x-stat-card icon="fas fa-layer-group" :value="($stats['categories'] ?? 0)" label="Categories" bgColor="bg-warning" />
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> All Expenses</h3>
        <div class="card-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search expenses..." onkeyup="filterTable()">
            </div>
            <select id="categoryFilter" onchange="filterTable()">
                <option value="">All Categories</option>
                <option value="utilities">Utilities</option>
                <option value="maintenance">Maintenance</option>
                <option value="salaries">Salaries</option>
                <option value="marketing">Marketing</option>
                <option value="other">Other</option>
            </select>
            <button class="btn btn-secondary"><i class="fas fa-download"></i> Export</button>
        </div>
    </div>

    <div class="card-body">
        @if($expenses->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-receipt"></i></div>
                <h3 class="empty-title">No Expenses Found</h3>
                <p class="empty-description">Start tracking your business expenses</p>
                @can('expenses.create')
                <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add First Expense</a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Attachments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="expensesTableBody">
                        @foreach($expenses as $expense)
                        <tr data-category="{{ $expense->category }}">
                            <td>{{ date('M d, Y', strtotime($expense->date)) }}</td>
                            <td><span class="category-badge category-{{ $expense->category }}">{{ ucfirst($expense->category) }}</span></td>
                            <td>{{ $expense->description }}</td>
                            <td><strong>PKR {{ number_format($expense->amount) }}</strong></td>
                            <td>{{ ucfirst($expense->payment_method ?? 'Cash') }}</td>
                            <td>
                                @if($expense->attachment)
                                    <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn-icon text-info" title="View Attachment">
                                        <i class="fas fa-paperclip"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('expenses.show', $expense) }}" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                    @can('expenses.edit')
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('expenses.delete')
                                    <button onclick="deleteExpense({{ $expense->id }})" class="btn-icon text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .category-badge { display: inline-flex; padding: 6px 12px; border-radius: 16px; font-size: 0.875rem; font-weight: 600; }
    .category-utilities { background: #dbeafe; color: #1e40af; }
    .category-maintenance { background: #fef3c7; color: #92400e; }
    .category-salaries { background: #d1fae5; color: #065f46; }
    .category-marketing { background: #e9d5ff; color: #6b21a8; }
    .category-other { background: #f3f4f6; color: #6b7280; }
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const categoryValue = document.getElementById('categoryFilter').value;
    const rows = document.querySelectorAll('#expensesTableBody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const category = row.getAttribute('data-category');
        const matchesSearch = text.includes(searchValue);
        const matchesCategory = !categoryValue || category === categoryValue;
        row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
    });
}

function deleteExpense(id) {
    if (!confirm('Delete this expense?')) return;
    fetch(`/expenses/${id}`, {
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
