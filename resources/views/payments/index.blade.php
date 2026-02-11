@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Payments</span>
    </div>
    <h1 class="page-title">Payments</h1>
    <p class="page-subtitle">Manage payments and receipts</p>
</div>
    <!-- Filter & Search Bar -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" action="{{ route('payments.index') }}" style="display: flex; gap: 15px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0; flex: 1;">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Receipt #, client name..." value="{{ request('search') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ request('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="online" {{ request('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Search
                </button>

                <a href="{{ route('payments.index') }}" class="btn btn-outline">
                    <i class="fas fa-redo"></i>
                    Reset
                </a>

                <a href="{{ route('payments.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Record Payment
                </a>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="margin-bottom: 20px;">
        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="stat-value">Rs. {{ number_format($stats['total_amount'] ?? 0, 0) }}</div>
            <div class="stat-label">Total Received</div>
        </div>

        <div class="stat-card primary">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="stat-value">Rs. {{ number_format($stats['today_amount'] ?? 0, 0) }}</div>
            <div class="stat-label">Today's Collection</div>
        </div>

        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
            </div>
            <div class="stat-value">Rs. {{ number_format($stats['month_amount'] ?? 0, 0) }}</div>
            <div class="stat-label">This Month</div>
        </div>

        <div class="stat-card danger">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
            <div class="stat-value">{{ $stats['total_transactions'] ?? 0 }}</div>
            <div class="stat-label">Total Transactions</div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Payments ({{ $payments->total() ?? 0 }})</h3>
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
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>File #</th>
                            <th>Installment</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Received By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments ?? [] as $payment)
                            <tr>
                                <td><strong>{{ $payment->receipt_number }}</strong></td>
                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td>
                                    <div style="font-weight: 600;">{{ $payment->installment->property_file->client->name ?? 'N/A' }}</div>
                                    <div style="font-size: 12px; color: var(--gray);">
                                        {{ $payment->installment->property_file->client->phone ?? '' }}
                                    </div>
                                </td>
                                <td>{{ $payment->installment->property_file->file_number ?? 'N/A' }}</td>
                                <td>
                                    <div>Installment #{{ $payment->installment->installment_number ?? 'N/A' }}</div>
                                    <div style="font-size: 12px; color: var(--gray);">
                                        Due: {{ $payment->installment->due_date->format('M d, Y') ?? 'N/A' }}
                                    </div>
                                </td>
                                <td><strong style="color: var(--success-color);">Rs. {{ number_format($payment->amount, 0) }}</strong></td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $payment->received_by_user->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-success" title="Download Receipt" target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('payments.print', $payment) }}" class="btn btn-sm btn-warning" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--gray);">
                                    <i class="fas fa-money-bill-wave" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                                    <p>No payments found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($payments) && $payments->hasPages())
                <div style="margin-top: 20px; display: flex; justify-content: center;">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
