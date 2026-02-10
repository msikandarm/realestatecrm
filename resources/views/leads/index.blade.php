@extends('layouts.admin')

@section('title', 'Leads')
@section('page-title', 'Leads Management')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <span>Leads</span>
@endsection

@section('content')
    <!-- Filter & Search Bar -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="{{ route('leads.index') }}" style="display: flex; gap: 15px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, phone, email..." value="{{ request('search') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ request('status') == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ request('status') == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="negotiating" {{ request('status') == 'negotiating' ? 'selected' : '' }}>Negotiating</option>
                        <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-control">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>

                <a href="{{ route('leads.index') }}" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Reset
                </a>

                <a href="{{ route('leads.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Add Lead
                </a>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 20px;">
        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['new'] ?? 0 }}</div>
            <div class="stat-label">New Leads</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-phone"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['contacted'] ?? 0 }}</div>
            <div class="stat-label">Contacted</div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['converted'] ?? 0 }}</div>
            <div class="stat-label">Converted</div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['conversion_rate'] ?? 0 }}%</div>
            <div class="stat-label">Conversion Rate</div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Leads ({{ $leads->total() ?? 0 }})</h3>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-sm btn-outline">
                    <i class="fas fa-file-export"></i>
                    Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Lead Info</th>
                            <th>Contact</th>
                            <th>Source</th>
                            <th>Assigned To</th>
                            <th>Budget Range</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads ?? [] as $lead)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $lead->name }}</div>
                                    <div style="font-size: 12px; color: var(--gray);">{{ $lead->email }}</div>
                                </td>
                                <td>
                                    <div>{{ $lead->phone }}</div>
                                    @if($lead->alternate_phone)
                                        <div style="font-size: 12px; color: var(--gray);">{{ $lead->alternate_phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ $lead->lead_source->name ?? 'Direct' }}
                                    </span>
                                </td>
                                <td>
                                    @if($lead->assigned_dealer)
                                        <div style="font-weight: 600;">{{ $lead->assigned_dealer->user->name ?? 'N/A' }}</div>
                                    @else
                                        <span class="badge badge-warning">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->budget_min && $lead->budget_max)
                                        <div style="font-size: 13px;">
                                            Rs. {{ number_format($lead->budget_min / 1000, 0) }}K - {{ number_format($lead->budget_max / 1000, 0) }}K
                                        </div>
                                    @else
                                        <span style="color: var(--gray);">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $lead->status === 'converted' ? 'success' : ($lead->status === 'new' ? 'primary' : ($lead->status === 'lost' ? 'danger' : 'warning')) }}">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $lead->priority === 'urgent' ? 'danger' : ($lead->priority === 'high' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($lead->priority) }}
                                    </span>
                                </td>
                                <td>{{ $lead->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="{{ route('leads.show', $lead) }}" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($lead->status !== 'converted')
                                            <form action="{{ route('leads.convert', $lead) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Convert to Client">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--gray);">
                                    <i class="fas fa-user-plus" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                    <p>No leads found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($leads) && $leads->hasPages())
                <div style="margin-top: 20px; display: flex; justify-content: center;">
                    {{ $leads->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
