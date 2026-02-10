@extends('layouts.app')

@section('title', 'Edit Dealer')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('dealers.index') }}">Dealers</a>
        <span class="separator">/</span>
        <span class="current">Edit</span>
    </div>
    <h1 class="page-title">Edit Dealer</h1>
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

<form method="POST" action="{{ route('dealers.update', $dealer) }}">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3 class="section-title">Dealer Information</h3>
                    <p class="section-description">Update dealer details</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="user_id">Link to User *</label>
                    <select id="user_id" name="user_id" required>
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $dealer->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone">Phone *</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $dealer->phone) }}" placeholder="e.g., +92 300 1234567" required>
                </div>

                <div class="form-group">
                    <label for="cnic">CNIC *</label>
                    <input type="text" id="cnic" name="cnic" value="{{ old('cnic', $dealer->cnic) }}" placeholder="e.g., 12345-1234567-1" required>
                </div>

                <div class="form-group">
                    <label for="commission_rate">Commission Rate (%) *</label>
                    <input type="number" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $dealer->commission_rate) }}" placeholder="e.g., 2" step="0.01" min="0" max="100" required>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="active" {{ old('status', $dealer->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $dealer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" placeholder="Enter complete address">{{ old('address', $dealer->address) }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h3 class="section-title">Bank Details</h3>
                    <p class="section-description">Update bank account information</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $dealer->bank_name) }}" placeholder="e.g., HBL, UBL">
                </div>

                <div class="form-group">
                    <label for="account_number">Account Number</label>
                    <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $dealer->account_number) }}" placeholder="Enter account number">
                </div>

                <div class="form-group full-width">
                    <label for="account_title">Account Title</label>
                    <input type="text" id="account_title" name="account_title" value="{{ old('account_title', $dealer->account_title) }}" placeholder="Account holder name">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('dealers.show', $dealer) }}" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Dealer
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
@endsection
