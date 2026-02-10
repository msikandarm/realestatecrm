@extends('layouts.app')

@section('title', 'Create Deal')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('deals.index') }}">Deals</a>
        <span class="separator">/</span>
        <span class="current">Create</span>
    </div>
    <h1 class="page-title">Create New Deal</h1>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('deals.store') }}">
    @csrf

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div>
                    <h3 class="section-title">Deal Information</h3>
                    <p class="section-description">Enter deal details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="deal_type">Deal Type *</label>
                    <select id="deal_type" name="deal_type" required onchange="toggleDealType()">
                        <option value="">Select Type</option>
                        <option value="property" {{ old('deal_type') == 'property' ? 'selected' : '' }}>Property</option>
                        <option value="plot" {{ old('deal_type') == 'plot' ? 'selected' : '' }}>Plot</option>
                    </select>
                </div>

                <div class="form-group" id="property_group" style="display: none;">
                    <label for="property_id">Property *</label>
                    <select id="property_id" name="property_id">
                        <option value="">Select Property</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                {{ $property->title }} - PKR {{ number_format($property->price) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="plot_group" style="display: none;">
                    <label for="plot_id">Plot *</label>
                    <select id="plot_id" name="plot_id">
                        <option value="">Select Plot</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}" {{ old('plot_id') == $plot->id ? 'selected' : '' }}>
                                Plot #{{ $plot->plot_number }} - {{ $plot->size }} {{ $plot->unit }} - PKR {{ number_format($plot->price) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="client_id">Client *</label>
                    <select id="client_id" name="client_id" required>
                        <option value="">Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} - {{ $client->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="dealer_id">Dealer *</label>
                    <select id="dealer_id" name="dealer_id" required onchange="updateCommission()">
                        <option value="">Select Dealer</option>
                        @foreach($dealers as $dealer)
                            <option value="{{ $dealer->id }}" data-commission="{{ $dealer->commission_rate }}" {{ old('dealer_id') == $dealer->id ? 'selected' : '' }}>
                                {{ $dealer->user->name ?? 'Dealer' }} - {{ $dealer->commission_rate }}%
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Deal Amount (PKR) *</label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}" placeholder="e.g., 5000000" step="0.01" required onkeyup="updateCommission()">
                </div>

                <div class="form-group">
                    <label for="commission_amount">Commission Amount (PKR)</label>
                    <input type="number" id="commission_amount" name="commission_amount" value="{{ old('commission_amount') }}" placeholder="Auto-calculated" step="0.01" readonly>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Enter any additional notes">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('deals.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Deal
            </button>
        </div>
    </div>
</form>

@push('styles')
<style>
    .form-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden; }
    .form-section { padding: 30px; border-bottom: 1px solid #e5e7eb; }
    .form-section:last-child { border-bottom: none; }
    .section-header { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 25px; }
    .section-icon { width: 48px; height: 48px; background: linear-gradient(135deg, var(--success), #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; flex-shrink: 0; }
    .section-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; }
    .section-description { font-size: 0.875rem; color: var(--gray-600); margin: 4px 0 0 0; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-group label { font-weight: 600; color: var(--gray-700); margin-bottom: 8px; font-size: 0.95rem; }
    .form-group input, .form-group select, .form-group textarea { padding: 12px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s; font-family: inherit; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--success); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
    .form-group input[readonly] { background: var(--gray-100); color: var(--gray-600); }
    .form-group textarea { resize: vertical; }
    .form-actions { display: flex; justify-content: flex-end; gap: 15px; padding: 25px 30px; background: var(--gray-50); }
    .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px; }
    .alert i { font-size: 1.25rem; margin-top: 2px; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert ul { margin: 8px 0 0 0; padding-left: 20px; }
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-actions { flex-direction: column; }
        .form-actions .btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@push('scripts')
<script>
function toggleDealType() {
    const dealType = document.getElementById('deal_type').value;
    const propertyGroup = document.getElementById('property_group');
    const plotGroup = document.getElementById('plot_group');
    const propertySelect = document.getElementById('property_id');
    const plotSelect = document.getElementById('plot_id');

    if (dealType === 'property') {
        propertyGroup.style.display = 'flex';
        plotGroup.style.display = 'none';
        propertySelect.required = true;
        plotSelect.required = false;
        plotSelect.value = '';
    } else if (dealType === 'plot') {
        plotGroup.style.display = 'flex';
        propertyGroup.style.display = 'none';
        plotSelect.required = true;
        propertySelect.required = false;
        propertySelect.value = '';
    } else {
        propertyGroup.style.display = 'none';
        plotGroup.style.display = 'none';
        propertySelect.required = false;
        plotSelect.required = false;
    }
}

function updateCommission() {
    const dealerSelect = document.getElementById('dealer_id');
    const amountInput = document.getElementById('amount');
    const commissionInput = document.getElementById('commission_amount');

    if (dealerSelect.value && amountInput.value) {
        const selectedOption = dealerSelect.options[dealerSelect.selectedIndex];
        const commissionRate = parseFloat(selectedOption.getAttribute('data-commission') || 0);
        const amount = parseFloat(amountInput.value);
        const commission = (amount * commissionRate) / 100;

        commissionInput.value = commission.toFixed(2);
    }
}

const oldDealType = "{{ old('deal_type') }}";
if (oldDealType) {
    toggleDealType();
}
</script>
@endpush
@endsection
