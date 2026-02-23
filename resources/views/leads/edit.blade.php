@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('leads.index') }}">Leads</a>
        <span class="separator">/</span>
        <span class="current">Edit</span>
    </div>
    <h1 class="page-title">Edit Lead</h1>
    <p class="page-subtitle">Update lead information</p>
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

<form method="POST" action="{{ route('leads.update', $lead) }}" id="leadForm">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3 class="section-title">Personal Information</h3>
                    <p class="section-description">Update the lead's contact details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $lead->name) }}"
                           placeholder="Enter full name"
                           required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone', $lead->phone) }}"
                           placeholder="e.g., 0300-1234567"
                           required>
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $lead->email) }}"
                           placeholder="Enter email address">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_secondary">Alternate Phone</label>
                    <input type="text" id="phone_secondary" name="phone_secondary"
                           value="{{ old('phone_secondary', $lead->phone_secondary) }}"
                           placeholder="Enter alternate number">
                    @error('phone_secondary')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="preferred_location">Preferred Location</label>
                    <input type="text" id="preferred_location" name="preferred_location"
                           value="{{ old('preferred_location', $lead->preferred_location) }}"
                           placeholder="Enter preferred location">
                    @error('preferred_location')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 class="section-title">Lead Information</h3>
                    <p class="section-description">Update lead classification</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="source">Lead Source *</label>
                    @php $S = '\\App\\Models\\Lead'; @endphp
                    <select id="source" name="source" required>
                        <option value="">Select Source</option>
                        <option value="{{ $S::SOURCE_WEBSITE }}" {{ old('source', $lead->source) == $S::SOURCE_WEBSITE ? 'selected' : '' }}>Website</option>
                        <option value="{{ $S::SOURCE_FACEBOOK }}" {{ old('source', $lead->source) == $S::SOURCE_FACEBOOK ? 'selected' : '' }}>Facebook</option>
                        <option value="{{ $S::SOURCE_REFERRAL }}" {{ old('source', $lead->source) == $S::SOURCE_REFERRAL ? 'selected' : '' }}>Referral</option>
                        <option value="{{ $S::SOURCE_WALKIN }}" {{ old('source', $lead->source) == $S::SOURCE_WALKIN ? 'selected' : '' }}>Walk-in</option>
                        <option value="{{ $S::SOURCE_CALL }}" {{ old('source', $lead->source) == $S::SOURCE_CALL ? 'selected' : '' }}>Call</option>
                        <option value="{{ $S::SOURCE_WHATSAPP }}" {{ old('source', $lead->source) == $S::SOURCE_WHATSAPP ? 'selected' : '' }}>WhatsApp</option>
                        <option value="{{ $S::SOURCE_EMAIL }}" {{ old('source', $lead->source) == $S::SOURCE_EMAIL ? 'selected' : '' }}>Email</option>
                        <option value="{{ $S::SOURCE_OTHER }}" {{ old('source', $lead->source) == $S::SOURCE_OTHER ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('source')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="interest_type">Interest Type *</label>
                    <select id="interest_type" name="interest_type" required>
                        <option value="">Select Interest Type</option>
                        <option value="{{ $S::INTEREST_PLOT }}" {{ old('interest_type', $lead->interest_type) == $S::INTEREST_PLOT ? 'selected' : '' }}>Plot</option>
                        <option value="{{ $S::INTEREST_HOUSE }}" {{ old('interest_type', $lead->interest_type) == $S::INTEREST_HOUSE ? 'selected' : '' }}>House</option>
                        <option value="{{ $S::INTEREST_APARTMENT }}" {{ old('interest_type', $lead->interest_type) == $S::INTEREST_APARTMENT ? 'selected' : '' }}>Apartment</option>
                        <option value="{{ $S::INTEREST_COMMERCIAL }}" {{ old('interest_type', $lead->interest_type) == $S::INTEREST_COMMERCIAL ? 'selected' : '' }}>Commercial</option>
                    </select>
                    @error('interest_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="new" {{ old('status', $lead->status) == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ old('status', $lead->status) == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ old('status', $lead->status) == 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="negotiation" {{ old('status', $lead->status) == 'negotiation' ? 'selected' : '' }}>Negotiation</option>
                        <option value="converted" {{ old('status', $lead->status) == 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ old('status', $lead->status) == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                    @error('status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="priority">Priority *</label>
                    <select id="priority" name="priority" required>
                        <option value="">Select Priority</option>
                        <option value="low" {{ old('priority', $lead->priority) == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $lead->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority', $lead->priority) == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority', $lead->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select id="assigned_to" name="assigned_to">
                        <option value="">Select User</option>
                        @if(isset($users))
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $lead->assigned_to) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('assigned_to')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <h3 class="section-title">Budget & Requirements</h3>
                    <p class="section-description">Update budget range and notes</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="budget_range">Budget Range (Rs.)</label>
                    <input type="text" id="budget_range" name="budget_range"
                           value="{{ old('budget_range', $lead->budget_range) }}"
                           placeholder="e.g., 10 Lac - 50 Lac">
                    @error('budget_range')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="remarks">Notes / Requirements</label>
                    <textarea id="remarks" name="remarks" rows="4"
                              placeholder="Enter any additional notes or requirements">{{ old('remarks', $lead->remarks) }}</textarea>
                    @error('remarks')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('leads.index') }}" class="btn btn-light">
                <i class="fas fa-times"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Update Lead
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

    .btn-light:hover {
        background: var(--gray-50);
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .section-header {
            flex-direction: column;
            text-align: center;
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
document.getElementById('leadForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const source = document.getElementById('source').value;

    if (!name || !phone || !source) {
        e.preventDefault();
        alert('Please fill in all required fields marked with *');
        return false;
    }
});
</script>
@endpush
@endsection
