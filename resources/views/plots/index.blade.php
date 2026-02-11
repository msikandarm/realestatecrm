@extends('layouts.app')

@section('title', 'Plots')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Plots</span>
    </div>
    <h1 class="page-title">Plots Management</h1>
    <p class="page-subtitle">Manage plots across societies</p>
</div>
    <!-- Filter & Search Bar -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="{{ route('plots.index') }}" style="display: flex; gap: 15px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Plot number, location..." value="{{ request('search') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                    <label class="form-label">Society</label>
                    <select name="society_id" class="form-control">
                        <option value="">All Societies</option>
                        @foreach($societies ?? [] as $society)
                            <option value="{{ $society->id }}" {{ request('society_id') == $society->id ? 'selected' : '' }}>
                                {{ $society->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                        <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>

                <a href="{{ route('plots.index') }}" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Reset
                </a>

                <a href="{{ route('plots.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Add Plot
                </a>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 20px;">
        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['available'] ?? 0 }}</div>
            <div class="stat-label">Available Plots</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['booked'] ?? 0 }}</div>
            <div class="stat-label">Booked Plots</div>
        </div>

        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['sold'] ?? 0 }}</div>
            <div class="stat-label">Sold Plots</div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="stat-value">Rs. {{ number_format($stats['total_value'] ?? 0, 0) }}</div>
            <div class="stat-label">Total Value</div>
        </div>
    </div>

    <!-- Plots Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Plots ({{ $plots->total() ?? 0 }})</h3>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-sm btn-outline">
                    <i class="fas fa-file-export"></i>
                    Export
                </button>
                <button class="btn btn-sm btn-outline">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Plot #</th>
                            <th>Society</th>
                            <th>Block</th>
                            <th>Street</th>
                            <th>Size</th>
                            <th>Price (PKR)</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plots ?? [] as $plot)
                            <tr>
                                <td><strong>{{ $plot->plot_number }}</strong></td>
                                <td>{{ $plot->street->block->society->name ?? 'N/A' }}</td>
                                <td>{{ $plot->street->block->name ?? 'N/A' }}</td>
                                <td>{{ $plot->street->name ?? 'N/A' }}</td>
                                <td>
                                    {{ $plot->size }} {{ $plot->size_unit }}
                                    @if($plot->is_corner)
                                        <span class="badge badge-warning" style="font-size: 10px;">Corner</span>
                                    @endif
                                    @if($plot->is_park_facing)
                                        <span class="badge badge-success" style="font-size: 10px;">Park</span>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($plot->price, 0) }}</strong></td>
                                <td>
                                    <span class="badge badge-{{ $plot->status === 'available' ? 'success' : ($plot->status === 'booked' ? 'warning' : 'primary') }}">
                                        {{ ucfirst($plot->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($plot->current_owner_id)
                                        <div style="font-weight: 600;">{{ $plot->owner->name ?? 'N/A' }}</div>
                                        <div style="font-size: 12px; color: var(--gray);">{{ $plot->owner->phone ?? '' }}</div>
                                    @else
                                        <span style="color: var(--gray);">No Owner</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="{{ route('plots.show', $plot) }}" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('plots.edit', $plot) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('plots.destroy', $plot) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--gray);">
                                    <i class="fas fa-map-marked-alt" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                    <p>No plots found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($plots) && $plots->hasPages())
                <div style="margin-top: 20px; display: flex; justify-content: center;">
                    {{ $plots->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
