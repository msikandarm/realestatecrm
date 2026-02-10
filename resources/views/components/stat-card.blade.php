{{-- Stats Card Component --}}
@php
    $bgColor = $bgColor ?? 'primary';
    $colors = [
        'primary' => ['bg' => 'rgba(102, 126, 234, 0.1)', 'text' => '#667eea'],
        'success' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'text' => '#10b981'],
        'danger' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'text' => '#ef4444'],
        'warning' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'text' => '#f59e0b'],
        'info' => ['bg' => 'rgba(59, 130, 246, 0.1)', 'text' => '#3b82f6'],
    ];
    $color = $colors[$bgColor] ?? $colors['primary'];
@endphp

<div class="stat-card" style="background: {{ $color['bg'] }}; border-left: 4px solid {{ $color['text'] }};">
    <div class="stat-icon" style="background: {{ $color['text'] }};">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="stat-content">
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
        @if(isset($change))
            <div class="stat-change {{ $change >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $change >= 0 ? 'up' : 'down' }}"></i>
                {{ abs($change) }}% from last month
            </div>
        @endif
    </div>
</div>

<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .stat-content {
        flex: 1;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-label {
        color: var(--gray-600);
        font-size: 0.875rem;
        margin-bottom: 8px;
    }

    .stat-change {
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change.positive {
        color: var(--success);
    }

    .stat-change.negative {
        color: var(--danger);
    }
</style>
