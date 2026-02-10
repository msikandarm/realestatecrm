@extends('layouts.app')

@section('title', 'Add Expense')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('expenses.index') }}">Expenses</a>
        <span class="separator">/</span>
        <span class="current">Add</span>
    </div>
    <h1 class="page-title">Add New Expense</h1>
</div>

<form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

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
                                <option value="utilities" {{ old('category') == 'utilities' ? 'selected' : '' }}>Utilities</option>
                                <option value="maintenance" {{ old('category') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="salaries" {{ old('category') == 'salaries' ? 'selected' : '' }}>Salaries</option>
                                <option value="marketing" {{ old('category') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('category')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Date <span class="required">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Amount (PKR) <span class="required">*</span></label>
                            <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" value="{{ old('amount') }}" required>
                            @error('amount')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Payment Method <span class="required">*</span></label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                            </select>
                            @error('payment_method')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description <span class="required">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Expense description..." required>{{ old('description') }}</textarea>
                        @error('description')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" name="reference" class="form-control" placeholder="Invoice/Receipt number" value="{{ old('reference') }}">
                        @error('reference')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Attachment (Invoice/Receipt)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Supported formats: PDF, JPG, PNG (Max 5MB)</small>
                        @error('attachment')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_recurring" id="isRecurring" value="1" {{ old('is_recurring') ? 'checked' : '' }} onchange="toggleRecurring()">
                            <span>This is a recurring expense</span>
                        </label>
                    </div>

                    <div id="recurringFields" style="display: none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Frequency</label>
                                <select name="frequency" class="form-control">
                                    <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="yearly" {{ old('frequency') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('frequency')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                @error('end_date')<span class="error-text">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Expense</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card info-card">
                <div class="card-body">
                    <h4 class="info-title"><i class="fas fa-lightbulb"></i> Tips</h4>
                    <ul class="info-list">
                        <li>Select the appropriate category for better tracking</li>
                        <li>Upload invoices or receipts as proof</li>
                        <li>Use recurring option for monthly bills</li>
                        <li>Add reference numbers for easy lookup</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

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
