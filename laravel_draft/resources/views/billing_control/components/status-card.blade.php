<div class="stat">
    <div class="stat-num {{ !empty($warn) ? 'is-warn' : (!empty($ok) ? 'is-ok' : '') }}">{{ $value ?? '-' }}</div>
    <div class="stat-label">{{ $title ?? 'Metric' }}</div>
</div>
