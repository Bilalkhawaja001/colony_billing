@php
    $issueTitle = is_array($issue ?? null) ? ($issue['title'] ?? $issue['message'] ?? $issue['code'] ?? 'Needs attention') : (string)($issue ?? 'Needs attention');
    $issueHelp = is_array($issue ?? null) ? ($issue['help'] ?? $issue['description'] ?? $issue['detail'] ?? '') : '';
    $issueCode = strtoupper((string)(is_array($issue ?? null) ? ($issue['code'] ?? '') : ''));
    $fixUrl = is_array($issue ?? null) ? ($issue['fixUrl'] ?? $issue['url'] ?? null) : null;

    if (!$fixUrl) {
        if (strpos($issueCode, 'READING') !== false) {
            $fixUrl = route('billing.control.readings', request()->query());
        } elseif (strpos($issueCode, 'ROOM') !== false || strpos($issueCode, 'RESIDENCE') !== false || strpos($issueCode, 'ALLOWANCE') !== false) {
            $fixUrl = route('billing.control.rooms', request()->query());
        } elseif (strpos($issueCode, 'ACTIVE_DAYS') !== false) {
            $fixUrl = url('/active-days-monthly');
        }
    }
@endphp

<div class="issue is-blocker">
    <span class="issue-badge">✕</span>
    <div class="issue-body">
        <div class="issue-title">{{ $issueTitle }}</div>
        @if($issueHelp)
            <div class="issue-desc">{{ $issueHelp }}</div>
        @endif
    </div>
    @if($fixUrl)
        <a class="btn btn-outline" href="{{ $fixUrl }}">Fix this →</a>
    @endif
</div>
