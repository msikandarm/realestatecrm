@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('clients.index') }}">Clients</a>
        <span class="separator">/</span>
        <span class="current">{{ $client->name }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">{{ $client->name }}</h1>
            <p class="page-subtitle">
                <i class="fas fa-phone"></i>
                {{ $client->phone }} | Type: <strong>{{ ucfirst($client->client_type ?? 'N/A') }}</strong>
            </p>
        </div>
        <div class="header-actions">
            @can('clients.edit')
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Client
                </a>
            @endcan
            @can('clients.delete')
                <button onclick="deleteClient({{ $client->id }})" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-user-check',
        'value' => ucfirst($client->client_status ?? 'Active'),
        'label' => 'Client Status',
        'bgColor' => ($client->client_status ?? 'active') === 'active' ? 'success' : 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-handshake',
        'value' => $stats['total_deals'] ?? 0,
        'label' => 'Total Deals',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-money-bill-wave',
        'value' => 'Rs. ' . number_format($stats['total_deals_value'] ?? 0),
        'label' => 'Total Value',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-clock',
        'value' => $stats['pending_followups'] ?? 0,
        'label' => 'Pending Follow-ups',
        'bgColor' => 'info'
    ])
</div>

<div class="details-layout">
    <!-- Main Column -->
    <div class="main-column">
        <!-- Basic Information Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                <span class="badge badge-{{ ($client->client_status ?? 'active') === 'active' ? 'success' : 'warning' }}">
                    {{ ucfirst($client->client_status ?? 'Active') }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Full Name</label>
                        <value>{{ $client->name }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Phone Number</label>
                        <value><i class="fas fa-phone text-primary"></i> {{ $client->phone }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Email Address</label>
                        <value>
                            @if($client->email)
                                <i class="fas fa-envelope text-info"></i> {{ $client->email }}
                            @else
                                Not provided
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Alternate Phone</label>
                        <value>{{ $client->phone_secondary ?? 'Not provided' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>CNIC</label>
                        <value>{{ $client->cnic ?? 'Not provided' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Client Type</label>
                        <value><span class="code-badge">{{ ucfirst($client->client_type ?? 'N/A') }}</span></value>
                    </div>
                    <div class="detail-item">
                        <label>Occupation</label>
                        <value>{{ $client->occupation ?? 'Not specified' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Company</label>
                        <value>{{ $client->company ?? 'Not specified' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Assigned To</label>
                        <value>{{ $client->assignedTo->name ?? 'Unassigned' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Created Date</label>
                        <value>
                            <i class="fas fa-calendar-alt text-success"></i>
                            {{ $client->created_at->format('M d, Y') }}
                        </value>
                    </div>
                </div>

                @if($client->address || $client->city || $client->province)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-map-marked-alt"></i> Address</label>
                        <value>
                            {{ $client->address ?? '' }}
                            @if($client->city || $client->province)
                                <br>{{ $client->city ?? '' }}{{ $client->city && $client->province ? ', ' : '' }}{{ $client->province ?? '' }}
                            @endif
                        </value>
                    </div>
                @endif

                @if($client->remarks)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-align-left"></i> Remarks / Notes</label>
                        <value>{{ $client->remarks }}</value>
                    </div>
                @endif
            </div>
        </div>

        <!-- Lead Conversion Info -->
        @if($client->converted_from_lead_id || $client->lead_source)
            <div class="details-card">
                <div class="card-header">
                    <h3><i class="fas fa-exchange-alt"></i> Lead Conversion</h3>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        @if($client->lead_source)
                            <div class="detail-item">
                                <label>Lead Source</label>
                                <value><span class="code-badge">{{ ucfirst($client->lead_source) }}</span></value>
                            </div>
                        @endif
                        @if($client->converted_from_lead_at)
                            <div class="detail-item">
                                <label>Conversion Date</label>
                                <value>{{ $client->converted_from_lead_at->format('M d, Y') }}</value>
                            </div>
                        @endif
                        @if($client->originalLead)
                            <div class="detail-item">
                                <label>Original Lead</label>
                                <value>
                                    <a href="{{ route('leads.show', $client->originalLead) }}">
                                        {{ $client->originalLead->name }}
                                    </a>
                                </value>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Deals Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-handshake"></i> Deals</h3>
                <span class="badge badge-info">{{ $client->deals->count() }} Records</span>
            </div>
            <div class="card-body">
                @if($client->deals->isEmpty())
                    <div class="empty-state-small">
                        <i class="fas fa-handshake"></i>
                        <p>No deals found for this client</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Deal #</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->deals->take(5) as $deal)
                                    <tr>
                                        <td>
                                            <a href="{{ route('deals.show', $deal) }}">{{ $deal->deal_number }}</a>
                                        </td>
                                        <td>{{ ucfirst($deal->deal_type) }}</td>
                                        <td>Rs. {{ number_format($deal->deal_amount) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $deal->status === 'completed' ? 'success' : ($deal->status === 'pending' ? 'warning' : 'info') }}">
                                                {{ ucfirst($deal->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $deal->deal_date?->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($client->deals->count() > 5)
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="{{ route('deals.index', ['client_id' => $client->id]) }}" class="btn btn-light btn-sm">
                                View All {{ $client->deals->count() }} Deals
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Follow-ups Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Follow-up History</h3>
                <span class="badge badge-info">{{ $client->followUps->count() }} Records</span>
            </div>
            <div class="card-body">
                @if($client->followUps->isEmpty())
                    <div class="empty-state-small">
                        <i class="fas fa-clock"></i>
                        <p>No follow-ups recorded yet</p>
                    </div>
                @else
                    <div class="followup-timeline">
                        @foreach($client->followUps->take(5) as $followUp)
                            <div class="followup-item">
                                <div class="followup-header">
                                    <span class="followup-date">
                                        <i class="fas fa-calendar"></i>
                                        {{ $followUp->scheduled_at?->format('M d, Y') }}
                                    </span>
                                    <span class="badge badge-{{ $followUp->status === 'completed' ? 'success' : ($followUp->status === 'pending' ? 'warning' : 'info') }}">
                                        {{ ucfirst($followUp->status) }}
                                    </span>
                                </div>
                                <div class="followup-type">
                                    <i class="fas fa-{{ $followUp->type === 'call' ? 'phone' : ($followUp->type === 'meeting' ? 'users' : 'envelope') }}"></i>
                                    {{ ucfirst($followUp->type ?? 'General') }}
                                </div>
                                @if($followUp->notes)
                                    <div class="followup-notes">{{ Str::limit($followUp->notes, 100) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="sidebar-column">
        <!-- Quick Stats Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Quick Stats</h3>
            </div>
            <div class="card-body">
                <div class="quick-stats">
                    <div class="stat-row">
                        <span class="stat-label">Active Deals</span>
                        <span class="stat-value text-primary">{{ $stats['active_deals'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Completed Deals</span>
                        <span class="stat-value text-success">{{ $stats['completed_deals'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Active Files</span>
                        <span class="stat-value text-info">{{ $stats['active_files'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Properties Owned</span>
                        <span class="stat-value text-warning">{{ $stats['properties_owned'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Total Paid</span>
                        <span class="stat-value text-success">Rs. {{ number_format($stats['total_paid'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    @can('deals.create')
                        <a href="{{ route('deals.create', ['client_id' => $client->id]) }}" class="action-btn">
                            <i class="fas fa-handshake"></i>
                            <span>New Deal</span>
                        </a>
                    @endcan
                    @can('follow_ups.create')
                        <a href="{{ route('follow-ups.create', ['client_id' => $client->id]) }}" class="action-btn">
                            <i class="fas fa-clock"></i>
                            <span>Schedule Follow-up</span>
                        </a>
                    @endcan
                    @can('payments.create')
                        <a href="{{ route('payments.create', ['client_id' => $client->id]) }}" class="action-btn">
                            <i class="fas fa-money-bill"></i>
                            <span>Record Payment</span>
                        </a>
                    @endcan
                    <a href="tel:{{ $client->phone }}" class="action-btn">
                        <i class="fas fa-phone"></i>
                        <span>Call Client</span>
                    </a>
                    @if($client->email)
                        <a href="mailto:{{ $client->email }}" class="action-btn">
                            <i class="fas fa-envelope"></i>
                            <span>Email Client</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Properties Card -->
        @if($client->properties && $client->properties->count() > 0)
            <div class="details-card">
                <div class="card-header">
                    <h3><i class="fas fa-building"></i> Properties</h3>
                </div>
                <div class="card-body">
                    <ul class="property-list">
                        @foreach($client->properties->take(5) as $property)
                            <li>
                                <a href="{{ route('properties.show', $property) }}">
                                    {{ $property->name ?? $property->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Payments Summary Card -->
        @if($client->payments && $client->payments->count() > 0)
            <div class="details-card">
                <div class="card-header">
                    <h3><i class="fas fa-receipt"></i> Recent Payments</h3>
                </div>
                <div class="card-body">
                    <div class="payment-list">
                        @foreach($client->payments->take(5) as $payment)
                            <div class="payment-item">
                                <div class="payment-info">
                                    <span class="payment-amount">Rs. {{ number_format($payment->amount) }}</span>
                                    <span class="payment-date">{{ $payment->created_at->format('M d, Y') }}</span>
                                </div>
                                <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .details-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .details-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .details-card .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .details-card .card-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--gray-800);
    }

    .details-card .card-header h3 i {
        margin-right: 8px;
        color: var(--primary);
    }

    .details-card .card-body {
        padding: 20px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detail-item.full-width {
        grid-column: span 2;
    }

    .detail-item label {
        font-size: 12px;
        font-weight: 500;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-item value {
        font-size: 14px;
        color: var(--gray-800);
        font-weight: 500;
    }

    .code-badge {
        display: inline-block;
        padding: 4px 10px;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-700);
    }

    .quick-stats .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .quick-stats .stat-row:last-child {
        border-bottom: none;
    }

    .quick-stats .stat-label {
        font-size: 13px;
        color: var(--gray-600);
    }

    .quick-stats .stat-value {
        font-size: 14px;
        font-weight: 600;
    }

    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 8px;
        color: var(--gray-700);
        text-decoration: none;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: var(--primary);
        color: white;
    }

    .action-btn i {
        width: 20px;
        text-align: center;
    }

    .empty-state-small {
        text-align: center;
        padding: 30px 20px;
        color: var(--gray-500);
    }

    .empty-state-small i {
        font-size: 32px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-state-small p {
        margin: 0;
        font-size: 14px;
    }

    .followup-timeline {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .followup-item {
        padding: 16px;
        background: #f9fafb;
        border-radius: 8px;
        border-left: 3px solid var(--primary);
    }

    .followup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .followup-date {
        font-size: 13px;
        color: var(--gray-600);
    }

    .followup-date i {
        margin-right: 6px;
    }

    .followup-type {
        font-size: 14px;
        font-weight: 500;
        color: var(--gray-800);
        margin-bottom: 6px;
    }

    .followup-type i {
        margin-right: 6px;
        color: var(--primary);
    }

    .followup-notes {
        font-size: 13px;
        color: var(--gray-600);
        line-height: 1.5;
    }

    .property-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .property-list li {
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .property-list li:last-child {
        border-bottom: none;
    }

    .property-list a {
        color: var(--primary);
        text-decoration: none;
    }

    .property-list a:hover {
        text-decoration: underline;
    }

    .payment-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .payment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: #f9fafb;
        border-radius: 6px;
    }

    .payment-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .payment-amount {
        font-weight: 600;
        color: var(--gray-800);
    }

    .payment-date {
        font-size: 12px;
        color: var(--gray-500);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .data-table th {
        font-size: 12px;
        font-weight: 600;
        color: var(--gray-500);
        text-transform: uppercase;
        background: #f9fafb;
    }

    .data-table td {
        font-size: 14px;
        color: var(--gray-700);
    }

    .data-table a {
        color: var(--primary);
        text-decoration: none;
    }

    .data-table a:hover {
        text-decoration: underline;
    }

    @media (max-width: 1024px) {
        .details-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-item.full-width {
            grid-column: span 1;
        }
    }
</style>
@endpush

@can('clients.delete')
<script>
    function deleteClient(id) {
        if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
            fetch(`/clients/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("clients.index") }}';
                } else {
                    alert(data.message || 'Failed to delete client');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the client');
                console.error(error);
            });
        }
    }
</script>
@endcan
@endsection
