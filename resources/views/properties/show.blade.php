@extends('layouts.app')

@section('title', $property->title)

@section('content')
<div class="page-header">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        <span class="separator">/</span>
        <a href="{{ route('properties.index') }}">Properties</a>
        <span class="separator">/</span>
        <span class="current">{{ $property->title }}</span>
    </div>
    <div class="header-actions">
        <h1 class="page-title">{{ $property->title }}</h1>
        <div class="actions-group">
            @can('properties.edit')
            <a href="{{ route('properties.edit', $property) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            @can('properties.delete')
            <form action="{{ route('properties.destroy', $property) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this property?')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
            @endcan
        </div>
    </div>
</div>

@php
    // Normalize images: prefer relation `propertyImages`, fallback to `images` JSON
    $images = [];
    if(isset($property->propertyImages) && $property->propertyImages->count()) {
        foreach($property->propertyImages as $pi) {
            if(!empty($pi->image_path)) $images[] = $pi->image_path;
        }
    } elseif(is_array($property->images) && count($property->images)) {
        foreach($property->images as $img) {
            if(is_array($img) && isset($img['image_path'])) {
                $images[] = $img['image_path'];
            } elseif(is_string($img)) {
                $images[] = $img;
            }
        }
    }
@endphp

@if(count($images) > 0)
<div class="property-gallery">
    <div class="main-image">
        <img src="{{ asset('storage/' . $images[0]) }}" alt="{{ $property->title }}" id="mainImage">
        <span class="status-badge-large status-{{ $property->status }}">
            {{ ucfirst(str_replace('_', ' ', $property->status)) }}
        </span>
    </div>
    @if(count($images) > 1)
    <div class="thumbnail-strip">
        @foreach($images as $index => $image)
        <div class="thumbnail {{ $index === 0 ? 'active' : '' }}" onclick="changeImage('{{ asset('storage/' . $image) }}', this)">
            <img src="{{ asset('storage/' . $image) }}" alt="Thumbnail {{ $index + 1 }}">
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

