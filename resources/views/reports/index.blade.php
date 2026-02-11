@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <span>Reports</span>
    </div>
    <h1 class="page-title">Reports & Analytics</h1>
    <p class="page-subtitle">System-wide reports and analytics</p>
</div>
    <!-- Report Type Selector -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="btn btn-primary report-tab active" data-report="plots">
                    <i class="fas fa-chart-pie"></i>
                    Plots Report
                </button>
                <button class="btn btn-outline report-tab" data-report="payments">
                    <i class="fas fa-money-bill-wave"></i>
                    Payments Report
                </button>
                <button class="btn btn-outline report-tab" data-report="commissions">
                    <i class="fas fa-hand-holding-usd"></i>
                    Commissions Report
                </button>
                <button class="btn btn-outline report-tab" data-report="installments">
                    <i class="fas fa-exclamation-triangle"></i>
                    Overdue Installments
                </button>
                <button class="btn btn-outline report-tab" data-report="society">
                    <i class="fas fa-city"></i>
                    Society-wise Sales
                </button>
            </div>
        </div>
    </div>

    <!-- Global Filters -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;" id="filter-society">
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

                <div class="form-group" style="margin-bottom: 0;" id="filter-dealer">
                    <label class="form-label">Dealer</label>
                    <select name="dealer_id" class="form-control">
                        <option value="">All Dealers</option>
                        @foreach($dealers ?? [] as $dealer)
                            <option value="{{ $dealer->id }}" {{ request('dealer_id') == $dealer->id ? 'selected' : '' }}>
                                {{ $dealer->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                    Apply Filters
                </button>

                <button type="button" class="btn btn-success" onclick="exportReport()">
                    <i class="fas fa-file-export"></i>
                    Export
                </button>

                <button type="button" class="btn btn-outline" onclick="printReport()">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </form>
        </div>
    </div>

    <!-- REPORT 1: PLOTS REPORT (Available vs Sold) -->
    <div class="report-section" id="report-plots">
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $plotsReport['available'] ?? 0 }}</div>
                <div class="stat-label">Available Plots</div>
                <div class="stat-change positive">
                    <span>{{ number_format($plotsReport['available_percentage'] ?? 0, 1) }}% of total</span>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $plotsReport['booked'] ?? 0 }}</div>
                <div class="stat-label">Booked Plots</div>
                <div class="stat-change">
                    <span>{{ number_format($plotsReport['booked_percentage'] ?? 0, 1) }}% of total</span>
                </div>
            </div>

            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $plotsReport['sold'] ?? 0 }}</div>
                <div class="stat-label">Sold Plots</div>
                <div class="stat-change positive">
                    <span>{{ number_format($plotsReport['sold_percentage'] ?? 0, 1) }}% of total</span>
                </div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $plotsReport['total'] ?? 0 }}</div>
                <div class="stat-label">Total Plots</div>
                <div class="stat-change positive">
                    <span>Rs. {{ number_format($plotsReport['total_value'] ?? 0, 0) }}</span>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plot Status Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="plotStatusChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Plot Value by Status</h3>
                </div>
                <div class="card-body">
                    <canvas id="plotValueChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3 class="card-title">Society-wise Plot Status</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Society Name</th>
                                <th>Total Plots</th>
                                <th>Available</th>
                                <th>Booked</th>
                                <th>Sold</th>
                                <th>Total Value (PKR)</th>
                                <th>Sold Value (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($societyPlots ?? [] as $society)
                                <tr>
                                    <td><strong>{{ $society->name }}</strong></td>
                                    <td>{{ $society->total_plots }}</td>
                                    <td><span class="badge badge-success">{{ $society->available_plots }}</span></td>
                                    <td><span class="badge badge-warning">{{ $society->booked_plots }}</span></td>
                                    <td><span class="badge badge-primary">{{ $society->sold_plots }}</span></td>
                                    <td>Rs. {{ number_format($society->total_value, 0) }}</td>
                                    <td><strong style="color: var(--success-color);">Rs. {{ number_format($society->sold_value, 0) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--gray);">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REPORT 2: PAYMENTS REPORT -->
    <div class="report-section" id="report-payments" style="display: none;">
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($paymentsReport['total_received'] ?? 0, 0) }}</div>
                <div class="stat-label">Total Received</div>
            </div>

            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $paymentsReport['total_transactions'] ?? 0 }}</div>
                <div class="stat-label">Total Transactions</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($paymentsReport['average_payment'] ?? 0, 0) }}</div>
                <div class="stat-label">Average Payment</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($paymentsReport['daily_average'] ?? 0, 0) }}</div>
                <div class="stat-label">Daily Average</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Payment Trend</h3>
                </div>
                <div class="card-body">
                    <canvas id="paymentTrendChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Methods</h3>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h3 class="card-title">Daily Payment Summary</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transactions</th>
                                <th>Cash</th>
                                <th>Bank Transfer</th>
                                <th>Cheque</th>
                                <th>Online</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyPayments ?? [] as $day)
                                <tr>
                                    <td><strong>{{ $day->date }}</strong></td>
                                    <td>{{ $day->transaction_count }}</td>
                                    <td>Rs. {{ number_format($day->cash_amount, 0) }}</td>
                                    <td>Rs. {{ number_format($day->bank_amount, 0) }}</td>
                                    <td>Rs. {{ number_format($day->cheque_amount, 0) }}</td>
                                    <td>Rs. {{ number_format($day->online_amount, 0) }}</td>
                                    <td><strong style="color: var(--success-color);">Rs. {{ number_format($day->total_amount, 0) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--gray);">No payments in selected period</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--bg-light); font-weight: 700;">
                                <td>TOTAL</td>
                                <td>{{ $paymentTotals['transactions'] ?? 0 }}</td>
                                <td>Rs. {{ number_format($paymentTotals['cash'] ?? 0, 0) }}</td>
                                <td>Rs. {{ number_format($paymentTotals['bank'] ?? 0, 0) }}</td>
                                <td>Rs. {{ number_format($paymentTotals['cheque'] ?? 0, 0) }}</td>
                                <td>Rs. {{ number_format($paymentTotals['online'] ?? 0, 0) }}</td>
                                <td style="color: var(--success-color);">Rs. {{ number_format($paymentTotals['total'] ?? 0, 0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REPORT 3: DEALER COMMISSIONS -->
    <div class="report-section" id="report-commissions" style="display: none;">
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($commissionsReport['total_earned'] ?? 0, 0) }}</div>
                <div class="stat-label">Total Earned</div>
            </div>

            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($commissionsReport['total_paid'] ?? 0, 0) }}</div>
                <div class="stat-label">Total Paid</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($commissionsReport['pending'] ?? 0, 0) }}</div>
                <div class="stat-label">Pending Payment</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $commissionsReport['active_dealers'] ?? 0 }}</div>
                <div class="stat-label">Active Dealers</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">Top Performing Dealers</h3>
            </div>
            <div class="card-body">
                <canvas id="topDealersChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dealer Commission Details</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Dealer Name</th>
                                <th>Total Deals</th>
                                <th>Commission Earned</th>
                                <th>Commission Paid</th>
                                <th>Pending</th>
                                <th>Avg Commission/Deal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dealerCommissions ?? [] as $dealer)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;">{{ $dealer->name }}</div>
                                        <div style="font-size: 12px; color: var(--gray);">{{ $dealer->phone }}</div>
                                    </td>
                                    <td>{{ $dealer->total_deals }}</td>
                                    <td><strong>Rs. {{ number_format($dealer->commission_earned, 0) }}</strong></td>
                                    <td style="color: var(--success-color);">Rs. {{ number_format($dealer->commission_paid, 0) }}</td>
                                    <td style="color: var(--warning-color);">Rs. {{ number_format($dealer->commission_pending, 0) }}</td>
                                    <td>Rs. {{ number_format($dealer->avg_commission, 0) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $dealer->is_active ? 'success' : 'secondary' }}">
                                            {{ $dealer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--gray);">No commission data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REPORT 4: OVERDUE INSTALLMENTS -->
    <div class="report-section" id="report-installments" style="display: none;">
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $overdueReport['total_overdue'] ?? 0 }}</div>
                <div class="stat-label">Overdue Installments</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($overdueReport['overdue_amount'] ?? 0, 0) }}</div>
                <div class="stat-label">Overdue Amount</div>
            </div>

            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $overdueReport['avg_days_overdue'] ?? 0 }} days</div>
                <div class="stat-label">Average Days Overdue</div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($overdueReport['late_fees'] ?? 0, 0) }}</div>
                <div class="stat-label">Total Late Fees</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">Overdue Aging Analysis</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                    <div style="padding: 20px; background: rgba(245, 158, 11, 0.1); border-radius: 8px; text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: var(--warning-color);">
                            {{ $overdueAging['1_30_days'] ?? 0 }}
                        </div>
                        <div style="font-size: 14px; color: var(--gray); margin-top: 5px;">1-30 Days</div>
                        <div style="font-size: 13px; color: var(--gray); margin-top: 3px;">
                            Rs. {{ number_format($overdueAging['1_30_amount'] ?? 0, 0) }}
                        </div>
                    </div>
                    <div style="padding: 20px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: var(--danger-color);">
                            {{ $overdueAging['31_60_days'] ?? 0 }}
                        </div>
                        <div style="font-size: 14px; color: var(--gray); margin-top: 5px;">31-60 Days</div>
                        <div style="font-size: 13px; color: var(--gray); margin-top: 3px;">
                            Rs. {{ number_format($overdueAging['31_60_amount'] ?? 0, 0) }}
                        </div>
                    </div>
                    <div style="padding: 20px; background: rgba(239, 68, 68, 0.15); border-radius: 8px; text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: var(--danger-color);">
                            {{ $overdueAging['61_90_days'] ?? 0 }}
                        </div>
                        <div style="font-size: 14px; color: var(--gray); margin-top: 5px;">61-90 Days</div>
                        <div style="font-size: 13px; color: var(--gray); margin-top: 3px;">
                            Rs. {{ number_format($overdueAging['61_90_amount'] ?? 0, 0) }}
                        </div>
                    </div>
                    <div style="padding: 20px; background: rgba(239, 68, 68, 0.2); border-radius: 8px; text-align: center;">
                        <div style="font-size: 32px; font-weight: 700; color: #dc2626;">
                            {{ $overdueAging['90_plus_days'] ?? 0 }}
                        </div>
                        <div style="font-size: 14px; color: var(--gray); margin-top: 5px;">90+ Days</div>
                        <div style="font-size: 13px; color: var(--gray); margin-top: 3px;">
                            Rs. {{ number_format($overdueAging['90_plus_amount'] ?? 0, 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Overdue Installments Details</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>File #</th>
                                <th>Client</th>
                                <th>Installment #</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Amount</th>
                                <th>Late Fee</th>
                                <th>Total Due</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overdueInstallments ?? [] as $installment)
                                <tr style="background: {{ $installment->days_overdue > 90 ? 'rgba(239, 68, 68, 0.05)' : ($installment->days_overdue > 60 ? 'rgba(245, 158, 11, 0.05)' : '') }}">
                                    <td><strong>{{ $installment->file_number }}</strong></td>
                                    <td>
                                        <div style="font-weight: 600;">{{ $installment->client_name }}</div>
                                        <div style="font-size: 12px; color: var(--gray);">{{ $installment->client_phone }}</div>
                                    </td>
                                    <td>{{ $installment->installment_number }}</td>
                                    <td>{{ $installment->due_date }}</td>
                                    <td>
                                        <span class="badge badge-{{ $installment->days_overdue > 90 ? 'danger' : ($installment->days_overdue > 60 ? 'warning' : 'secondary') }}">
                                            {{ $installment->days_overdue }} days
                                        </span>
                                    </td>
                                    <td>Rs. {{ number_format($installment->amount, 0) }}</td>
                                    <td style="color: var(--danger-color);">Rs. {{ number_format($installment->late_fee, 0) }}</td>
                                    <td><strong>Rs. {{ number_format($installment->total_due, 0) }}</strong></td>
                                    <td>
                                        <a href="{{ route('payments.create', ['installment_id' => $installment->id]) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-money-bill-wave"></i>
                                            Pay Now
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align: center; color: var(--gray);">
                                        <i class="fas fa-check-circle" style="font-size: 48px; opacity: 0.3; margin-bottom: 10px;"></i>
                                        <p>No overdue installments</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REPORT 5: SOCIETY-WISE SALES -->
    <div class="report-section" id="report-society" style="display: none;">
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-city"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $societyReport['total_societies'] ?? 0 }}</div>
                <div class="stat-label">Total Societies</div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">Rs. {{ number_format($societyReport['total_sales'] ?? 0, 0) }}</div>
                <div class="stat-label">Total Sales Value</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $societyReport['total_deals'] ?? 0 }}</div>
                <div class="stat-label">Total Deals</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $societyReport['top_society'] ?? 'N/A' }}</div>
                <div class="stat-label">Top Performer</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3 class="card-title">Sales by Society</h3>
            </div>
            <div class="card-body">
                <canvas id="societySalesChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Society Performance Breakdown</h3>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Society</th>
                                <th>Total Plots</th>
                                <th>Sold Plots</th>
                                <th>Sales Rate</th>
                                <th>Total Sales Value</th>
                                <th>Avg Plot Price</th>
                                <th>Total Deals</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($societySales ?? [] as $society)
                                <tr>
                                    <td><strong>{{ $society->name }}</strong></td>
                                    <td>{{ $society->total_plots }}</td>
                                    <td>{{ $society->sold_plots }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="flex: 1; background: var(--gray-light); height: 8px; border-radius: 4px; overflow: hidden;">
                                                <div style="width: {{ $society->sales_rate }}%; background: var(--success-color); height: 100%;"></div>
                                            </div>
                                            <span style="font-weight: 600;">{{ number_format($society->sales_rate, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td><strong style="color: var(--success-color);">Rs. {{ number_format($society->total_sales, 0) }}</strong></td>
                                    <td>Rs. {{ number_format($society->avg_price, 0) }}</td>
                                    <td>{{ $society->total_deals }}</td>
                                    <td>
                                        @if($society->sales_rate >= 75)
                                            <span class="badge badge-success">Excellent</span>
                                        @elseif($society->sales_rate >= 50)
                                            <span class="badge badge-primary">Good</span>
                                        @elseif($society->sales_rate >= 25)
                                            <span class="badge badge-warning">Average</span>
                                        @else
                                            <span class="badge badge-danger">Poor</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; color: var(--gray);">No sales data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Report Tab Switching
document.querySelectorAll('.report-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('.report-tab').forEach(b => {
            b.classList.remove('btn-primary', 'active');
            b.classList.add('btn-outline');
        });
        this.classList.remove('btn-outline');
        this.classList.add('btn-primary', 'active');

        // Show selected report
        const reportType = this.dataset.report;
        document.querySelectorAll('.report-section').forEach(section => {
            section.style.display = 'none';
        });
        document.getElementById('report-' + reportType).style.display = 'block';
    });
});

