@extends('layouts.app')

@section('title', 'Edit Client')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('clients.index') }}">Clients</a>
        <span class="separator">/</span>
        <a href="{{ route('clients.show', $client) }}">{{ $client->name }}</a>
        <span class="separator">/</span>
        <span class="current">Edit</span>
    </div>
    <h1 class="page-title">Edit Client</h1>
    <p class="page-subtitle">Update client information</p>
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

<form method="POST" action="{{ route('clients.update', $client) }}" id="clientForm">
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
                    <p class="section-description">Update the client's basic contact details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $client->name) }}"
                           placeholder="Enter full name"
                           required autofocus>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone"
                           value="{{ old('phone', $client->phone) }}"
                           placeholder="e.g., 0300-1234567"
                           required>
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $client->email) }}"
                           placeholder="Enter email address">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_secondary">Alternate Phone</label>
                    <input type="text" id="phone_secondary" name="phone_secondary"
                           value="{{ old('phone_secondary', $client->phone_secondary) }}"
                           placeholder="Enter alternate number">
                    @error('phone_secondary')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="cnic">CNIC</label>
                    <input type="text" id="cnic" name="cnic"
                           value="{{ old('cnic', $client->cnic) }}"
                           placeholder="e.g., 35201-1234567-1">
                    @error('cnic')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city"
                           value="{{ old('city', $client->city) }}"
                           placeholder="Enter city">
                    @error('city')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="province">Province</label>
                    <input type="text" id="province" name="province"
                           value="{{ old('province', $client->province) }}"
                           placeholder="Enter province">
                    @error('province')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"
                              placeholder="Enter complete address">{{ old('address', $client->address) }}</textarea>
                    @error('address')
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
                    <h3 class="section-title">Client Information</h3>
                    <p class="section-description">Update client type and status</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="client_type">Client Type *</label>
                    <select id="client_type" name="client_type" required>
                        <option value="">Select Type</option>
                        <option value="buyer" {{ old('client_type', $client->client_type) == 'buyer' ? 'selected' : '' }}>Buyer</option>
                        <option value="seller" {{ old('client_type', $client->client_type) == 'seller' ? 'selected' : '' }}>Seller</option>
                        <option value="both" {{ old('client_type', $client->client_type) == 'both' ? 'selected' : '' }}>Both (Buyer & Seller)</option>
                    </select>
                    @error('client_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="client_status">Status *</label>
                    <select id="client_status" name="client_status" required>
                        <option value="">Select Status</option>
                        <option value="active" {{ old('client_status', $client->client_status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('client_status', $client->client_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blacklisted" {{ old('client_status', $client->client_status) == 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    </select>
                    @error('client_status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation"
                           value="{{ old('occupation', $client->occupation) }}"
                           placeholder="Enter occupation">
                    @error('occupation')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="company">Company</label>
                    <input type="text" id="company" name="company"
                           value="{{ old('company', $client->company) }}"
                           placeholder="Enter company name">
                    @error('company')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select id="assigned_to" name="assigned_to">
                        <option value="">Select Dealer/Agent</option>
                        @if(isset($dealers))
                            @foreach($dealers as $dealer)
                                <option value="{{ $dealer->id }}" {{ old('assigned_to', $client->assigned_to) == $dealer->id ? 'selected' : '' }}>
                                    {{ $dealer->name }}
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
                    <i class="fas fa-sticky-note"></i>
                </div>
                <div>
                    <h3 class="section-title">Additional Information</h3>
                    <p class="section-description">Any additional notes or remarks</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="remarks">Remarks / Notes</label>
                    <textarea id="remarks" name="remarks" rows="4"
                              placeholder="Enter any additional notes or remarks">{{ old('remarks', $client->remarks) }}</textarea>
                    @error('remarks')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('clients.show', $client) }}" class="btn btn-light">
                <i class="fas fa-times"></i>
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Update Client
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
        gap: 16px;
        margin-bottom: 24px;
    }

    .section-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary), #818cf8);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        flex-shrink: 0;
    }

    .section-title {
        margin: 0 0 4px 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--gray-800);
    }

    .section-description {
        margin: 0;
        font-size: 14px;
        color: var(--gray-500);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-700);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .error-message {
        color: var(--danger);
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 30px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: span 1;
        }

        .form-section {
            padding: 20px;
        }

        .form-actions {
            padding: 15px 20px;
        }
    }
</style>
@endpush
@endsection
