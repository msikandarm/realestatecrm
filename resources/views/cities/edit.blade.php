@extends('layouts.app')

@section('title', 'Edit City')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('cities.index') }}">Cities</a>
        <span class="separator">/</span>
        <span class="current">Edit</span>
    </div>
    <h1 class="page-title">Edit City</h1>
    <p class="page-subtitle">Update city details</p>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
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

<form method="POST" action="{{ route('cities.update', $city) }}" id="cityFormEdit" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-map-pin"></i>
                </div>
                <div>
                    <h3 class="section-title">Basic Information</h3>
                    <p class="section-description">Update the city details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">City Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $city->name) }}" placeholder="Enter city name" required autofocus>
                    @error('name') <span class="error-message">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province" value="{{ old('province', $city->province) }}" placeholder="e.g., Sindh">
                    @error('province') <span class="error-message">{{ $message }}</span> @enderror
                </div>

                <div class="form-group full-width">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Optional notes about the city">{{ old('notes', $city->notes ?? '') }}</textarea>
                    @error('notes') <span class="error-message">{{ $message }}</span> @enderror
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
                    <p class="section-description">Upload or replace reference document</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="reference_file">Reference File (PDF/Image)</label>
                    <input type="file" id="reference_file" name="reference_file" accept="image/*,.pdf" onchange="previewFile(this, 'referencePreview')">
                    <small class="form-hint">Supported: JPG, PNG, PDF (Max: 5MB)</small>
                    @error('reference_file') <span class="error-message">{{ $message }}</span> @enderror
                    <div id="referencePreview" class="file-preview">
                            @if(!empty($city->reference_path))
                                @php $ext = strtolower(pathinfo($city->reference_path, PATHINFO_EXTENSION)); @endphp
                                @if($ext === 'pdf')
                                    <div class="file-preview-info"><i class="fas fa-file-pdf" style="color: #ef4444; font-size: 2rem;"></i><div><strong>{{ basename($city->reference_path) }}</strong></div></div>
                                @else
                                    <img src="{{ asset('storage/' . $city->reference_path) }}" alt="Preview" style="max-width:200px; max-height:200px; border-radius:8px;">
                                @endif
                            @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('cities.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update City
            </button>
        </div>
    </div>
</form>

@push('styles')
<style>
    /* Reuse Societies styles for consistent design */
    .btn { display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border:none; border-radius:8px; font-size:0.875rem; font-weight:600; cursor:pointer; transition:all 0.2s; text-decoration:none; white-space:nowrap; }
    .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3); }
    .btn-light { background: white; color: var(--gray-700); border: 1px solid var(--gray-300); }
    .form-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
    .form-section { padding: 30px; border-bottom: 1px solid #e5e7eb; }
    .form-section:last-child { border-bottom: none; }
    .section-header { display:flex; align-items:flex-start; gap:15px; margin-bottom:25px; }
    .section-icon { width:48px; height:48px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius:12px; display:flex; align-items:center; justify-content:center; color:white; font-size:1.25rem; flex-shrink:0; }
    .form-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px,1fr)); gap:20px; }
    .form-group { display:flex; flex-direction:column; }
    .form-group.full-width { grid-column:1 / -1; }
    .form-group label { font-weight:600; color: var(--gray-700); margin-bottom:8px; }
    .form-group input, .form-group textarea { padding:12px 15px; border:1px solid #d1d5db; border-radius:8px; }
    .file-preview { margin-top:15px; padding:15px; background: var(--gray-50); border-radius:8px; }
</style>
@endpush

@push('scripts')
<script>
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    if (!preview) return;
    if (file) {
        preview.classList.add('active');
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">'; }
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            preview.innerHTML = '<div class="file-preview-info"><i class="fas fa-file-pdf" style="color: #ef4444; font-size: 2rem;"></i><div><strong>' + file.name + '</strong><br><small>' + (file.size / 1024 / 1024).toFixed(2) + ' MB</small></div></div>';
        } else {
            preview.innerHTML = '<div class="file-preview-info"><i class="fas fa-file" style="font-size: 2rem;"></i><div><strong>' + file.name + '</strong><br><small>' + (file.size / 1024 / 1024).toFixed(2) + ' MB</small></div></div>';
        }
    } else { preview.classList.remove('active'); preview.innerHTML = ''; }
}

document.getElementById('cityFormEdit')?.addEventListener('submit', function(e){
    const name = document.getElementById('name').value.trim();
    if (!name) { e.preventDefault(); alert('Please enter the city name'); return false; }
    const btn = this.querySelector('button[type=submit]'); if (btn) btn.disabled = true;
});
</script>
@endpush

@endsection
