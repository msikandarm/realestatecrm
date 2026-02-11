{{-- Stats Card Component --}}
@php
    $bgColor = $bgColor ?? 'primary';
    $size = $size ?? 'normal';
    $colors = [
        'primary' => ['bg' => 'rgba(102, 126, 234, 0.1)', 'text' => '#667eea'],
        'success' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'text' => '#10b981'],
        'danger' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'text' => '#ef4444'],
        'warning' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'text' => '#f59e0b'],
        'info' => ['bg' => 'rgba(59, 130, 246, 0.1)', 'text' => '#3b82f6'],
    ];
    $color = $colors[$bgColor] ?? $colors['primary'];
    $isSmall = $size === 'small';
@endphp

<div class="stat-card {{ $isSmall ? 'stat-card-sm' : '' }}" style="background: {{ $color['bg'] }}; border-left: 6px solid {{ $color['text'] }};">
    <div class="stat-icon" style="background: transparent; border: 2px solid rgba(255,255,255,0.03);">
        <div class="stat-icon-inner" style="background: {{ $color['text'] }};">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
    <div class="stat-content">
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
    </div>
</div>

<style>
    .stat-card {
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.18s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 28px rgba(0,0,0,0.06);
    }

    .stat-card .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card .stat-icon-inner {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.15rem;
    }

    .stat-content {
        flex: 1;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 3px;
    }

    .stat-label {
        color: var(--gray-600);
        font-size: 0.85rem;
    }

    /* Small variant */
    .stat-card-sm {
        padding: 10px 12px;
        gap: 10px;
    }
    .stat-card-sm .stat-icon {
        width: 48px;
        height: 48px;
    }
    .stat-card-sm .stat-icon-inner {
        width: 34px;
        height: 34px;
        font-size: 1rem;
    }
    .stat-card-sm .stat-value {
        font-size: 1.15rem;
    }
    .stat-card-sm .stat-label {
        font-size: 0.8rem;
    }
</style>
