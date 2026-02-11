@extends('layouts.app')

@section('title', 'Edit Plot')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('plots.index') }}">Plots</a>
        <span class="separator">/</span>
        <span class="current">Edit #{{ $plot->plot_number }}</span>
    </div>
    <h1 class="page-title">Edit Plot #{{ $plot->plot_number }}</h1>
    <p class="page-subtitle">Update plot information</p>
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

<form method="POST" action="{{ route('plots.update', $plot) }}">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h3 class="section-title">Location Details</h3>
                    <p class="section-description">Update plot location</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="society_id">Society *</label>
                    <select id="society_id" name="society_id" required>
                        <option value="">Select Society</option>
                        @foreach($societies as $society)
                            <option value="{{ $society->id }}" {{ old('society_id', $plot->street->block->society_id) == $society->id ? 'selected' : '' }}>
                                {{ $society->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="block_id">Block *</label>
                    <select id="block_id" name="block_id" required>
                        <option value="">Select Block</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="street_id">Street *</label>
                    <select id="street_id" name="street_id" required>
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
                    <p class="section-description">Update plot details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="plot_number">Plot Number *</label>
                    <input type="text" id="plot_number" name="plot_number"
                           value="{{ old('plot_number', $plot->plot_number) }}"
                           placeholder="e.g., 123, A-45"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="area">Plot Size *</label>
                    <input type="number" id="area" name="area"
                           value="{{ old('area', $plot->area ?? $plot->size) }}"
                           placeholder="e.g., 5"
                           step="0.01"
                           required>
                </div>

                <div class="form-group">
                    <label for="area_unit">Unit *</label>
                    <select id="area_unit" name="area_unit" required>
                        <option value="">Select Unit</option>
                        <option value="marla" {{ old('area_unit', $plot->area_unit ?? $plot->unit) == 'marla' ? 'selected' : '' }}>Marla</option>
                        <option value="kanal" {{ old('area_unit', $plot->area_unit ?? $plot->unit) == 'kanal' ? 'selected' : '' }}>Kanal</option>
                        <option value="acre" {{ old('area_unit', $plot->area_unit) == 'acre' ? 'selected' : '' }}>Acre</option>
                        <option value="sq ft" {{ old('area_unit', $plot->area_unit ?? $plot->unit) == 'sq ft' ? 'selected' : '' }}>Square Feet</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="total_price">Total Price (PKR)</label>
                    <input type="number" id="total_price" name="total_price"
                           value="{{ old('total_price', $plot->total_price ?? $plot->price) }}"
                           placeholder="e.g., 5000000"
                           step="0.01">
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="available" {{ old('status', $plot->status) == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="booked" {{ old('status', $plot->status) == 'booked' ? 'selected' : '' }}>Booked</option>
                        <option value="sold" {{ old('status', $plot->status) == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="on-hold" {{ old('status', $plot->status) == 'on-hold' ? 'selected' : '' }}>On Hold</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="type">Plot Type</label>
                    <select id="type" name="type">
                        <option value="">Select Type</option>
                        <option value="residential" {{ old('type', $plot->type) == 'residential' ? 'selected' : '' }}>Residential</option>
                        <option value="commercial" {{ old('type', $plot->type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="industrial" {{ old('type', $plot->type) == 'industrial' ? 'selected' : '' }}>Industrial</option>
                        <option value="agricultural" {{ old('type', $plot->type) == 'agricultural' ? 'selected' : '' }}>Agricultural</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="width">Width (optional)</label>
                    <input type="number" id="width" name="width" value="{{ old('width', $plot->width) }}" step="0.01">
                </div>

                <div class="form-group">
                    <label for="length">Length (optional)</label>
                    <input type="number" id="length" name="length" value="{{ old('length', $plot->length) }}" step="0.01">
                </div>

                <div class="form-group">
                    <label for="corner">Corner *</label>
                    <select id="corner" name="corner" required>
                        <option value="">Select</option>
                        <option value="yes" {{ old('corner', $plot->corner) == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ old('corner', $plot->corner) == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="park_facing">Park Facing *</label>
                    <select id="park_facing" name="park_facing" required>
                        <option value="">Select</option>
                        <option value="yes" {{ old('park_facing', $plot->park_facing) == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ old('park_facing', $plot->park_facing) == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="main_road_facing">Main Road Facing *</label>
                    <select id="main_road_facing" name="main_road_facing" required>
                        <option value="">Select</option>
                        <option value="yes" {{ old('main_road_facing', $plot->main_road_facing) == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ old('main_road_facing', $plot->main_road_facing) == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="facing">Facing</label>
                    <select id="facing" name="facing">
                        <option value="">Select Facing</option>
                        <option value="north" {{ old('facing', $plot->facing) == 'north' ? 'selected' : '' }}>North</option>
                        <option value="south" {{ old('facing', $plot->facing) == 'south' ? 'selected' : '' }}>South</option>
                        <option value="east" {{ old('facing', $plot->facing) == 'east' ? 'selected' : '' }}>East</option>
                        <option value="west" {{ old('facing', $plot->facing) == 'west' ? 'selected' : '' }}>West</option>
                        <option value="north-east" {{ old('facing', $plot->facing) == 'north-east' ? 'selected' : '' }}>North-East</option>
                        <option value="north-west" {{ old('facing', $plot->facing) == 'north-west' ? 'selected' : '' }}>North-West</option>
                        <option value="south-east" {{ old('facing', $plot->facing) == 'south-east' ? 'selected' : '' }}>South-East</option>
                        <option value="south-west" {{ old('facing', $plot->facing) == 'south-west' ? 'selected' : '' }}>South-West</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Enter plot description">{{ old('description', $plot->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('plots.show', $plot) }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Plot
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
    .section-icon { width: 48px; height: 48px; background: linear-gradient(135deg, var(--warning), #f59e0b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; flex-shrink: 0; }
    .section-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; }
    .section-description { font-size: 0.875rem; color: var(--gray-600); margin: 4px 0 0 0; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-group label { font-weight: 600; color: var(--gray-700); margin-bottom: 8px; font-size: 0.95rem; }
    .form-group input, .form-group select, .form-group textarea { padding: 12px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s; font-family: inherit; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--warning); box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
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
const allBlocks = @json($blocks);
const allStreets = @json($streets);
const selectedSocietyId = "{{ old('society_id', $plot->street->block->society_id) }}";
const selectedBlockId = "{{ old('block_id', $plot->street->block_id) }}";
const selectedStreetId = "{{ old('street_id', $plot->street_id) }}";

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

document.getElementById('society_id').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection
