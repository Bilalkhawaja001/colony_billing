@php
    $rawMonthLabel = trim((string) ($value ?? request('month_cycle', request('month', now()->format('m-Y')))));
    $prettyMonthLabel = $rawMonthLabel;

    if (preg_match('/^(\d{2})-(\d{4})$/', $rawMonthLabel, $m)) {
        $dt = \DateTime::createFromFormat('!m-Y', $m[1].'-'.$m[2]);
        if ($dt) {
            $prettyMonthLabel = $dt->format('M Y');
        }
    } elseif (preg_match('/^(\d{4})-(\d{2})/', $rawMonthLabel, $m)) {
        $dt = \DateTime::createFromFormat('!Y-m', $m[1].'-'.$m[2]);
        if ($dt) {
            $prettyMonthLabel = $dt->format('M Y');
        }
    }
@endphp
{{ $prettyMonthLabel }}
