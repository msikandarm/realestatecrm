@extends('layouts.app')

@section('title', 'Add New Society')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('societies.index') }}">Societies</a>
        <span class="separator">/</span>
        <span class="current">Add New</span>
    </div>
    <h1 class="page-title">Add New Society</h1>
    <p class="page-subtitle">Create a new housing society or scheme</p>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

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

<form method="POST" action="{{ route('societies.store') }}" enctype="multipart/form-data" id="societyForm">
    @csrf

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="section-title">Basic Information</h3>
                    <p class="section-description">Enter the basic details of the society</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Society Name *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           placeholder="Enter society name"
                           required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code">Society Code *</label>
                    <input type="text" id="code" name="code"
                           value="{{ old('code') }}"
                           placeholder="e.g., BT-001"
                           required>
                    <small class="form-hint">Unique identifier for the society</small>
                    @error('code')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="city_id">City *</label>
                    <select id="city_id" name="city_id" required>
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    </select>
                    @error('status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"
                              placeholder="Enter complete address">{{ old('address') }}</textarea>
                    @error('address')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Enter society description">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h3 class="section-title">Society Details</h3>
                    <p class="section-description">Additional information about the society</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="total_area">Total Area (Marla/Kanal)</label>
                    <input type="text" id="total_area" name="total_area"
                           value="{{ old('total_area') }}"
                           placeholder="e.g., 500 Kanal">
                    @error('total_area')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="launch_date">Launch Date</label>
                    <input type="date" id="launch_date" name="launch_date"
                           value="{{ old('launch_date') }}">
                    @error('launch_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="possession_date">Possession Date</label>
                    <input type="date" id="possession_date" name="possession_date"
                           value="{{ old('possession_date') }}">
                    @error('possession_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="developer_name">Developer Name</label>
                    <input type="text" id="developer_name" name="developer_name"
                           value="{{ old('developer_name') }}"
                           placeholder="Enter developer name">
                    @error('developer_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <h3 class="section-title">Amenities & Features</h3>
                    <p class="section-description">Select available amenities</p>
                </div>
            </div>

            <div class="amenities-grid">
                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Electricity"
                           {{ is_array(old('amenities')) && in_array('Electricity', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-bolt"></i>
                        <span>Electricity</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Gas"
                           {{ is_array(old('amenities')) && in_array('Gas', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-fire"></i>
                        <span>Gas</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Water"
                           {{ is_array(old('amenities')) && in_array('Water', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-tint"></i>
                        <span>Water Supply</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Mosque"
                           {{ is_array(old('amenities')) && in_array('Mosque', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-mosque"></i>
                        <span>Mosque</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="School"
                           {{ is_array(old('amenities')) && in_array('School', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-school"></i>
                        <span>School</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Park"
                           {{ is_array(old('amenities')) && in_array('Park', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-tree"></i>
                        <span>Parks</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Hospital"
                           {{ is_array(old('amenities')) && in_array('Hospital', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-hospital"></i>
                        <span>Hospital</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Shopping Mall"
                           {{ is_array(old('amenities')) && in_array('Shopping Mall', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Shopping Mall</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Security"
                           {{ is_array(old('amenities')) && in_array('Security', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-shield-alt"></i>
                        <span>24/7 Security</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Gated Community"
                           {{ is_array(old('amenities')) && in_array('Gated Community', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-lock"></i>
                        <span>Gated Community</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Gym"
                           {{ is_array(old('amenities')) && in_array('Gym', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-dumbbell"></i>
                        <span>Gym</span>
                    </span>
                </label>

                <label class="amenity-checkbox">
                    <input type="checkbox" name="amenities[]" value="Community Center"
                           {{ is_array(old('amenities')) && in_array('Community Center', old('amenities')) ? 'checked' : '' }}>
                    <span class="amenity-label">
                        <i class="fas fa-users"></i>
                        <span>Community Center</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-map"></i>
                </div>
                <div>
                    <h3 class="section-title">Map & Documents</h3>
                    <p class="section-description">Upload society map and related documents</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="map_file">Society Map (Image/PDF)</label>
                    <input type="file" id="map_file" name="map_file"
                           accept="image/*,.pdf"
                           onchange="previewFile(this, 'mapPreview')">
                    <small class="form-hint">Supported formats: JPG, PNG, PDF (Max: 5MB)</small>
                    @error('map_file')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <div id="mapPreview" class="file-preview"></div>
                </div>

                <div class="form-group full-width">
                    <label for="noc_file">NOC Document (Optional)</label>
                    <input type="file" id="noc_file" name="noc_file"
                           accept=".pdf"
                           onchange="previewFile(this, 'nocPreview')">
                    <small class="form-hint">Upload No Objection Certificate (PDF only)</small>
                    @error('noc_file')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <div id="nocPreview" class="file-preview"></div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('societies.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Society
            </button>
        </div>
    </div>
</form>

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .form-section {
        padding: 30px;
        border-bottom: 1px solid #e5e7eb;
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .section-header {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 25px;
    }

    .section-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .section-description {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin: 4px 0 0 0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px 15px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-hint {
        font-size: 0.8rem;
        color: var(--gray-500);
        margin-top: 5px;
    }

    .error-message {
        color: var(--danger);
        font-size: 0.8rem;
        margin-top: 5px;
    }

    .amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 15px;
    }

    .amenity-checkbox {
        cursor: pointer;
    }

    .amenity-checkbox input[type="checkbox"] {
        display: none;
    }

    .amenity-label {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: var(--gray-50);
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        transition: all 0.3s;
    }

    .amenity-label i {
        font-size: 1.25rem;
        color: var(--gray-600);
        width: 24px;
        text-align: center;
    }

    .amenity-label span {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    .amenity-checkbox input[type="checkbox"]:checked + .amenity-label {
        background: rgba(102, 126, 234, 0.1);
        border-color: var(--primary);
    }

    .amenity-checkbox input[type="checkbox"]:checked + .amenity-label i {
        color: var(--primary);
    }

    .amenity-checkbox:hover .amenity-label {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .file-preview {
        margin-top: 15px;
        padding: 15px;
        background: var(--gray-50);
        border-radius: 8px;
        display: none;
    }

    .file-preview.active {
        display: block;
    }

    .file-preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
    }

    .file-preview-info {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--gray-700);
        font-size: 0.9rem;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 25px 30px;
        background: var(--gray-50);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert i {
        font-size: 1.25rem;
        margin-top: 2px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alert ul {
        margin: 8px 0 0 0;
        padding-left: 20px;
    }

    .alert ul li {
        margin: 4px 0;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .section-header {
            flex-direction: column;
            text-align: center;
        }

        .amenities-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];

    if (file) {
        preview.classList.add('active');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            }
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            preview.innerHTML = '<div class="file-preview-info"><i class="fas fa-file-pdf" style="color: #ef4444; font-size: 2rem;"></i><div><strong>' + file.name + '</strong><br><small>' + (file.size / 1024 / 1024).toFixed(2) + ' MB</small></div></div>';
        } else {
            preview.innerHTML = '<div class="file-preview-info"><i class="fas fa-file" style="font-size: 2rem;"></i><div><strong>' + file.name + '</strong><br><small>' + (file.size / 1024 / 1024).toFixed(2) + ' MB</small></div></div>';
        }
    } else {
        preview.classList.remove('active');
        preview.innerHTML = '';
    }
}

// Auto-generate code from name
document.getElementById('name').addEventListener('blur', function() {
    const codeInput = document.getElementById('code');
    if (!codeInput.value) {
        const name = this.value.trim();
        if (name) {
            // Generate code: take first 3 letters + random 3 digits
            const prefix = name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, '');
            const suffix = Math.floor(100 + Math.random() * 900);
            codeInput.value = prefix + '-' + suffix;
        }
    }
});

// Form validation
document.getElementById('societyForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const code = document.getElementById('code').value.trim();
    const city = document.getElementById('city_id').value;
    const status = document.getElementById('status').value;

    if (!name || !code || !city || !status) {
        e.preventDefault();
        alert('Please fill in all required fields marked with *');
        return false;
    }
});
</script>
@endpush
@endsection