// Chart.js Charts
const chartColors = {
    primary: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    secondary: '#8b5cf6'
};

// Plot Status Chart
const plotStatusCtx = document.getElementById('plotStatusChart');
if (plotStatusCtx) {
    new Chart(plotStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Booked', 'Sold'],
            datasets: [{
                data: [
                    {{ $plotsReport['available'] ?? 0 }},
                    {{ $plotsReport['booked'] ?? 0 }},
                    {{ $plotsReport['sold'] ?? 0 }}
                ],
                backgroundColor: [chartColors.success, chartColors.warning, chartColors.primary]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// Plot Value Chart
const plotValueCtx = document.getElementById('plotValueChart');
if (plotValueCtx) {
    new Chart(plotValueCtx, {
        type: 'bar',
        data: {
            labels: ['Available', 'Booked', 'Sold'],
            datasets: [{
                label: 'Value (Million PKR)',
                data: [
                    {{ ($plotsReport['available_value'] ?? 0) / 1000000 }},
                    {{ ($plotsReport['booked_value'] ?? 0) / 1000000 }},
                    {{ ($plotsReport['sold_value'] ?? 0) / 1000000 }}
                ],
                backgroundColor: [chartColors.success, chartColors.warning, chartColors.primary]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// Payment Trend Chart
const paymentTrendCtx = document.getElementById('paymentTrendChart');
if (paymentTrendCtx) {
    new Chart(paymentTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($paymentMonths ?? []) !!},
            datasets: [{
                label: 'Payments (PKR)',
                data: {!! json_encode($paymentAmounts ?? []) !!},
                borderColor: chartColors.success,
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Payment Method Chart
const paymentMethodCtx = document.getElementById('paymentMethodChart');
if (paymentMethodCtx) {
    new Chart(paymentMethodCtx, {
        type: 'pie',
        data: {
            labels: ['Cash', 'Bank Transfer', 'Cheque', 'Online'],
            datasets: [{
                data: {!! json_encode($paymentMethodData ?? [0, 0, 0, 0]) !!},
                backgroundColor: [chartColors.success, chartColors.primary, chartColors.warning, chartColors.secondary]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// Top Dealers Chart
const topDealersCtx = document.getElementById('topDealersChart');
if (topDealersCtx) {
    new Chart(topDealersCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topDealerNames ?? []) !!},
            datasets: [{
                label: 'Commission Earned (PKR)',
                data: {!! json_encode($topDealerCommissions ?? []) !!},
                backgroundColor: chartColors.primary
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y'
        }
    });
}

// Society Sales Chart
const societySalesCtx = document.getElementById('societySalesChart');
if (societySalesCtx) {
    new Chart(societySalesCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($societyNames ?? []) !!},
            datasets: [{
                label: 'Total Sales (Million PKR)',
                data: {!! json_encode($societySalesData ?? []) !!},
                backgroundColor: chartColors.primary
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Export Report
function exportReport() {
    alert('Export functionality will be implemented with backend API');
    // window.location.href = '/reports/export?' + new URLSearchParams(window.location.search);
}

// Print Report
function printReport() {
    window.print();
}
</script>
@endpush
