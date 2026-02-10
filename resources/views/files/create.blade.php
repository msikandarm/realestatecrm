@extends('layouts.app')

@section('title', 'Create Property File')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('files.index') }}">Files</a>
        <span class="separator">/</span>
        <span class="current">Create</span>
    </div>
    <h1 class="page-title">Create Property File</h1>
</div>

<form action="{{ route('files.store') }}" method="POST">
    @csrf

    <div class="form-grid">
        <div class="form-main">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> File Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Property Type <span class="required">*</span></label>
                        <select name="type" id="typeSelect" class="form-control" onchange="toggleType()" required>
                            <option value="">Select Type</option>
                            <option value="property" {{ old('type') == 'property' ? 'selected' : '' }}>Property</option>
                            <option value="plot" {{ old('type') == 'plot' ? 'selected' : '' }}>Plot</option>
                        </select>
                        @error('type')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div id="propertyGroup" class="form-group" style="display: none;">
                        <label>Property <span class="required">*</span></label>
                        <select name="property_id" id="propertySelect" class="form-control">
                            <option value="">Select Property</option>
                            @foreach($properties as $property)
                            <option value="{{ $property->id }}" data-price="{{ $property->price }}">{{ $property->title }} - PKR {{ number_format($property->price) }}</option>
                            @endforeach
                        </select>
                        @error('property_id')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div id="plotGroup" class="form-group" style="display: none;">
                        <label>Plot <span class="required">*</span></label>
                        <select name="plot_id" id="plotSelect" class="form-control">
                            <option value="">Select Plot</option>
                            @foreach($plots as $plot)
                            <option value="{{ $plot->id }}" data-price="{{ $plot->price }}">Plot #{{ $plot->plot_number }} ({{ $plot->size }} {{ $plot->unit }}) - PKR {{ number_format($plot->price) }}</option>
                            @endforeach
                        </select>
                        @error('plot_id')<span class="error-text">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Client <span class="required">*</span></label>
                        <select name="client_id" class="form-control" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id')<span class="error-text">{{ $message }}</span>@enderror
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
                            <input type="number" name="total_amount" id="totalAmount" class="form-control" placeholder="0" value="{{ old('total_amount') }}" onkeyup="calculateInstallment()" required>
                            @error('total_amount')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Down Payment <span class="required">*</span></label>
                            <input type="number" name="down_payment" id="downPayment" class="form-control" placeholder="0" value="{{ old('down_payment') }}" onkeyup="calculateInstallment()" required>
                            @error('down_payment')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Number of Installments <span class="required">*</span></label>
                            <input type="number" name="total_installments" id="totalInstallments" class="form-control" placeholder="0" min="1" value="{{ old('total_installments') }}" onkeyup="calculateInstallment()" required>
                            @error('total_installments')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Installment Amount (Auto-Calculated)</label>
                            <input type="number" name="installment_amount" id="installmentAmount" class="form-control" placeholder="0" value="{{ old('installment_amount') }}" readonly>
                            @error('installment_amount')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date <span class="required">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                            @error('start_date')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label>Payment Frequency <span class="required">*</span></label>
                            <select name="frequency" class="form-control" required>
                                <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('frequency') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('frequency')<span class="error-text">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                        @error('notes')<span class="error-text">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create File</button>
                <a href="{{ route('files.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </div>

        <div class="form-sidebar">
            <div class="card info-card">
                <div class="card-body">
                    <h4 class="info-title"><i class="fas fa-calculator"></i> Payment Summary</h4>
                    <div class="info-item">
                        <span class="info-label">Total Amount:</span>
                        <span class="info-value" id="summaryTotal">PKR 0</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Down Payment:</span>
                        <span class="info-value" id="summaryDown">PKR 0</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Remaining:</span>
                        <span class="info-value" id="summaryRemaining">PKR 0</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Per Installment:</span>
                        <span class="info-value text-success" id="summaryInstallment">PKR 0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function toggleType() {
    const type = document.getElementById('typeSelect').value;
    const propertyGroup = document.getElementById('propertyGroup');
    const plotGroup = document.getElementById('plotGroup');
    const propertySelect = document.getElementById('propertySelect');
    const plotSelect = document.getElementById('plotSelect');

    if (type === 'property') {
        propertyGroup.style.display = 'block';
        plotGroup.style.display = 'none';
        propertySelect.required = true;
        plotSelect.required = false;
        plotSelect.value = '';
    } else if (type === 'plot') {
        propertyGroup.style.display = 'none';
        plotGroup.style.display = 'block';
        propertySelect.required = false;
        plotSelect.required = true;
        propertySelect.value = '';
    } else {
        propertyGroup.style.display = 'none';
        plotGroup.style.display = 'none';
        propertySelect.required = false;
        plotSelect.required = false;
    }
}

document.getElementById('propertySelect')?.addEventListener('change', function() {
    const price = this.selectedOptions[0]?.dataset.price || 0;
    document.getElementById('totalAmount').value = price;
    calculateInstallment();
});

document.getElementById('plotSelect')?.addEventListener('change', function() {
    const price = this.selectedOptions[0]?.dataset.price || 0;
    document.getElementById('totalAmount').value = price;
    calculateInstallment();
});

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

document.addEventListener('DOMContentLoaded', () => {
    toggleType();
    calculateInstallment();
});
</script>
@endpush
@endsection
