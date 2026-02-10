@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
    </div>
</div>

<!-- Quick Stats Overview -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
    @can('societies.view')
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i class="fas fa-city"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['societies'] ?? 0 }}</h3>
            <p>Societies</p>
        </div>
    </div>
    @endcan

    @can('plots.view')
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['total_plots'] ?? 0 }}</h3>
            <p>Total Plots</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['available_plots'] ?? 0 }}</h3>
            <p>Available Plots</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i class="fas fa-handshake"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['sold_plots'] ?? 0 }}</h3>
            <p>Sold Plots</p>
        </div>
    </div>
    @endcan

    @can('properties.view')
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['properties'] ?? 0 }}</h3>
            <p>Properties</p>
        </div>
    </div>
    @endcan

    @can('clients.view')
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['clients'] ?? 0 }}</h3>
            <p>Clients</p>
        </div>
    </div>
    @endcan

    @can('leads.view')
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['leads'] ?? 0 }}</h3>
            <p>Active Leads</p>
        </div>
    </div>
    @endcan

    @can('deals.view')
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['active_deals'] ?? 0 }}</h3>
            <p>Active Deals</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
            <i class="fas fa-folder-open"></i>
        </div>
        <div class="stat-content">
            <h3>{{ $stats['active_files'] ?? 0 }}</h3>
            <p>Active Files</p>
        </div>
    </div>
    @endcan
</div>

<!-- Dealer Specific Stats -->
@if(isset($stats['my_leads']))
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding: 20px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
            <i class="fas fa-user-tie"></i> My Performance
        </h2>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="stat-card-small">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="stat-icon-small" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 24px; font-weight: 700; margin: 0;">{{ $stats['my_leads'] ?? 0 }}</h4>
                        <p style="color: #6b7280; font-size: 14px; margin: 0;">My Leads</p>
                    </div>
                </div>
            </div>
            <div class="stat-card-small">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="stat-icon-small" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 24px; font-weight: 700; margin: 0;">{{ $stats['my_clients'] ?? 0 }}</h4>
                        <p style="color: #6b7280; font-size: 14px; margin: 0;">My Clients</p>
                    </div>
                </div>
            </div>
            <div class="stat-card-small">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div class="stat-icon-small" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 24px; font-weight: 700; margin: 0;">{{ $stats['my_deals'] ?? 0 }}</h4>
                        <p style="color: #6b7280; font-size: 14px; margin: 0;">My Deals</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Financial Overview -->
@can('payments.view')
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding: 20px;">
        <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
            <i class="fas fa-chart-line"></i> Financial Overview
        </h2>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            <div class="stat-card-outline">
                <div style="text-align: center; padding: 20px;">
                    <div style="color: #10b981; font-size: 32px; margin-bottom: 10px;">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h3 style="font-size: 28px; font-weight: 700; color: #1f2937; margin: 0 0 5px 0;">
                        {{ number_format($financialStats['total_revenue_today'] ?? 0, 0) }} PKR
                    </h3>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">Today's Revenue</p>
                </div>
            </div>
            <div class="stat-card-outline">
                <div style="text-align: center; padding: 20px;">
                    <div style="color: #3b82f6; font-size: 32px; margin-bottom: 10px;">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <h3 style="font-size: 28px; font-weight: 700; color: #1f2937; margin: 0 0 5px 0;">
                        {{ number_format($financialStats['total_revenue_week'] ?? 0, 0) }} PKR
                    </h3>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">This Week</p>
                </div>
            </div>
            <div class="stat-card-outline">
                <div style="text-align: center; padding: 20px;">
                    <div style="color: #8b5cf6; font-size: 32px; margin-bottom: 10px;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 style="font-size: 28px; font-weight: 700; color: #1f2937; margin: 0 0 5px 0;">
                        {{ number_format($financialStats['total_revenue_month'] ?? 0, 0) }} PKR
                    </h3>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">This Month</p>
                </div>
            </div>
            <div class="stat-card-outline">
                <div style="text-align: center; padding: 20px;">
                    <div style="color: #f59e0b; font-size: 32px; margin-bottom: 10px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 style="font-size: 28px; font-weight: 700; color: #1f2937; margin: 0 0 5px 0;">
                        {{ number_format($financialStats['pending_payments'] ?? 0, 0) }} PKR
                    </h3>
                    <p style="color: #6b7280; font-size: 14px; margin: 0;">Pending Payments</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan

