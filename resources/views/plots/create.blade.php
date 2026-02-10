@extends('layouts.app')

@section('title', 'Add New Plot')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('plots.index') }}">Plots</a>
        <span class="separator">/</span>
        <span class="current">Add New</span>
    </div>
    <h1 class="page-title">Add New Plot</h1>
    <p class="page-subtitle">Create a new plot in a street</p>
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

<form method="POST" action="{{ route('plots.store') }}" id="plotForm">
    @csrf

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h3 class="section-title">Location Details</h3>
                    <p class="section-description">Select plot location</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="society_id">Society *</label>
                    <select id="society_id" name="society_id" required>
                        <option value="">Select Society</option>
                        @foreach($societies as $society)
                            <option value="{{ $society->id }}" {{ old('society_id', request('society_id')) == $society->id ? 'selected' : '' }}>
                                {{ $society->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="block_id">Block *</label>
                    <select id="block_id" name="block_id" required disabled>
                        <option value="">Select Block</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="street_id">Street *</label>
                    <select id="street_id" name="street_id" required disabled>
                        <option value="">Select Street</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-th"></i>
                </div>
                <div>
                    <h3 class="section-title">Plot Information</h3>
                    <p class="section-description">Enter plot details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="plot_number">Plot Number *</label>
                    <input type="text" id="plot_number" name="plot_number"
                           value="{{ old('plot_number') }}"
                           placeholder="e.g., 123, A-45"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="size">Plot Size *</label>
                    <input type="number" id="size" name="size"
                           value="{{ old('size') }}"
                           placeholder="e.g., 5"
                           step="0.01"
                           required>
                </div>

                <div class="form-group">
                    <label for="unit">Unit *</label>
                    <select id="unit" name="unit" required>
                        <option value="">Select Unit</option>
                        <option value="marla" {{ old('unit') == 'marla' ? 'selected' : '' }}>Marla</option>
                        <option value="kanal" {{ old('unit') == 'kanal' ? 'selected' : '' }}>Kanal</option>
                        <option value="sqft" {{ old('unit') == 'sqft' ? 'selected' : '' }}>Square Feet</option>
                        <option value="sqyd" {{ old('unit') == 'sqyd' ? 'selected' : '' }}>Square Yards</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price (PKR) *</label>
                    <input type="number" id="price" name="price"
                           value="{{ old('price') }}"
                           placeholder="e.g., 5000000"
                           step="0.01"
                           required>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="sold" {{ old('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="type">Plot Type</label>
                    <select id="type" name="type">
                        <option value="">Select Type</option>
                        <option value="residential" {{ old('type') == 'residential' ? 'selected' : '' }}>Residential</option>
                        <option value="commercial" {{ old('type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="agricultural" {{ old('type') == 'agricultural' ? 'selected' : '' }}>Agricultural</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Enter plot description">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('plots.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Plot
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
    .form-group textarea { resize: vertical; }
    .error-message { color: var(--danger); font-size: 0.8rem; margin-top: 5px; }
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
const allBlocks = @json($blocks);
const allStreets = @json($streets);
const selectedBlockId = "{{ old('block_id', request('block_id')) }}";
const selectedStreetId = "{{ old('street_id', request('street_id')) }}";

document.getElementById('society_id').addEventListener('change', function() {
    const societyId = this.value;
    const blockSelect = document.getElementById('block_id');
    const streetSelect = document.getElementById('street_id');

    blockSelect.innerHTML = '<option value="">Select Block</option>';
    streetSelect.innerHTML = '<option value="">Select Street</option>';
    streetSelect.disabled = true;

    if (societyId) {
        const societyBlocks = allBlocks.filter(block => block.society_id == societyId);
        societyBlocks.forEach(block => {
            const option = document.createElement('option');
            option.value = block.id;
            option.textContent = block.name;
            if (selectedBlockId == block.id) option.selected = true;
            blockSelect.appendChild(option);
        });
        blockSelect.disabled = false;
        if (selectedBlockId) blockSelect.dispatchEvent(new Event('change'));
    } else {
        blockSelect.disabled = true;
    }
});

document.getElementById('block_id').addEventListener('change', function() {
    const blockId = this.value;
    const streetSelect = document.getElementById('street_id');

    streetSelect.innerHTML = '<option value="">Select Street</option>';

    if (blockId) {
        const blockStreets = allStreets.filter(street => street.block_id == blockId);
        blockStreets.forEach(street => {
            const option = document.createElement('option');
            option.value = street.id;
            option.textContent = street.name;
            if (selectedStreetId == street.id) option.selected = true;
            streetSelect.appendChild(option);
        });
        streetSelect.disabled = false;
    } else {
        streetSelect.disabled = true;
    }
});

if (document.getElementById('society_id').value) {
    document.getElementById('society_id').dispatchEvent(new Event('change'));
}
</script>
@endpush
@endsection
