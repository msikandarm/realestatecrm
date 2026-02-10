@extends('layouts.app')

@section('title', 'Property Files')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Property Files</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Property Files</h1>
        @can('files.create')
        <a href="{{ route('files.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create File
        </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <x-stat-card icon="fas fa-file-alt" value="{{ $stats['total'] ?? 0 }}" label="Total Files" bgColor="bg-primary" />
    <x-stat-card icon="fas fa-check-circle" value="{{ $stats['active'] ?? 0 }}" label="Active" bgColor="bg-success" />
    <x-stat-card icon="fas fa-hourglass-half" value="{{ $stats['pending'] ?? 0 }}" label="Pending" bgColor="bg-warning" />
    <x-stat-card icon="fas fa-money-bill-wave" value="PKR {{ number_format($stats['total_amount'] ?? 0) }}" label="Total Value" bgColor="bg-info" />
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> All Files</h3>
        <div class="card-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search files..." onkeyup="filterTable()">
            </div>
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div class="card-body">
        @if($files->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-file-alt"></i></div>
                <h3 class="empty-title">No Files Found</h3>
                <p class="empty-description">Start creating property files with installment plans</p>
                @can('files.create')
                <a href="{{ route('files.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create First File</a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File#</th>
                            <th>Property/Plot</th>
                            <th>Client</th>
                            <th>Total Amount</th>
                            <th>Down Payment</th>
                            <th>Installments</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="filesTableBody">
                        @foreach($files as $file)
                        <tr data-status="{{ $file->status }}">
                            <td><strong>#{{ $file->id }}</strong></td>
                            <td>
                                @if($file->property)
                                    <i class="fas fa-building text-primary"></i> {{ $file->property->title }}
                                @elseif($file->plot)
                                    <i class="fas fa-th text-success"></i> Plot #{{ $file->plot->plot_number }}
                                @endif
                            </td>
                            <td>{{ $file->client->name ?? 'N/A' }}</td>
                            <td><strong>PKR {{ number_format($file->total_amount) }}</strong></td>
                            <td>PKR {{ number_format($file->down_payment) }}</td>
                            <td>{{ $file->installments_count ?? 0 }} / {{ $file->total_installments }}</td>
                            <td><span class="status-badge status-{{ $file->status }}">{{ ucfirst($file->status) }}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('files.show', $file) }}" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('files.statement', $file) }}" class="btn-icon text-info" title="Statement"><i class="fas fa-file-invoice"></i></a>
                                    @can('files.edit')
                                    <a href="{{ route('files.edit', $file) }}" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('files.delete')
                                    <button onclick="deleteFile({{ $file->id }})" class="btn-icon text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper">{{ $files->links() }}</div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-active { background: #d1fae5; color: #065f46; }
    .status-completed { background: #dbeafe; color: #1e40af; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const statusValue = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#filesTableBody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        const matchesSearch = text.includes(searchValue);
        const matchesStatus = !statusValue || status === statusValue;
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

function deleteFile(id) {
    if (!confirm('Delete this file?')) return;
    fetch(`/files/${id}`, {
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
