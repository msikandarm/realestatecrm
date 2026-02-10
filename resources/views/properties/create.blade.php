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
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="for_sale" {{ old('status', 'for_sale') == 'for_sale' ? 'selected' : '' }}>For Sale</option>
                        <option value="rented" {{ old('status') == 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="sold" {{ old('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
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
                    <label for="area">Area *</label>
                    <input type="number" id="area" name="area" value="{{ old('area') }}" placeholder="e.g., 5" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="area_unit">Area Unit *</label>
                    <select id="area_unit" name="area_unit" required>
                        <option value="marla" {{ old('area_unit', 'marla') == 'marla' ? 'selected' : '' }}>Marla</option>
                        <option value="kanal" {{ old('area_unit') == 'kanal' ? 'selected' : '' }}>Kanal</option>
                        <option value="sqft" {{ old('area_unit') == 'sqft' ? 'selected' : '' }}>Square Feet</option>
                        <option value="sqyd" {{ old('area_unit') == 'sqyd' ? 'selected' : '' }}>Square Yards</option>
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
</script>
@endpush
@endsection