<!-- Two Column Layout for Recent Activity & Tasks -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-bottom: 30px;">

    <!-- Recent Leads -->
    @can('leads.view')
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-user-plus"></i> Recent Leads
            </h2>
            <a href="{{ route('leads.index') }}" class="btn-link">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($recentLeads ?? [] as $lead)
            <div style="padding: 15px 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 5px 0;">
                        {{ $lead->name }}
                    </h4>
                    <p style="font-size: 12px; color: #6b7280; margin: 0;">
                        <i class="fas fa-phone"></i> {{ $lead->phone }}
                    </p>
                </div>
                <span class="badge badge-{{ $lead->status == 'hot' ? 'danger' : ($lead->status == 'warm' ? 'warning' : 'info') }}">
                    {{ ucfirst($lead->status) }}
                </span>
            </div>
            @empty
            <div style="padding: 40px 20px; text-align: center; color: #9ca3af;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                <p style="margin: 0;">No recent leads</p>
            </div>
            @endforelse
        </div>
    </div>
    @endcan

    <!-- Upcoming Follow-ups -->
    @can('followups.view')
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-bell"></i> Upcoming Follow-ups
            </h2>
            <a href="{{ route('followups.index') }}" class="btn-link">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($upcomingFollowups ?? [] as $followup)
            <div style="padding: 15px 20px; border-bottom: 1px solid #f3f4f6;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">
                    <h4 style="font-size: 14px; font-weight: 600; color: #1f2937; margin: 0;">
                        {{ $followup->followable->name ?? 'N/A' }}
                    </h4>
                    <span style="font-size: 12px; color: #6b7280;">
                        <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($followup->scheduled_at)->format('M d, Y') }}
                    </span>
                </div>
                <p style="font-size: 12px; color: #6b7280; margin: 0;">
                    {{ Str::limit($followup->notes, 60) }}
                </p>
            </div>
            @empty
            <div style="padding: 40px 20px; text-align: center; color: #9ca3af;">
                <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                <p style="margin: 0;">No upcoming follow-ups</p>
            </div>
            @endforelse
        </div>
    </div>
    @endcan

</div>

<!-- Recent Deals & Payments -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">

    <!-- Recent Deals -->
    @can('deals.view')
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-file-contract"></i> Recent Deals
            </h2>
            <a href="{{ route('deals.index') }}" class="btn-link">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($recentDeals ?? [] as $deal)
            <div style="padding: 15px 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 5px 0;">
                        {{ $deal->client->name ?? 'N/A' }}
                    </h4>
                    <p style="font-size: 12px; color: #6b7280; margin: 0;">
                        <i class="fas fa-money-bill-wave"></i> {{ number_format($deal->deal_amount, 0) }} PKR
                    </p>
                </div>
                <span class="badge badge-{{ $deal->status == 'confirmed' ? 'success' : 'warning' }}">
                    {{ ucfirst($deal->status) }}
                </span>
            </div>
            @empty
            <div style="padding: 40px 20px; text-align: center; color: #9ca3af;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                <p style="margin: 0;">No recent deals</p>
            </div>
            @endforelse
        </div>
    </div>
    @endcan

    <!-- Recent Payments -->
    @can('payments.view')
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-receipt"></i> Recent Payments
            </h2>
            <a href="{{ route('payments.index') }}" class="btn-link">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            @forelse($recentPayments ?? [] as $payment)
            <div style="padding: 15px 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 5px 0;">
                        {{ $payment->payment_number }}
                    </h4>
                    <p style="font-size: 12px; color: #6b7280; margin: 0;">
                        <i class="fas fa-calendar"></i> {{ $payment->payment_date }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <h4 style="font-size: 14px; font-weight: 600; color: #10b981; margin: 0;">
                        {{ number_format($payment->amount, 0) }} PKR
                    </h4>
                    <span class="badge badge-{{ $payment->status == 'received' ? 'success' : 'warning' }}" style="font-size: 11px;">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>
            @empty
            <div style="padding: 40px 20px; text-align: center; color: #9ca3af;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                <p style="margin: 0;">No recent payments</p>
            </div>
            @endforelse
        </div>
    </div>
    @endcan

</div>

<style>
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-content h3 {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 5px 0;
}

.stat-content p {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.stat-card-small {
    background: white;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon-small {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.stat-card-outline {
    background: white;
    border: 2px solid #f3f4f6;
    border-radius: 12px;
    transition: border-color 0.2s;
}

.stat-card-outline:hover {
    border-color: #d1d5db;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.btn-link {
    color: #3b82f6;
    font-size: 14px;
    text-decoration: none;
    font-weight: 500;
}

.btn-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge-info {
    background: #dbeafe;
    color: #1e40af;
}
</style>
@endsection
