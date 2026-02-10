@extends('layouts.app')

@section('title', 'Expense Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('expenses.index') }}">Expenses</a>
        <span class="separator">/</span>
        <span class="current">Details</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">Expense Details</h1>
        <div class="action-buttons">
            @can('expenses.edit')
            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
            @endcan
        </div>
    </div>
</div>

<div class="details-grid">
    <div class="details-main">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Expense Information</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Date:</span>
                        <span class="info-value">{{ date('F d, Y', strtotime($expense->date)) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Category:</span>
                        <span class="info-value"><span class="category-badge category-{{ $expense->category }}">{{ ucfirst($expense->category) }}</span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Amount:</span>
                        <span class="info-value text-primary"><strong>PKR {{ number_format($expense->amount, 2) }}</strong></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value">{{ ucfirst($expense->payment_method ?? 'Cash') }}</span>
                    </div>
                    @if($expense->reference)
                    <div class="info-item">
                        <span class="info-label">Reference Number:</span>
                        <span class="info-value">{{ $expense->reference }}</span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="info-label">Recurring:</span>
                        <span class="info-value">{{ $expense->is_recurring ? 'Yes (' . ucfirst($expense->frequency) . ')' : 'No' }}</span>
                    </div>
                    @if($expense->is_recurring && $expense->end_date)
                    <div class="info-item">
                        <span class="info-label">End Date:</span>
                        <span class="info-value">{{ date('F d, Y', strtotime($expense->end_date)) }}</span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value">{{ $expense->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>

                <div class="section-divider"></div>

                <div class="description-section">
                    <h4 class="section-subtitle"><i class="fas fa-align-left"></i> Description</h4>
                    <p>{{ $expense->description }}</p>
                </div>

                @if($expense->attachment)
                <div class="section-divider"></div>
                <div class="attachment-section">
                    <h4 class="section-subtitle"><i class="fas fa-paperclip"></i> Attachment</h4>
                    <div class="attachment-preview">
                        @php
                            $extension = pathinfo($expense->attachment, PATHINFO_EXTENSION);
                            $isPdf = strtolower($extension) === 'pdf';
                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                        @endphp

                        @if($isImage)
                            <img src="{{ asset('storage/' . $expense->attachment) }}" alt="Attachment" style="max-width: 100%; height: auto; border-radius: 8px;">
                        @elseif($isPdf)
                            <div class="pdf-preview">
                                <i class="fas fa-file-pdf" style="font-size: 48px; color: #ef4444;"></i>
                                <p style="margin-top: 10px;">PDF Document</p>
                            </div>
                        @endif

                        <div style="margin-top: 15px;">
                            <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-secondary">
                                <i class="fas fa-download"></i> Download Attachment
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="details-sidebar">
        <div class="card">
            <div class="card-body">
                <h4 class="sidebar-title"><i class="fas fa-chart-pie"></i> Quick Stats</h4>
                <div class="sidebar-stats">
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Category:</span>
                        <span class="sidebar-stat-value">{{ ucfirst($expense->category) }}</span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Amount:</span>
                        <span class="sidebar-stat-value text-danger">PKR {{ number_format($expense->amount) }}</span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Method:</span>
                        <span class="sidebar-stat-value">{{ ucfirst($expense->payment_method ?? 'Cash') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4 class="sidebar-title"><i class="fas fa-tasks"></i> Quick Actions</h4>
                <div class="sidebar-links">
                    @can('expenses.edit')
                    <a href="{{ route('expenses.edit', $expense) }}" class="sidebar-link">
                        <i class="fas fa-edit"></i> Edit Expense
                    </a>
                    @endcan
                    <a href="{{ route('expenses.index') }}" class="sidebar-link">
                        <i class="fas fa-list"></i> All Expenses
                    </a>
                    @can('expenses.create')
                    <a href="{{ route('expenses.create') }}" class="sidebar-link">
                        <i class="fas fa-plus"></i> Add New Expense
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .category-badge { display: inline-flex; padding: 6px 12px; border-radius: 16px; font-size: 0.875rem; font-weight: 600; }
    .category-utilities { background: #dbeafe; color: #1e40af; }
    .category-maintenance { background: #fef3c7; color: #92400e; }
    .category-salaries { background: #d1fae5; color: #065f46; }
    .category-marketing { background: #e9d5ff; color: #6b21a8; }
    .category-other { background: #f3f4f6; color: #6b7280; }
    .attachment-preview { padding: 20px; background: #f9fafb; border-radius: 8px; text-align: center; }
    .pdf-preview { display: flex; flex-direction: column; align-items: center; padding: 30px; }
</style>
@endpush
@endsection
