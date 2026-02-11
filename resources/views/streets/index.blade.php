@extends('layouts.app')

@section('title', 'Streets')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Streets</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
        <div>
            <h1 class="page-title">Streets Management</h1>
            <p class="page-subtitle">Manage streets within blocks</p>
        </div>
        @can('streets.create')
            <a href="{{ route('streets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Street
            </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-road',
        'value' => $stats['total'] ?? 0,
        'label' => 'Total Streets',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-check-circle',
        'value' => $stats['active'] ?? 0,
        'label' => 'Active Streets',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-th',
        'value' => $stats['total_plots'] ?? 0,
        'label' => 'Total Plots',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-ruler',
        'value' => $stats['avg_width'] ?? 0,
        'label' => 'Avg Width (ft)',
        'bgColor' => 'warning'
    ])
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filter Streets</h3>
        <div class="card-actions">
            <form method="GET" action="{{ route('streets.index') }}" class="filters-form" id="filterForm">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search streets..." value="{{ request('search') }}">
                </div>

                <select name="society_id" class="filter-select" id="societyFilter">
                    <option value="">All Societies</option>
                    @foreach($societies as $society)
                        <option value="{{ $society->id }}" {{ request('society_id') == $society->id ? 'selected' : '' }}>
                            {{ $society->name }}
                        </option>
                    @endforeach
                </select>

                <select name="block_id" class="filter-select" id="blockFilter">
                    <option value="">All Blocks</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}"
                                data-society="{{ $block->society_id }}"
                                {{ request('block_id') == $block->id ? 'selected' : '' }}>
                            {{ $block->name }} ({{ $block->society->name }})
                        </option>
                    @endforeach
                </select>

                <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'society_id', 'block_id', 'status']))
                    <a href="{{ route('streets.index') }}" class="btn btn-light">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($streets->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-road"></i>
                </div>
                <h3>No Streets Found</h3>
                <p>There are no streets matching your criteria. Start by adding a new street.</p>
                @can('streets.create')
                    <a href="{{ route('streets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Street
                    </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Street Details</th>
                            <th>Block</th>
                            <th>Society</th>
                            <th>Type</th>
                            <th>Width</th>
                            <th>Plots</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($streets as $street)
                            <tr>
                                <td>
                                    <div class="table-primary">
                                        <strong>{{ $street->name }}</strong>
                                        <span class="table-secondary">{{ $street->code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-building text-primary"></i>
                                    {{ $street->block->name ?? 'N/A' }}
                                </td>
                                <td>
                                    <i class="fas fa-city text-info"></i>
                                    {{ $street->block->society->name ?? 'N/A' }}
                                </td>
                                <td>
                                    @if($street->type)
                                        <span class="badge badge-secondary">
                                            {{ ucfirst($street->type) }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($street->width)
                                        <strong>{{ $street->width }} ft</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-th"></i> {{ $street->plots_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $street->status == 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($street->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="{{ route('streets.show', $street) }}" class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('streets.edit')
                                            <a href="{{ route('streets.edit', $street) }}" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('streets.delete')
                                            <button onclick="deleteStreet({{ $street->id }})" class="btn-icon btn-danger" title="Delete">
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
                {{ $streets->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
    }

    .card-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .filters-form {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        min-width: 250px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .search-box input {
        width: 100%;
        padding: 8px 12px 8px 36px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
        background: white;
        cursor: pointer;
    }

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
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary {
        background: var(--gray-100);
        color: var(--gray-700);
    }

    .btn-secondary:hover {
        background: var(--gray-200);
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

    .card-body {
        padding: 0;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: var(--gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: var(--gray-400);
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 8px;
    }

    .empty-state p {
        color: var(--gray-600);
        margin-bottom: 20px;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table thead {
        background: var(--gray-50);
    }

    .data-table th {
        padding: 14px 20px;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--gray-600);
        border-bottom: 1px solid var(--gray-200);
    }

    .data-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--gray-100);
        font-size: 0.875rem;
    }

    .data-table tbody tr:hover {
        background: var(--gray-50);
    }

    .table-primary {
        display: flex;
        flex-direction: column;
    }

    .table-primary strong {
        color: var(--gray-900);
        margin-bottom: 2px;
    }

    .table-secondary {
        font-size: 0.8125rem;
        color: var(--gray-500);
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

    .badge-success {
        background: var(--success);
        color: white;
    }

    .badge-warning {
        background: var(--warning);
        color: white;
    }

    .badge-info {
        background: var(--info);
        color: white;
    }

    .badge-primary {
        background: var(--primary);
        color: white;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: var(--gray-100);
        color: var(--gray-600);
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-icon:hover {
        background: var(--primary);
        color: white;
    }

    .btn-icon.btn-danger:hover {
        background: var(--danger);
        color: white;
    }

    .pagination-wrapper {
        padding: 20px 24px;
        border-top: 1px solid var(--gray-200);
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .filters-form {
            width: 100%;
        }

        .search-box {
            width: 100%;
        }
    }
</style>

@push('scripts')
<script>
// Cascading filter: When society changes, filter blocks
document.getElementById('societyFilter').addEventListener('change', function() {
    const societyId = this.value;
    const blockSelect = document.getElementById('blockFilter');
    const allOptions = blockSelect.querySelectorAll('option');

    allOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block';
            return;
        }

        if (!societyId || option.dataset.society === societyId) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });

    // Reset block selection if current selection is now hidden
    const currentOption = blockSelect.options[blockSelect.selectedIndex];
    if (currentOption && currentOption.style.display === 'none') {
        blockSelect.value = '';
    }
});

// Trigger on page load if society is already selected
if (document.getElementById('societyFilter').value) {
    document.getElementById('societyFilter').dispatchEvent(new Event('change'));
}
</script>

@can('streets.delete')
<script>
function deleteStreet(id) {
    if (confirm('Are you sure you want to delete this street? This will also delete all plots on this street.')) {
        fetch(`/streets/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unable to delete street'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting street. Please try again.');
        });
    }
}
</script>
@endcan
@endpush
@endsection