<div class="details-layout">
    <div class="main-column">
        <div class="stats-grid">
            <x-stat-card
                icon="fas fa-money-bill-wave"
                value="PKR {{ number_format($property->price) }}"
                label="Price"
                bgColor="bg-success"
            />
            <x-stat-card
                icon="fas fa-ruler-combined"
                value="{{ $property->size }} {{ strtoupper(str_replace('_',' ', $property->size_unit)) }}"
                label="Area"
                bgColor="bg-info"
            />
            @if($property->bedrooms)
            <x-stat-card
                icon="fas fa-bed"
                value="{{ $property->bedrooms }}"
                label="Bedrooms"
                bgColor="bg-purple"
            />
            @endif
            @if($property->bathrooms)
            <x-stat-card
                icon="fas fa-bath"
                value="{{ $property->bathrooms }}"
                label="Bathrooms"
                bgColor="bg-warning"
            />
            @endif
        </div>

        <div class="details-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Property Details
                </h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Property Type</span>
                        <span class="info-value">{{ ucfirst($property->type) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-{{ $property->status }}">{{ ucfirst(str_replace('_', ' ', $property->status)) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Location</span>
                        <span class="info-value"><i class="fas fa-map-marker-alt"></i> {{ $property->location }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Price</span>
                        <span class="info-value">PKR {{ number_format($property->price) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Area</span>
                        <span class="info-value">{{ $property->size }} {{ strtoupper(str_replace('_',' ', $property->size_unit)) }}</span>
                    </div>
                    @if($property->bedrooms)
                    <div class="info-item">
                        <span class="info-label">Bedrooms</span>
                        <span class="info-value">{{ $property->bedrooms }}</span>
                    </div>
                    @endif
                    @if($property->bathrooms)
                    <div class="info-item">
                        <span class="info-label">Bathrooms</span>
                        <span class="info-value">{{ $property->bathrooms }}</span>
                    </div>
                    @endif
                    <div class="info-item">
                        <span class="info-label">Listed On</span>
                        <span class="info-value">{{ $property->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                @if($property->description)
                <div class="description-section">
                    <h4 class="section-subtitle">Description</h4>
                    <p class="description-text">{{ $property->description }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($property->deals && $property->deals->count() > 0)
        <div class="related-section">
            <div class="section-header-row">
                <h3 class="section-title">
                    <i class="fas fa-handshake"></i> Related Deals
                </h3>
            </div>
            <div class="deals-grid">
                @foreach($property->deals as $deal)
                <div class="deal-card">
                    <div class="deal-header">
                        <div>
                            <h4 class="deal-title">Deal #{{ $deal->id }}</h4>
                            <p class="deal-date">{{ $deal->created_at->format('M d, Y') }}</p>
                        </div>
                        <span class="status-badge status-{{ $deal->status }}">{{ ucfirst($deal->status) }}</span>
                    </div>
                    <div class="deal-info">
                        <div class="deal-item">
                            <i class="fas fa-user"></i>
                            <span>{{ $deal->client->name ?? 'N/A' }}</span>
                        </div>
                        <div class="deal-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>PKR {{ number_format($deal->amount) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="sidebar-column">
        <div class="sidebar-card">
            <h4 class="sidebar-title">Status</h4>
            <div class="status-display status-{{ $property->status }}">
                <i class="fas fa-circle"></i> {{ ucfirst(str_replace('_', ' ', $property->status)) }}
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Quick Info</h4>
            <div class="quick-stats">
                <div class="stat-row">
                    <span class="stat-label">Type</span>
                    <span class="stat-value">{{ ucfirst($property->type) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Price</span>
                    <span class="stat-value">PKR {{ number_format($property->price) }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Area</span>
                    <span class="stat-value">{{ $property->size }} {{ strtoupper(str_replace('_',' ', $property->size_unit)) }}</span>
                </div>
                @if($property->deals)
                <div class="stat-row">
                    <span class="stat-label">Deals</span>
                    <span class="stat-value">{{ $property->deals->count() }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="sidebar-card">
            <h4 class="sidebar-title">Contact Agent</h4>
            <div class="agent-info">
                <div class="agent-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="agent-name">Real Estate CRM</p>
                    <p class="agent-phone"><i class="fas fa-phone"></i> +92 300 1234567</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .property-gallery { background: white; border-radius: 12px; overflow: hidden; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
    .main-image { position: relative; height: 500px; background: var(--gray-100); }
    .main-image img { width: 100%; height: 100%; object-fit: cover; }
    .status-badge-large { position: absolute; top: 20px; right: 20px; padding: 10px 20px; border-radius: 25px; font-size: 0.95rem; font-weight: 700; background: rgba(255, 255, 255, 0.95); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
    .thumbnail-strip { display: flex; gap: 10px; padding: 15px; overflow-x: auto; }
    .thumbnail { width: 100px; height: 100px; border-radius: 8px; overflow: hidden; cursor: pointer; border: 3px solid transparent; transition: all 0.3s; flex-shrink: 0; }
    .thumbnail:hover, .thumbnail.active { border-color: var(--primary); }
    .thumbnail img { width: 100%; height: 100%; object-fit: cover; }
    .details-layout { display: grid; grid-template-columns: 1fr 320px; gap: 25px; }
    .main-column { display: flex; flex-direction: column; gap: 25px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .details-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
    .card-header { padding: 25px; border-bottom: 1px solid #e5e7eb; }
    .card-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 10px; }
    .card-body { padding: 30px; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; }
    .info-item { display: flex; flex-direction: column; gap: 8px; }
    .info-label { font-size: 0.875rem; font-weight: 600; color: var(--gray-600); text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 1.1rem; font-weight: 600; color: var(--gray-900); }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
    .status-for_sale, .status-badge-large.status-for_sale { background: #d1fae5; color: #065f46; }
    .status-rented, .status-badge-large.status-rented { background: #fef3c7; color: #92400e; }
    .status-sold, .status-badge-large.status-sold { background: #dbeafe; color: #1e40af; }
    .status-pending, .status-badge-large.status-pending { background: #e0e7ff; color: #4338ca; }
    .description-section { margin-top: 30px; padding-top: 30px; border-top: 1px solid #e5e7eb; }
    .section-subtitle { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0 0 12px 0; }
    .description-text { color: var(--gray-700); line-height: 1.7; margin: 0; }
    .related-section { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 25px; }
    .section-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .section-title { font-size: 1.25rem; font-weight: 700; color: var(--gray-900); margin: 0; display: flex; align-items: center; gap: 10px; }
    .deals-grid { display: grid; gap: 15px; }
    .deal-card { background: var(--gray-50); border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; }
    .deal-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; }
    .deal-title { font-size: 1rem; font-weight: 700; color: var(--gray-900); margin: 0; }
    .deal-date { font-size: 0.875rem; color: var(--gray-600); margin: 4px 0 0 0; }
    .deal-info { display: flex; gap: 20px; flex-wrap: wrap; }
    .deal-item { display: flex; align-items: center; gap: 8px; color: var(--gray-700); font-size: 0.95rem; }
    .sidebar-column { display: flex; flex-direction: column; gap: 20px; }
    .sidebar-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 20px; }
    .sidebar-title { font-size: 0.95rem; font-weight: 700; color: var(--gray-900); margin: 0 0 15px 0; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-display { padding: 12px 16px; background: var(--gray-100); border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .quick-stats { display: flex; flex-direction: column; gap: 12px; }
    .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
    .stat-row:last-child { border-bottom: none; }
    .stat-label { font-size: 0.875rem; color: var(--gray-600); font-weight: 500; }
    .stat-value { font-size: 0.95rem; color: var(--gray-900); font-weight: 700; }
    .agent-info { display: flex; gap: 12px; align-items: center; }
    .agent-avatar { width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
    .agent-name { font-weight: 600; color: var(--gray-900); margin: 0 0 4px 0; }
    .agent-phone { font-size: 0.9rem; color: var(--gray-600); margin: 0; display: flex; align-items: center; gap: 6px; }
    @media (max-width: 1024px) {
        .details-layout { grid-template-columns: 1fr; }
        .main-image { height: 350px; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .info-grid { grid-template-columns: 1fr; }
        .main-image { height: 250px; }
    }
</style>
@endpush

@push('scripts')
<script>
function changeImage(src, element) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}
</script>
@endpush
@endsection
