@extends('layouts.app')

@section('title', 'Add City')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('cities.index') }}">Cities</a>
        <span class="separator">/</span>
        <span class="current">Add</span>
    </div>
    <h1 class="page-title">Add City</h1>
    <p class="page-subtitle">Add a city that can be selected in forms</p>
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

<form method="POST" action="{{ route('cities.store') }}" id="cityForm" enctype="multipart/form-data">
    @csrf

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-map-pin"></i>
                </div>
                <div>
                    <h3 class="section-title">Basic Information</h3>
                    <p class="section-description">Enter the city details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">City Name *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           placeholder="Enter city name"
                           required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province"
                           value="{{ old('province') }}"
                           placeholder="e.g., Sindh">
                    @error('province')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Optional notes about the city">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>
                    <h3 class="section-title">Documents (Optional)</h3>
                    <p class="section-description">Upload any reference document for the city</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="reference_file">Reference File (PDF/Image)</label>
                    <input type="file" id="reference_file" name="reference_file" accept="image/*,.pdf" onchange="previewFile(this, 'referencePreview')">
                    <small class="form-hint">Supported: JPG, PNG, PDF (Max: 5MB)</small>
                    @error('reference_file')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <div id="referencePreview" class="file-preview"></div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('cities.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save City
            </button>
        </div>
    </div>
</form>

@push('styles')
<style>
    /* Copied Societies form styles for matching design */

    /* Button styles to match Societies */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-light {
        background: white;
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }

    .btn-secondary {
        background: var(--gray-100);
        color: var(--gray-700);
    }

    .btn-sm { padding: 6px 14px; font-size: 0.8125rem; }

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
    .form-hint { font-size: 0.8rem; color: var(--gray-500); margin-top: 5px; }
    .error-message { color: var(--danger); font-size: 0.8rem; margin-top: 5px; }
    .file-preview { margin-top: 15px; padding: 15px; background: var(--gray-50); border-radius: 8px; display: none; }
    .file-preview.active { display: block; }
    .file-preview img { max-width: 200px; max-height: 200px; border-radius: 8px; }
    .file-preview-info { display: flex; align-items: center; gap: 10px; color: var(--gray-700); font-size: 0.9rem; }
    .form-actions { display: flex; justify-content: flex-end; gap: 15px; padding: 25px 30px; background: var(--gray-50); }
    .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px; }
    .alert i { font-size: 1.25rem; margin-top: 2px; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .section-header { flex-direction: column; text-align: center; } .form-actions { flex-direction: column; } .form-actions .btn { width: 100%; justify-content: center; } }
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

// Disable submit after click to avoid double posts
document.getElementById('cityForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type=submit]');
    if (btn) btn.disabled = true;
});

// Basic validation
document.getElementById('cityForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    if (!name) {
        e.preventDefault();
        alert('Please enter the city name');
        return false;
    }
});
</script>
@endpush

@endsection
