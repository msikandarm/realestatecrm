@extends('layouts.app')

@section('title', 'Edit Block')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('blocks.index') }}">Blocks</a>
        <span class="separator">/</span>
        <span class="current">Edit</span>
    </div>
    <h1 class="page-title">Edit Block</h1>
    <p class="page-subtitle">Update block information</p>
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

<form method="POST" action="{{ route('blocks.update', $block) }}" id="blockForm">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h3 class="section-title">Block Information</h3>
                    <p class="section-description">Update the block details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="society_id">Society *</label>
                    <select id="society_id" name="society_id" required>
                        <option value="">Select Society</option>
                        @foreach($societies as $society)
                            <option value="{{ $society->id }}" {{ old('society_id', $block->society_id) == $society->id ? 'selected' : '' }}>
                                {{ $society->name }} ({{ $society->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('society_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Block Name *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $block->name) }}"
                           placeholder="e.g., Block A, Phase 1"
                           required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code">Block Code *</label>
                    <input type="text" id="code" name="code"
                           value="{{ old('code', $block->code) }}"
                           placeholder="e.g., BLK-A"
                           required>
                    <small class="form-hint">Unique identifier for the block</small>
                    @error('code')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="active" {{ old('status', $block->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $block->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Enter block description">{{ old('description', $block->description) }}</textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('blocks.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Block
            </button>
        </div>
    </div>
</form>

@push('styles')
<style>
    /* Societies-like button and form styles */
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap; }
    .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
    .btn-light { background: white; color: var(--gray-700); border: 1px solid var(--gray-300); }
    .btn-secondary { background: var(--gray-100); color: var(--gray-700); }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .form-section {
        padding: 30px;
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

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 25px 30px;
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
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
// Form validation
document.getElementById('blockForm').addEventListener('submit', function(e) {
    const society = document.getElementById('society_id').value;
    const name = document.getElementById('name').value.trim();
    const code = document.getElementById('code').value.trim();
    const status = document.getElementById('status').value;

    if (!society || !name || !code || !status) {
        e.preventDefault();
        alert('Please fill in all required fields marked with *');
        return false;
    }
});
</script>
@endpush
@endsection
