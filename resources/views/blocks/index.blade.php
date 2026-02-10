@extends('layouts.app')

@section('title', 'Blocks')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <span class="current">Blocks</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">Blocks Management</h1>
            <p class="page-subtitle">Manage society blocks and sectors</p>
        </div>
        <div class="header-actions">
            @can('blocks.create')
                <a href="{{ route('blocks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Block
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-building',
        'value' => $stats['total'] ?? 0,
        'label' => 'Total Blocks',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-check-circle',
        'value' => $stats['active'] ?? 0,
        'label' => 'Active Blocks',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-pause-circle',
        'value' => $stats['inactive'] ?? 0,
        'label' => 'Inactive Blocks',
        'bgColor' => 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-road',
        'value' => $stats['total_streets'] ?? 0,
        'label' => 'Total Streets',
        'bgColor' => 'info'
    ])
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-filter"></i> Filter Blocks</h3>
        <div class="card-actions">
            <form method="GET" action="{{ route('blocks.index') }}" class="filters-form" id="filterForm">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search blocks..." value="{{ request('search') }}">
                </div>

                <select name="society_id" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Societies</option>
                    @foreach($societies as $society)
                        <option value="{{ $society->id }}" {{ request('society_id') == $society->id ? 'selected' : '' }}>
                            {{ $society->name }}
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

                @if(request()->hasAny(['search', 'society_id', 'status']))
                    <a href="{{ route('blocks.index') }}" class="btn btn-light">
                        <i class="fas fa-times"></i> Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($blocks->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>No Blocks Found</h3>
                <p>There are no blocks matching your criteria. Start by adding a new block.</p>
                @can('blocks.create')
                    <a href="{{ route('blocks.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Block
                    </a>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Block Details</th>
                            <th>Society</th>
                            <th>Streets</th>
                            <th>Plots</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blocks as $block)
                            <tr>
                                <td>
                                    <div class="table-primary">
                                        <strong>{{ $block->name }}</strong>
                                        <span class="table-secondary">{{ $block->code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="society-info">
                                        <i class="fas fa-city text-primary"></i>
                                        {{ $block->society->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fas fa-road"></i> {{ $block->streets_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-th"></i> {{ $block->plots_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $block->status == 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($block->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="{{ route('blocks.show', $block) }}" class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('blocks.edit')
                                            <a href="{{ route('blocks.edit', $block) }}" class="btn-icon" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('blocks.delete')
                                            <button onclick="deleteBlock({{ $block->id }})" class="btn-icon btn-danger" title="Delete">
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
                {{ $blocks->links() }}
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

    .society-info {
        display: flex;
        align-items: center;
        gap: 8px;
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
@can('blocks.delete')
<script>
function deleteBlock(id) {
    if (confirm('Are you sure you want to delete this block? This will also delete all associated streets and plots.')) {
        fetch(`/blocks/${id}`, {
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
                alert('Error: ' + (data.error || 'Unable to delete block'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting block. Please try again.');
        });
    }
}
</script>
@endcan
@endpush
@endsection
