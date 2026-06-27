@php
    $fieldName = $name ?? 'month_cycle';
    $fieldId = $id ?? 'billing-month-picker';
    $fieldClass = $class ?? 'form-select';
    $raw = trim((string) ($value ?? request('month_cycle', request('month', now()->format('m-Y')))));

    $monthPickerValue = date('Y-m');
    $billingMonthValue = now()->format('m-Y');

    if (preg_match('/^(\d{2})-(\d{4})$/', $raw, $m)) {
        $billingMonthValue = sprintf('%02d-%04d', (int) $m[1], (int) $m[2]);
        $monthPickerValue = sprintf('%04d-%02d', (int) $m[2], (int) $m[1]);
    } elseif (preg_match('/^(\d{4})-(\d{2})/', $raw, $m)) {
        $billingMonthValue = sprintf('%02d-%04d', (int) $m[2], (int) $m[1]);
        $monthPickerValue = sprintf('%04d-%02d', (int) $m[1], (int) $m[2]);
    }
@endphp

<input
    type="month"
    id="{{ $fieldId }}"
    class="{{ $fieldClass }}"
    value="{{ $monthPickerValue }}"
    aria-label="Billing Month"
    data-billing-month-picker="1"
    onchange="
        const parts = this.value.split('-');
        const hidden = this.closest('[data-month-picker-wrap]').querySelector('input[type=hidden]');
        if (parts.length === 2 && hidden) hidden.value = parts[1] + '-' + parts[0];
    "
>

<input type="hidden" name="{{ $fieldName }}" value="{{ $billingMonthValue }}">

<script>
(function () {
    document.querySelectorAll('[data-month-picker-wrap]').forEach(function (wrap) {
        var picker = wrap.querySelector('input[type="month"]');
        var hidden = wrap.querySelector('input[type="hidden"]');
        if (!picker || !hidden) return;

        function sync() {
            var parts = String(picker.value || '').split('-');
            if (parts.length === 2 && parts[0] && parts[1]) {
                hidden.value = parts[1] + '-' + parts[0];
            }
        }

        picker.addEventListener('change', sync);
        sync();
    });
})();
</script>
