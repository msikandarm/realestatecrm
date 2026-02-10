@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <i class="fas fa-home"></i>
    <span>Dashboard</span>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_plots'] ?? 0 }}</div>
            <div class="stat-label">Total Plots</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>{{ $stats['plots_this_month'] ?? 0 }} this month</span>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_clients'] ?? 0 }}</div>
            <div class="stat-label">Total Clients</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>{{ $stats['clients_this_month'] ?? 0 }} new</span>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-handshake"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_deals'] ?? 0 }}</div>
            <div class="stat-label">Active Deals</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>{{ $stats['deals_this_month'] ?? 0 }} this month</span>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="stat-value">Rs. {{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+12.5%</span>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_leads'] ?? 0 }}</div>
            <div class="stat-label">Active Leads</div>
        </div>

        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_files'] ?? 0 }}</div>
            <div class="stat-label">Property Files</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['pending_installments'] ?? 0 }}</div>
            <div class="stat-label">Pending Installments</div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['overdue_installments'] ?? 0 }}</div>
            <div class="stat-label">Overdue Installments</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Recent Deals -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Deals</h3>
                <a href="{{ route('deals.index') }}" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Deal #</th>
                                <th>Client</th>
                                <th>Property</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeals ?? [] as $deal)
                                <tr>
                                    <td><strong>{{ $deal->deal_number }}</strong></td>
                                    <td>{{ $deal->client->name }}</td>
                                    <td>
                                        @if($deal->dealable_type === 'App\Models\Plot')
                                            Plot #{{ $deal->dealable->plot_number }}
                                        @else
                                            {{ $deal->dealable->title ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td><strong>Rs. {{ number_format($deal->deal_amount, 0) }}</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $deal->status === 'completed' ? 'success' : ($deal->status === 'pending' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($deal->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $deal->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--gray);">No deals found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upcoming Installments -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upcoming Installments</h3>
                <a href="{{ route('payments.index') }}?type=installment" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body">
                @forelse($upcomingInstallments ?? [] as $installment)
                    <div style="padding: 15px; border-bottom: 1px solid var(--gray-light); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; margin-bottom: 5px;">
                                {{ $installment->propertyFile->client->name }}
                            </div>
                            <div style="font-size: 13px; color: var(--gray);">
                                File: {{ $installment->propertyFile->file_number }}
                            </div>
                            <div style="font-size: 13px; color: var(--gray); margin-top: 3px;">
                                <i class="far fa-calendar"></i> {{ $installment->due_date->format('M d, Y') }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: var(--primary-color); margin-bottom: 5px;">
                                Rs. {{ number_format($installment->amount, 0) }}
                            </div>
                            <span class="badge badge-{{ $installment->status === 'paid' ? 'success' : ($installment->status === 'overdue' ? 'danger' : 'warning') }}">
                                {{ ucfirst($installment->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: var(--gray);">
                        <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                        <p>No upcoming installments</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
        <!-- Recent Leads -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Leads</h3>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline">View All</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLeads ?? [] as $lead)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;">{{ $lead->name }}</div>
                                        <div style="font-size: 12px; color: var(--gray);">{{ $lead->email }}</div>
                                    </td>
                                    <td>{{ $lead->phone }}</td>
                                    <td>
                                        <span class="badge badge-{{ $lead->status === 'converted' ? 'success' : ($lead->status === 'new' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($lead->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $lead->priority === 'high' ? 'danger' : ($lead->priority === 'medium' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($lead->priority) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--gray);">No leads found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Plot Status Overview -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Plot Status Overview</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: rgba(16, 185, 129, 0.05); border-radius: 8px; border-left: 4px solid var(--success-color);">
                        <div>
                            <div style="font-size: 13px; color: var(--gray); margin-bottom: 5px;">Available Plots</div>
                            <div style="font-size: 28px; font-weight: 700; color: var(--success-color);">
                                {{ $plotStats['available'] ?? 0 }}
                            </div>
                        </div>
                        <div style="width: 60px; height: 60px; background: var(--success-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: rgba(245, 158, 11, 0.05); border-radius: 8px; border-left: 4px solid var(--warning-color);">
                        <div>
                            <div style="font-size: 13px; color: var(--gray); margin-bottom: 5px;">Booked Plots</div>
                            <div style="font-size: 28px; font-weight: 700; color: var(--warning-color);">
                                {{ $plotStats['booked'] ?? 0 }}
                            </div>
                        </div>
                        <div style="width: 60px; height: 60px; background: var(--warning-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: rgba(59, 130, 246, 0.05); border-radius: 8px; border-left: 4px solid var(--primary-color);">
                        <div>
                            <div style="font-size: 13px; color: var(--gray); margin-bottom: 5px;">Sold Plots</div>
                            <div style="font-size: 28px; font-weight: 700; color: var(--primary-color);">
                                {{ $plotStats['sold'] ?? 0 }}
                            </div>
                        </div>
                        <div style="width: 60px; height: 60px; background: var(--primary-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <a href="{{ route('leads.create') }}" class="btn btn-primary" style="justify-content: center;">
                    <i class="fas fa-user-plus"></i>
                    Add New Lead
                </a>
                <a href="{{ route('clients.create') }}" class="btn btn-success" style="justify-content: center;">
                    <i class="fas fa-users"></i>
                    Add New Client
                </a>
                <a href="{{ route('plots.create') }}" class="btn btn-primary" style="justify-content: center;">
                    <i class="fas fa-map-marked-alt"></i>
                    Add New Plot
                </a>
                <a href="{{ route('deals.create') }}" class="btn btn-warning" style="justify-content: center;">
                    <i class="fas fa-handshake"></i>
                    Create Deal
                </a>
                <a href="{{ route('payments.create') }}" class="btn btn-success" style="justify-content: center;">
                    <i class="fas fa-money-bill-wave"></i>
                    Record Payment
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-primary" style="justify-content: center;">
                    <i class="fas fa-chart-line"></i>
                    View Reports
                </a>
            </div>
        </div>
    </div>
@endsection
