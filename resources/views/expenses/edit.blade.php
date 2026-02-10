@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('expenses.index') }}">Expenses</a>
        <span class="separator">/</span>
        <span class="current">Edit</span>
    </div>
    <h1 class="page-title">Edit Expense</h1>
</div>

<form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Expense Details</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="utilities" {{ old('category', $expense->category) == 'utilities' ? 'selected' : '' }}>Utilities</option>
                                <option value="maintenance" {{ old('category', $expense->category) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="salaries" {{ old('category', $expense->category) == 'salaries' ? 'selected' : '' }}>Salaries</option>
                                <option value="marketing" {{ old('category', $expense->category) == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                <option value="other" {{ old('category', $expense->category) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('category')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Date <span class="required">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', $expense->date) }}" required>
                            @error('date')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Amount (PKR) <span class="required">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" value="{{ old('amount', $expense->amount) }}" required>
                            @error('amount')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Payment Method <span class="required">*</span></label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ old('payment_method', $expense->payment_method) == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ old('payment_method', $expense->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="online" {{ old('payment_method', $expense->payment_method) == 'online' ? 'selected' : '' }}>Online Payment</option>
                            </select>
                            @error('payment_method')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description <span class="required">*</span></label>
                        <textarea name="description" class="form-control" rows="3" required>{{ old('description', $expense->description) }}</textarea>
                        @error('description')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" name="reference" class="form-control" value="{{ old('reference', $expense->reference) }}">
                        @error('reference')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    @if($expense->attachment)
                    <div class="form-group">
                        <label>Current Attachment</label>
                        <div class="current-attachment">
                            <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="attachment-link">
                                <i class="fas fa-paperclip"></i> View Current Attachment
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="form-group">
                        <label>{{ $expense->attachment ? 'Replace Attachment' : 'Add Attachment' }}</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Supported formats: PDF, JPG, PNG (Max 5MB)</small>
                        @error('attachment')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_recurring" id="isRecurring" value="1" {{ old('is_recurring', $expense->is_recurring) ? 'checked' : '' }} onchange="toggleRecurring()">
                            <span>This is a recurring expense</span>
                        </label>
                    </div>

                    <div id="recurringFields" style="display: {{ old('is_recurring', $expense->is_recurring) ? 'block' : 'none' }};">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Frequency</label>
                                <select name="frequency" class="form-control">
                                    <option value="monthly" {{ old('frequency', $expense->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('frequency', $expense->frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="yearly" {{ old('frequency', $expense->frequency) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('frequency')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $expense->end_date) }}">
                                @error('end_date')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Expense</button>
                <a href="{{ route('expenses.show', $expense) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card info-card">
                <div class="card-body">
                    <h4 class="info-title"><i class="fas fa-info-circle"></i> Expense Info</h4>
                    <div class="info-item">
                        <span class="info-label">Created:</span>
                        <span class="info-value">{{ $expense->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value">{{ $expense->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
<style>
    .current-attachment { padding: 12px; background: #f9fafb; border-radius: 8px; }
    .attachment-link { display: inline-flex; align-items: center; gap: 8px; color: #667eea; text-decoration: none; font-weight: 500; }
    .attachment-link:hover { color: #764ba2; }
</style>
@endpush

@push('scripts')
<script>
function toggleRecurring() {
    const isRecurring = document.getElementById('isRecurring').checked;
    document.getElementById('recurringFields').style.display = isRecurring ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', toggleRecurring);
</script>
@endpush
@endsection
