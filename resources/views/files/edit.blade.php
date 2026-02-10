@extends('layouts.app')

@section('title', 'Edit Property File')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('files.index') }}">Files</a>
        <span class="separator">/</span>
        <span class="current">Edit #{{ $file->id }}</span>
    </div>
    <h1 class="page-title">Edit Property File #{{ $file->id }}</h1>
</div>

<form action="{{ route('files.update', $file) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> File Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Property/Plot</label>
                        <input type="text" class="form-control" value="@if($file->property){{ $file->property->title }}@elseif($file->plot)Plot #{{ $file->plot->plot_number }}@endif" readonly>
                        <small class="form-text text-muted">Property/Plot cannot be changed after file creation</small>
                    </div>

                    <div class="form-group">
                        <label>Client</label>
                        <input type="text" class="form-control" value="{{ $file->client->name }}" readonly>
                        <small class="form-text text-muted">Client cannot be changed after file creation</small>
                    </div>

                    <div class="form-group">
                        <label>Status <span class="required">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="active" {{ old('status', $file->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status', $file->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $file->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-check-alt"></i> Payment Plan</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Total Amount <span class="required">*</span></label>
                            <input type="number" name="total_amount" id="totalAmount" class="form-control" value="{{ old('total_amount', $file->total_amount) }}" onkeyup="calculateInstallment()" required>
                            @error('total_amount')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Down Payment <span class="required">*</span></label>
                            <input type="number" name="down_payment" id="downPayment" class="form-control" value="{{ old('down_payment', $file->down_payment) }}" onkeyup="calculateInstallment()" required>
                            @error('down_payment')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Number of Installments <span class="required">*</span></label>
                            <input type="number" name="total_installments" id="totalInstallments" class="form-control" value="{{ old('total_installments', $file->total_installments) }}" min="1" onkeyup="calculateInstallment()" required>
                            @error('total_installments')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Installment Amount (Auto-Calculated)</label>
                            <input type="number" name="installment_amount" id="installmentAmount" class="form-control" value="{{ old('installment_amount', $file->installment_amount) }}" readonly>
                            @error('installment_amount')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date <span class="required">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $file->start_date) }}" required>
                            @error('start_date')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Payment Frequency <span class="required">*</span></label>
                            <select name="frequency" class="form-control" required>
                                <option value="monthly" {{ old('frequency', $file->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('frequency', $file->frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('frequency', $file->frequency) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('frequency')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $file->notes) }}</textarea>
                        @error('notes')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update File</button>
                <a href="{{ route('files.show', $file) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card info-card">
                <div class="card-body">
                    <h4 class="info-title"><i class="fas fa-calculator"></i> Payment Summary</h4>
                    <div class="info-item">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value" id="summaryTotal">PKR {{ number_format($file->total_amount) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Down Payment:</span>
                        <span class="info-value" id="summaryDown">PKR {{ number_format($file->down_payment) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Remaining:</span>
                        <span class="info-value" id="summaryRemaining">PKR {{ number_format($file->total_amount - $file->down_payment) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Per Installment:</span>
                        <span class="info-value text-success" id="summaryInstallment">PKR {{ number_format($file->installment_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function calculateInstallment() {
    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    const down = parseFloat(document.getElementById('downPayment').value) || 0;
    const installments = parseInt(document.getElementById('totalInstallments').value) || 0;

    const remaining = total - down;
    const installmentAmount = installments > 0 ? remaining / installments : 0;

    document.getElementById('installmentAmount').value = installmentAmount.toFixed(2);
    document.getElementById('summaryTotal').textContent = 'PKR ' + total.toLocaleString();
    document.getElementById('summaryDown').textContent = 'PKR ' + down.toLocaleString();
    document.getElementById('summaryRemaining').textContent = 'PKR ' + remaining.toLocaleString();
    document.getElementById('summaryInstallment').textContent = 'PKR ' + installmentAmount.toLocaleString();
}

document.addEventListener('DOMContentLoaded', calculateInstallment);
</script>
@endpush
@endsection
