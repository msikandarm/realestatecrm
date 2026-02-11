@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Clients</span>
    </div>
    <h1 class="page-title">Clients Management</h1>
    <p class="page-subtitle">Manage client records</p>
</div>
    <!-- Filter & Search Bar -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}" style="display: flex; gap: 15px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, CNIC, phone..." value="{{ request('search') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Client Type</label>
                    <select name="client_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="buyer" {{ request('client_type') == 'buyer' ? 'selected' : '' }}>Buyer</option>
                        <option value="seller" {{ request('client_type') == 'seller' ? 'selected' : '' }}>Seller</option>
                        <option value="investor" {{ request('client_type') == 'investor' ? 'selected' : '' }}>Investor</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="">All Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>

                <a href="{{ route('clients.index') }}" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Reset
                </a>

                <a href="{{ route('clients.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Add Client
                </a>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 20px;">
        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
            <div class="stat-label">Total Clients</div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
            <div class="stat-label">Active Clients</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['this_month'] ?? 0 }}</div>
            <div class="stat-label">New This Month</div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['with_deals'] ?? 0 }}</div>
            <div class="stat-label">With Active Deals</div>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Clients ({{ $clients->total() ?? 0 }})</h3>
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
                            <th>Client Info</th>
                            <th>CNIC</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Dealer</th>
                            <th>Properties</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients ?? [] as $client)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;">{{ $client->name }}</div>
                                            <div style="font-size: 12px; color: var(--gray);">{{ $client->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $client->cnic ?? 'N/A' }}</td>
                                <td>
                                    <div>{{ $client->phone }}</div>
                                    @if($client->alternate_phone)
                                        <div style="font-size: 12px; color: var(--gray);">{{ $client->alternate_phone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ ucfirst($client->client_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($client->dealer)
                                        <div style="font-weight: 600;">{{ $client->dealer->user->name ?? 'N/A' }}</div>
                                        <div style="font-size: 12px; color: var(--gray);">{{ $client->dealer->phone ?? '' }}</div>
                                    @else
                                        <span style="color: var(--gray);">Not Assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $client->plots->count() ?? 0 }} Plots</div>
                                    <div style="font-size: 12px; color: var(--gray);">{{ $client->property_files->count() ?? 0 }} Files</div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $client->is_active ? 'success' : 'secondary' }}">
                                        {{ $client->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $client->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
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
                                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                    <p>No clients found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($clients) && $clients->hasPages())
                <div style="margin-top: 20px; display: flex; justify-content: center;">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
