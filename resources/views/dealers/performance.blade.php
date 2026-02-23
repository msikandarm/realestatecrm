@extends('layouts.app')

@section('title', 'Dealer Performance')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <i class="fas fa-chevron-right"></i>
        <a href="{{ route('dealers.index') }}">Dealers</a>
        <i class="fas fa-chevron-right"></i>
        <a href="{{ route('dealers.show', $dealer) }}">{{ $dealer->user->name ?? 'Dealer' }}</a>
        <i class="fas fa-chevron-right"></i>
        <span>Performance</span>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
        <div>
            <h1 class="page-title">{{ $dealer->user->name ?? 'Dealer' }} - Performance Report</h1>
            <p class="page-subtitle">View dealer performance metrics and statistics</p>
        </div>
        <a href="{{ route('dealers.show', $dealer) }}" class="btn btn-light">
            <i class="fas fa-arrow-left"></i> Back to Dealer
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    @include('components.stat-card', [
        'icon' => 'fas fa-handshake',
        'value' => $performanceData['total_deals'] ?? 0,
        'label' => 'Total Deals',
        'bgColor' => 'primary'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-check-circle',
        'value' => $performanceData['completed_deals'] ?? 0,
        'label' => 'Completed',
        'bgColor' => 'success'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-money-bill-wave',
        'value' => 'PKR ' . number_format($performanceData['total_commission'] ?? 0),
        'label' => 'Total Commission',
        'bgColor' => 'warning'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-percentage',
        'value' => ($performanceData['success_rate'] ?? 0) . '%',
        'label' => 'Success Rate',
        'bgColor' => 'info'
    ])
</div>

<!-- Charts Grid -->
<div class="performance-grid">
    <div class="chart-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Monthly Performance
            </h3>
        </div>
        <div class="card-body">
            <canvas id="monthlyChart" height="300"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i> Deal Status Distribution
            </h3>
        </div>
        <div class="card-body">
            <canvas id="statusChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Commission Breakdown Table -->
<div class="details-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table"></i> Commission Breakdown
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total Deals</th>
                        <th>Completed</th>
                        <th>Total Amount</th>
                        <th>Commission Earned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyData ?? [] as $data)
                        <tr>
                            <td><strong>{{ $data['month'] }}</strong></td>
                            <td>{{ $data['total_deals'] }}</td>
                            <td><span class="badge badge-success">{{ $data['completed_deals'] }}</span></td>
                            <td>PKR {{ number_format($data['total_amount']) }}</td>
                            <td><strong class="text-success">PKR {{ number_format($data['commission']) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="totals-row">
                        <td><strong>Total</strong></td>
                        <td><strong>{{ $performanceData['total_deals'] ?? 0 }}</strong></td>
                        <td><strong>{{ $performanceData['completed_deals'] ?? 0 }}</strong></td>
                        <td><strong>PKR {{ number_format($performanceData['total_amount'] ?? 0) }}</strong></td>
                        <td><strong class="text-success">PKR {{ number_format($performanceData['total_commission'] ?? 0) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
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

    .performance-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 25px;
        margin-bottom: 25px;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-title i {
        color: var(--primary);
    }

    .card-body {
        padding: 25px;
    }

    .details-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
    }

    .details-card .card-body {
        padding: 0;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 14px 20px;
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

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .totals-row {
        background: var(--gray-50);
    }

    .totals-row td {
        padding: 16px 20px !important;
        font-weight: 600;
    }

    .text-success {
        color: var(--success);
    }

    .text-center {
        text-align: center;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-light {
        background: white;
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }

    .btn-light:hover {
        background: var(--gray-50);
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .performance-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const monthlyData = @json($monthlyData ?? []);
const statusData = @json($statusDistribution ?? []);

// Monthly Performance Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Deals',
            data: monthlyData.map(d => d.total_deals),
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Commission (PKR)',
            data: monthlyData.map(d => d.commission),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Deals' } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Commission (PKR)' } }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(statusData),
        datasets: [{
            data: Object.values(statusData),
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6366f1', '#8b5cf6']
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
</script>
@endpush
@endsection
