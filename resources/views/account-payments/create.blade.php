@extends('layouts.app')

@section('title', 'Create Account Payment')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('account-payments.index') }}">Account Payments</a>
        <span class="separator">/</span>
        <span class="current">Create</span>
    </div>
    <h1 class="page-title">Create Account Payment</h1>
</div>

<form action="{{ route('account-payments.store') }}" method="POST">
    @csrf

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Payment Details</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Payment Type <span class="required">*</span></label>
                            <select name="type" id="typeSelect" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="commission" {{ old('type') == 'commission' ? 'selected' : '' }}>Commission Payment</option>
                                <option value="refund" {{ old('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                                <option value="salary" {{ old('type') == 'salary' ? 'selected' : '' }}>Salary</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Date <span class="required">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Payer/Payee <span class="required">*</span></label>
                        <select name="payable_id" class="form-control" required>
                            <option value="">Select Payer/Payee</option>
                            <optgroup label="Dealers">
                                @foreach($dealers as $dealer)
                                <option value="{{ $dealer->id }}" data-type="dealer" {{ old('payable_id') == $dealer->id ? 'selected' : '' }}>{{ $dealer->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Clients">
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" data-type="client" {{ old('payable_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        <input type="hidden" name="payable_type" id="payableType">
                        @error('payable_id')<span class="error-text">{{ $message }}</span>@enderror
                        @error('payable_type')<span class="error-text">{{ $message }}</span>@enderror
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

                    <div class="form-row">
                        <div class="form-group">
                            <label>Reference Number</label>
                            <input type="text" name="reference" class="form-control" placeholder="Transaction/Cheque reference" value="{{ old('reference') }}">
                            @error('reference')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Status <span class="required">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional payment details...">{{ old('notes') }}</textarea>
                        @error('notes')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Payment</button>
                <a href="{{ route('account-payments.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card info-card">
                <div class="card-body">
                    <h4 class="info-title"><i class="fas fa-lightbulb"></i> Payment Types</h4>
                    <ul class="info-list">
                        <li><strong>Commission:</strong> Dealer commissions on deals</li>
                        <li><strong>Refund:</strong> Client refunds or returns</li>
                        <li><strong>Salary:</strong> Staff salary payments</li>
                        <li><strong>Other:</strong> Miscellaneous payments</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.querySelector('select[name="payable_id"]').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const dataType = selectedOption.getAttribute('data-type');

    if (dataType === 'dealer') {
        document.getElementById('payableType').value = 'App\\Models\\Dealer';
    } else if (dataType === 'client') {
        document.getElementById('payableType').value = 'App\\Models\\Client';
    }
});

// Trigger on page load if old value exists
document.addEventListener('DOMContentLoaded', function() {
    const payableSelect = document.querySelector('select[name="payable_id"]');
    if (payableSelect.value) {
        payableSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
