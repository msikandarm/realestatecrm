@extends('layouts.app')

@section('title', 'Lead Details')

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('leads.index') }}">Leads</a>
        <span class="separator">/</span>
        <span class="current">{{ $lead->name }}</span>
    </div>
    <div class="header-content">
        <div class="header-left">
            <h1 class="page-title">{{ $lead->name }}</h1>
            <p class="page-subtitle">
                <i class="fas fa-phone"></i>
                {{ $lead->phone }} | Source: <strong>{{ ucfirst($lead->source ?? 'Direct') }}</strong>
            </p>
        </div>
        <div class="header-actions">
            @can('leads.edit')
                <a href="{{ route('leads.edit', $lead) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Lead
                </a>
            @endcan
            @if($lead->status !== 'converted')
                @can('leads.convert')
                <button type="button" class="btn btn-success" onclick="openConvertModal()">
                    <i class="fas fa-user-check"></i> Convert to Client
                </button>
                @endcan
            @endif
            @can('leads.delete')
                <button onclick="deleteLead({{ $lead->id }})" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            @endcan
        </div>
    </div>
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

<div class="stats-grid" style="margin-bottom: 30px;">
    @include('components.stat-card', [
        'icon' => 'fas fa-user',
        'value' => ucfirst($lead->status),
        'label' => 'Lead Status',
        'bgColor' => $lead->status === 'converted' ? 'success' : ($lead->status === 'lost' ? 'danger' : 'primary')
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-flag',
        'value' => ucfirst($lead->priority),
        'label' => 'Priority Level',
        'bgColor' => $lead->priority === 'urgent' ? 'danger' : ($lead->priority === 'high' ? 'warning' : 'info')
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-clock',
        'value' => $lead->followUps()->count(),
        'label' => 'Follow-ups',
        'bgColor' => 'info'
    ])

    @include('components.stat-card', [
        'icon' => 'fas fa-calendar-alt',
        'value' => $lead->getNextFollowUpDate() ?? '—',
        'label' => 'Next Follow-up',
        'bgColor' => 'success'
    ])
</div>

