@extends('layouts.app')

@section('title', 'Streets')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Streets</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Streets Management</h1>
            <p class="page-subtitle">Manage streets within blocks</p>
        </div>
        <div class="header-actions">
            @can('streets.create')
                <a href="{{ route('streets.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Street
                </a>
            @endcan
        </div>
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

@push('styles')
<style>
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

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
