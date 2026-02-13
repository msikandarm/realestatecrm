@extends('layouts.app')

@section('title', 'Add New Property')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('properties.index') }}">Properties</a>
        <span class="separator">/</span>
        <span class="current">Add New</span>
    </div>
    <h1 class="page-title">Add New Property</h1>
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

<form method="POST" action="{{ route('properties.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="section-title">Basic Information</h3>
                    <p class="section-description">Enter property details</p>
                </div>
            </div>

            <div class="form-grid">

                <div class="form-group full-width">
                    <label for="title">Property Title *</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="e.g., 5 Marla House in DHA Phase 6" required autofocus>
                </div>

                <div class="form-group">
                    <label for="reference_code">Reference Code *</label>
                    <input type="text" id="reference_code" name="reference_code" value="{{ old('reference_code') }}" placeholder="Unique reference code" required>
                </div>

                <div class="form-group">
                    <label for="type">Property Type *</label>
                    <select id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="house" {{ old('type') == 'house' ? 'selected' : '' }}>House</option>
                        <option value="apartment" {{ old('type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="commercial" {{ old('type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="plot" {{ old('type') == 'plot' ? 'selected' : '' }}>Plot</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="property_for">Property For *</label>
                    <select id="property_for" name="property_for" required>
                        <option value="">Select</option>
                        <option value="sale" {{ old('property_for') == 'sale' ? 'selected' : '' }}>Sale</option>
                        <option value="rent" {{ old('property_for') == 'rent' ? 'selected' : '' }}>Rent</option>
                        <option value="both" {{ old('property_for') == 'both' ? 'selected' : '' }}>Both</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="condition">Condition *</label>
                    <select id="condition" name="condition" required>
                        <option value="">Select Condition</option>
                        <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                        <option value="old" {{ old('condition') == 'old' ? 'selected' : '' }}>Old</option>
                        <option value="under_construction" {{ old('condition') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="rented" {{ old('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="sold" {{ old('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="under_negotiation" {{ old('status') == 'under_negotiation' ? 'selected' : '' }}>Under Negotiation</option>
                        <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="off_market" {{ old('status') == 'off_market' ? 'selected' : '' }}>Off Market</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price (PKR) *</label>
                    <input type="number" id="price" name="price" value="{{ old('price') }}" placeholder="e.g., 15000000" step="0.01" required>
                </div>

                <div class="form-group full-width">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="e.g., DHA Phase 6, Lahore" required>
                </div>

                <div class="form-group">
                    <label for="society_id">Society</label>
                    <select id="society_id" name="society_id">
                        <option value="">Select Society</option>
                        @foreach($societies as $society)
                            <option value="{{ $society->id }}" {{ old('society_id') == $society->id ? 'selected' : '' }}>{{ $society->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="block_id">Block</label>
                    <select id="block_id" name="block_id">
                        <option value="">Select Block</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="street_id">Street</label>
                    <select id="street_id" name="street_id">
                        <option value="">Select Street</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="plot_id">Plot (optional)</label>
                    <select id="plot_id" name="plot_id">
                        <option value="">Select Plot</option>
                        @foreach($plots as $plot)
                            <option value="{{ $plot->id }}" {{ old('plot_id') == $plot->id ? 'selected' : '' }}>{{ $plot->reference_code ?? 'Plot #' . $plot->id }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Enter detailed property description">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-ruler-combined"></i>
                </div>
                <div>
                    <h3 class="section-title">Property Details</h3>
                    <p class="section-description">Specify property features</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="bedrooms">Bedrooms</label>
                    <input type="number" id="bedrooms" name="bedrooms" value="{{ old('bedrooms') }}" placeholder="Number of bedrooms" min="0">
                </div>

                <div class="form-group">
                    <label for="bathrooms">Bathrooms</label>
                    <input type="number" id="bathrooms" name="bathrooms" value="{{ old('bathrooms') }}" placeholder="Number of bathrooms" min="0">
                </div>

                <div class="form-group">
                    <label for="size">Area / Size *</label>
                    <input type="number" id="size" name="size" value="{{ old('size') }}" placeholder="e.g., 5" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="size_unit">Area Unit *</label>
                    <select id="size_unit" name="size_unit" required>
                        <option value="marla" {{ old('size_unit', 'marla') == 'marla' ? 'selected' : '' }}>Marla</option>
                        <option value="kanal" {{ old('size_unit') == 'kanal' ? 'selected' : '' }}>Kanal</option>
                        <option value="sq_ft" {{ old('size_unit') == 'sq_ft' ? 'selected' : '' }}>Square Feet</option>
                        <option value="sq_m" {{ old('size_unit') == 'sq_m' ? 'selected' : '' }}>Square Meter</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-images"></i>
                </div>
                <div>
                    <h3 class="section-title">Property Images</h3>
                    <p class="section-description">Upload property photos</p>
                </div>
            </div>

            <div class="form-group">
                <label for="images">Images (You can select multiple)</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple onchange="previewImages(event)">
                <p class="help-text">Maximum 10 images, each up to 5MB</p>
            </div>

            <div id="imagePreview" class="image-preview-grid"></div>
        </div>

        <div class="form-actions">
            <a href="{{ route('properties.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Property
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
    .section-icon { width: 48px; height: 48px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; flex-shrink: 0; }
    .section-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; }
    .section-description { font-size: 0.875rem; color: var(--gray-600); margin: 4px 0 0 0; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-group label { font-weight: 600; color: var(--gray-700); margin-bottom: 8px; font-size: 0.95rem; }
    .form-group input, .form-group select, .form-group textarea { padding: 12px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.95rem; transition: all 0.3s; font-family: inherit; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
    .form-group textarea { resize: vertical; }
    .help-text { font-size: 0.85rem; color: var(--gray-600); margin-top: 6px; }
    .image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
    .preview-item { position: relative; aspect-ratio: 1; border-radius: 10px; overflow: hidden; border: 2px solid #e5e7eb; }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .preview-item .remove-btn { position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; background: rgba(220, 38, 38, 0.9); color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; }
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
let selectedFiles = [];

function previewImages(event) {
    const files = Array.from(event.target.files);
    const previewContainer = document.getElementById('imagePreview');

    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            selectedFiles.push(file);

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn" onclick="removeImage(${selectedFiles.length - 1})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        }
    });
}

function removeImage(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
}

function updatePreview() {
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = '';

    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'preview-item';
            previewItem.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-btn" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            previewContainer.appendChild(previewItem);
        };
        reader.readAsDataURL(file);
    });

    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    document.getElementById('images').files = dataTransfer.files;
}

// Dependent selects: blocks and streets
const blocksUrl = "{{ route('blocks.by-society') }}";
const streetsUrl = "{{ route('streets.by-block') }}";

async function loadBlocks(societyId, selectedBlock = null) {
    const blockSelect = document.getElementById('block_id');
    blockSelect.innerHTML = '<option value="">Select Block</option>';
    if(!societyId) return;
    const res = await fetch(blocksUrl + '?society_id=' + societyId);
    if(!res.ok) return;
    const data = await res.json();
    data.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.text = b.name || b.title || ('Block ' + b.id);
        if(selectedBlock && selectedBlock == b.id) opt.selected = true;
        blockSelect.appendChild(opt);
    });
}

async function loadStreets(blockId, selectedStreet = null) {
    const streetSelect = document.getElementById('street_id');
    streetSelect.innerHTML = '<option value="">Select Street</option>';
    if(!blockId) return;
    const res = await fetch(streetsUrl + '?block_id=' + blockId);
    if(!res.ok) return;
    const data = await res.json();
    data.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.text = s.name || s.title || ('Street ' + s.id);
        if(selectedStreet && selectedStreet == s.id) opt.selected = true;
        streetSelect.appendChild(opt);
    });
}

document.getElementById('society_id')?.addEventListener('change', function() {
    loadBlocks(this.value);
});

document.getElementById('block_id')?.addEventListener('change', function() {
    loadStreets(this.value);
});

// On page load, if a society is selected, load its blocks
document.addEventListener('DOMContentLoaded', function() {
    const s = document.getElementById('society_id');
    const b = document.getElementById('block_id');
    const st = document.getElementById('street_id');
    if(s && s.value) {
        loadBlocks(s.value, '{{ old('block_id') }}').then(() => {
            if(b && b.value) loadStreets(b.value, '{{ old('street_id') }}');
        });
    }
});
</script>
@endpush
@endsection