<div class="details-layout">
    <!-- Main Column -->
    <div class="main-column">
        <!-- Basic Information Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                <span class="badge badge-{{ $lead->status === 'converted' ? 'success' : ($lead->status === 'lost' ? 'danger' : ($lead->status === 'new' ? 'primary' : 'warning')) }}">
                    {{ ucfirst($lead->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Full Name</label>
                        <value>{{ $lead->name }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Phone Number</label>
                        <value><i class="fas fa-phone text-primary"></i> {{ $lead->phone }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Email Address</label>
                        <value>
                            @if($lead->email)
                                <i class="fas fa-envelope text-info"></i> {{ $lead->email }}
                            @else
                                Not provided
                            @endif
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Alternate Phone</label>
                        <value>{{ $lead->phone_secondary ?? 'Not provided' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Lead Source</label>
                        <value><span class="code-badge">{{ ucfirst($lead->source ?? 'Direct') }}</span></value>
                    </div>
                    <div class="detail-item">
                        <label>Interest Type</label>
                        <value><span class="code-badge">{{ ucfirst($lead->interest_type ?? 'Not specified') }}</span></value>
                    </div>
                    <div class="detail-item">
                        <label>Priority</label>
                        <value>
                            <span class="badge badge-{{ $lead->priority === 'urgent' ? 'danger' : ($lead->priority === 'high' ? 'warning' : 'info') }}">
                                {{ ucfirst($lead->priority) }}
                            </span>
                        </value>
                    </div>
                    <div class="detail-item">
                        <label>Assigned To</label>
                        <value>{{ $lead->assignedTo->name ?? 'Unassigned' }}</value>
                    </div>
                    <div class="detail-item">
                        <label>Created Date</label>
                        <value>
                            <i class="fas fa-calendar-alt text-success"></i>
                            {{ $lead->created_at->format('M d, Y') }}
                        </value>
                    </div>
                </div>

                @if($lead->preferred_location)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-map-marked-alt"></i> Preferred Location</label>
                        <value>{{ $lead->preferred_location }}</value>
                    </div>
                @endif

                @if($lead->remarks)
                    <div class="detail-item full-width" style="margin-top: 20px;">
                        <label><i class="fas fa-align-left"></i> Notes / Requirements</label>
                        <value>{{ $lead->remarks }}</value>
                    </div>
                @endif
            </div>
        </div>

        <!-- Budget Information Card -->
        @if($lead->budget_range)
            <div class="details-card">
                <div class="card-header">
                    <h3><i class="fas fa-money-bill-wave"></i> Budget Information</h3>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item full-width">
                            <label>Budget Range</label>
                            <value>
                                <span class="budget-value">{{ $lead->budget_range }}</span>
                            </value>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Follow-ups Card -->
        <div class="details-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Follow-up History</h3>
                <span class="badge badge-info">{{ $lead->followUps->count() }} Records</span>
            </div>
            <div class="card-body">
                @if($lead->followUps->isEmpty())
                    <div class="empty-state-small">
                        <i class="fas fa-clock"></i>
                        <p>No follow-ups recorded yet</p>
                    </div>
                @else
                    <div class="followup-timeline">
                        @foreach($lead->followUps as $followUp)
                            <div class="followup-item">
                                <div class="followup-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="followup-content">
                                    <div class="followup-header">
                                        <strong>{{ $followUp->user->name ?? 'System' }}</strong>
                                        <span class="followup-date">{{ $followUp->scheduled_at?->format('M d, Y') }}</span>
                                    </div>
                                    <p class="followup-notes">{{ $followUp->notes }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Column -->
    <div class="sidebar-column">
        <!-- Status Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Lead Status</h4>
            </div>
            <div class="sidebar-body">
                <div class="status-item {{ $lead->status }}">
                    <i class="fas fa-circle"></i>
                    <span>{{ ucfirst($lead->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Quick Info</h4>
            </div>
            <div class="sidebar-body">
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-calendar-day text-primary"></i>
                        <span>Created</span>
                    </div>
                    <div class="stat-value">{{ $lead->created_at->format('M d, Y') }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-user text-info"></i>
                        <span>Created By</span>
                    </div>
                    <div class="stat-value">{{ $lead->creator->name ?? 'N/A' }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-clock text-success"></i>
                        <span>Last Updated</span>
                    </div>
                    <div class="stat-value">{{ $lead->updated_at->diffForHumans() }}</div>
                </div>
                <div class="sidebar-stat">
                    <div class="stat-label">
                        <i class="fas fa-phone text-warning"></i>
                        <span>Follow-ups</span>
                    </div>
                    <div class="stat-value">{{ $lead->followUps->count() }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Card -->
        <div class="sidebar-card">
            <div class="sidebar-header">
                <h4>Recent Activity</h4>
            </div>
            <div class="sidebar-body">
                <div class="activity-item">
                    <i class="fas fa-plus-circle text-success"></i>
                    <div class="activity-content">
                        <p>Lead created</p>
                        <span class="activity-time">{{ $lead->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @if($lead->updated_at != $lead->created_at)
                    <div class="activity-item">
                        <i class="fas fa-edit text-info"></i>
                        <div class="activity-content">
                            <p>Last updated</p>
                            <span class="activity-time">{{ $lead->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
    }

    .header-left {
        flex: 1;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .page-subtitle {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
        color: var(--gray-600);
        margin-top: 8px;
    }

    .details-layout {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 30px;
    }

    .main-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .details-card,
    .sidebar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .card-header h3 {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header i {
        color: var(--primary);
    }

    .card-body {
        padding: 25px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
    }

    .detail-item label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .detail-item value {
        font-size: 0.95rem;
        color: var(--gray-900);
    }

    .code-badge {
        display: inline-block;
        padding: 4px 10px;
        background: var(--gray-100);
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--gray-700);
    }

    .budget-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--success);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success { background: var(--success); color: white; }
    .badge-warning { background: var(--warning); color: white; }
    .badge-danger { background: var(--danger); color: white; }
    .badge-info { background: var(--info); color: white; }
    .badge-primary { background: var(--primary); color: white; }

    .sidebar-header {
        padding: 15px 20px;
        border-bottom: 1px solid var(--gray-200);
        background: var(--gray-50);
    }

    .sidebar-header h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .sidebar-body {
        padding: 20px;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 8px;
        font-weight: 600;
    }

    .status-item.new { background: rgba(102, 126, 234, 0.1); color: var(--primary); }
    .status-item.contacted { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
    .status-item.qualified { background: rgba(59, 130, 246, 0.1); color: var(--info); }
    .status-item.negotiation { background: rgba(168, 85, 247, 0.1); color: #a855f7; }
    .status-item.converted { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .status-item.lost { background: rgba(239, 68, 68, 0.1); color: var(--danger); }

    .status-item i {
        font-size: 0.5rem;
    }

    .sidebar-stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .sidebar-stat:last-child {
        border-bottom: none;
    }

    .stat-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .stat-value {
        font-weight: 600;
        color: var(--gray-900);
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--gray-100);
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item i {
        font-size: 1rem;
        margin-top: 2px;
    }

    .activity-content p {
        margin: 0;
        font-size: 0.875rem;
        color: var(--gray-700);
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .empty-state-small {
        text-align: center;
        padding: 40px 20px;
        color: var(--gray-500);
    }

    .empty-state-small i {
        font-size: 2.5rem;
        margin-bottom: 10px;
        opacity: 0.3;
    }

    .empty-state-small p {
        margin: 0;
    }

    .followup-timeline {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .followup-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: var(--gray-50);
        border-radius: 10px;
    }

    .followup-icon {
        width: 40px;
        height: 40px;
        background: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .followup-content {
        flex: 1;
    }

    .followup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .followup-header strong {
        color: var(--gray-900);
    }

    .followup-date {
        font-size: 0.8rem;
        color: var(--gray-500);
    }

    .followup-notes {
        margin: 0;
        color: var(--gray-700);
        font-size: 0.9rem;
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

    .btn-success {
        background: var(--success);
        color: white;
    }

    .btn-danger {
        background: transparent;
        color: var(--danger);
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    .btn-danger:hover {
        background: var(--danger);
        color: white;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    @media (max-width: 1024px) {
        .details-layout {
            grid-template-columns: 1fr;
        }

        .sidebar-column {
            order: -1;
        }
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
        }

        .header-actions {
            flex-wrap: wrap;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@can('leads.delete')
<script>
    function deleteLead(id) {
        if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
            fetch(`/leads/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("leads.index") }}';
                } else {
                    alert(data.message || 'Failed to delete lead');
                }
            })
            .catch(error => {
                alert('An error occurred while deleting the lead');
                console.error(error);
            });
        }
    }
</script>
@endcan

<!-- Convert to Client Modal -->
@if($lead->status !== 'converted')
@can('leads.convert')
<div class="modal-overlay" id="convertModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-check"></i> Convert Lead to Client</h3>
            <button type="button" class="modal-close" onclick="closeConvertModal()">&times;</button>
        </div>
        <form action="{{ route('leads.convert', $lead) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="client_name">Client Name *</label>
                        <input type="text" id="client_name" name="client_name"
                               value="{{ old('client_name', $lead->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="client_phone">Phone *</label>
                        <input type="text" id="client_phone" name="client_phone"
                               value="{{ old('client_phone', $lead->phone) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="client_email">Email</label>
                        <input type="email" id="client_email" name="client_email"
                               value="{{ old('client_email', $lead->email) }}">
                    </div>
                    <div class="form-group">
                        <label for="client_type">Client Type *</label>
                        <select id="client_type" name="client_type" required>
                            <option value="">Select Type</option>
                            <option value="buyer" selected>Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cnic">CNIC</label>
                        <input type="text" id="cnic" name="cnic"
                               placeholder="e.g., 35201-1234567-1">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city"
                               value="{{ old('city') }}" placeholder="Enter city">
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province"
                               value="{{ old('province') }}" placeholder="Enter province">
                    </div>
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="2"
                                  placeholder="Enter address">{{ old('address', $lead->preferred_location) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" onclick="closeConvertModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Convert to Client
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openConvertModal() {
        document.getElementById('convertModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeConvertModal() {
        document.getElementById('convertModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        // Remove hash from URL
        history.replaceState(null, null, window.location.pathname);
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConvertModal();
        }
    });

    // Close modal on overlay click
    document.getElementById('convertModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeConvertModal();
        }
    });

    // Auto-open modal if URL has #convert
    if (window.location.hash === '#convert') {
        openConvertModal();
    }

    // Auto-open modal if there are validation errors for convert form
    @if($errors->any())
        openConvertModal();
    @endif
</script>

<style>
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        color: var(--gray-800);
    }

    .modal-header h3 i {
        color: var(--success);
        margin-right: 10px;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        color: var(--gray-500);
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover {
        color: var(--gray-700);
    }

    .modal-body {
        padding: 24px;
    }

    .modal-body .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .modal-body .form-group {
        margin-bottom: 0;
    }

    .modal-body .form-group.full-width {
        grid-column: span 2;
    }

    .modal-body .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 6px;
    }

    .modal-body .form-group input,
    .modal-body .form-group select,
    .modal-body .form-group textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .modal-body .form-group input:focus,
    .modal-body .form-group select:focus,
    .modal-body .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 12px 12px;
    }
</style>
@endcan
@endif
@endsection
