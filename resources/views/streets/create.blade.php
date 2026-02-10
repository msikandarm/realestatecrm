@extends('layouts.app')

@section('title', 'Add New Street')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('streets.index') }}">Streets</a>
        <span class="separator">/</span>
        <span class="current">Add New</span>
    </div>
    <h1 class="page-title">Add New Street</h1>
    <p class="page-subtitle">Create a new street in a block</p>
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

<form method="POST" action="{{ route('streets.store') }}" id="streetForm">
    @csrf

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-road"></i>
                </div>
                <div>
                    <h3 class="section-title">Street Information</h3>
                    <p class="section-description">Enter the street details</p>
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
                    @error('society_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="block_id">Block *</label>
                    <select id="block_id" name="block_id" required disabled>
                        <option value="">Select Block</option>
                    </select>
                    <small class="form-hint">Select society first</small>
                    @error('block_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name">Street Name *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name') }}"
                           placeholder="e.g., Main Boulevard, Street 1"
                           required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code">Street Code *</label>
                    <input type="text" id="code" name="code"
                           value="{{ old('code') }}"
                           placeholder="e.g., ST-001"
                           required>
                    <small class="form-hint">Unique identifier for the street</small>
                    @error('code')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type">Street Type</label>
                    <select id="type" name="type">
                        <option value="">Select Type</option>
                        <option value="main" {{ old('type') == 'main' ? 'selected' : '' }}>Main</option>
                        <option value="commercial" {{ old('type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="residential" {{ old('type') == 'residential' ? 'selected' : '' }}>Residential</option>
                        <option value="side" {{ old('type') == 'side' ? 'selected' : '' }}>Side Street</option>
                    </select>
                    @error('type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="width">Width (in feet)</label>
                    <input type="number" id="width" name="width"
                           value="{{ old('width') }}"
                           placeholder="e.g., 30"
                           min="0"
                           step="0.01">
                    @error('width')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Enter street description">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('streets.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Street
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
        background: linear-gradient(135deg, var(--info), #2563eb);
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
        border-color: var(--info);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
// Store all blocks data
const allBlocks = @json($blocks);
const selectedBlockId = "{{ old('block_id', request('block_id')) }}";

// Cascading dropdown: Load blocks based on selected society
document.getElementById('society_id').addEventListener('change', function() {
    const societyId = this.value;
    const blockSelect = document.getElementById('block_id');

    // Clear existing options
    blockSelect.innerHTML = '<option value="">Select Block</option>';

    if (societyId) {
        // Filter and add blocks for selected society
        const societyBlocks = allBlocks.filter(block => block.society_id == societyId);

        societyBlocks.forEach(block => {
            const option = document.createElement('option');
            option.value = block.id;
            option.textContent = block.name + ' (' + block.code + ')';
            if (selectedBlockId == block.id) {
                option.selected = true;
            }
            blockSelect.appendChild(option);
        });

        blockSelect.disabled = false;
    } else {
        blockSelect.disabled = true;
    }
});

// Trigger on page load if society is already selected
if (document.getElementById('society_id').value) {
    document.getElementById('society_id').dispatchEvent(new Event('change'));
}

// Auto-generate code from name
document.getElementById('name').addEventListener('blur', function() {
    const codeInput = document.getElementById('code');
    if (!codeInput.value) {
        const name = this.value.trim();
        if (name) {
            const prefix = name.replace(/[^a-zA-Z0-9]/g, '').substring(0, 3).toUpperCase();
            const suffix = Math.floor(100 + Math.random() * 900);
            codeInput.value = 'ST-' + prefix + suffix;
        }
    }
});

// Form validation
document.getElementById('streetForm').addEventListener('submit', function(e) {
    const society = document.getElementById('society_id').value;
    const block = document.getElementById('block_id').value;
    const name = document.getElementById('name').value.trim();
    const code = document.getElementById('code').value.trim();
    const status = document.getElementById('status').value;

    if (!society || !block || !name || !code || !status) {
        e.preventDefault();
        alert('Please fill in all required fields marked with *');
        return false;
    }
});
</script>
@endpush
@endsection
