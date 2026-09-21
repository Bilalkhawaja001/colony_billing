<style id="nodesky-global-svg-icons">

/*
|--------------------------------------------------------------------------
| Material Symbols font completely bypassed
|--------------------------------------------------------------------------
*/

.material-symbols-outlined {
    font-size: 0 !important;
    line-height: 1 !important;
    width: 20px;
    min-width: 20px;
    height: 20px;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    overflow: visible;
}

.material-symbols-outlined > svg {
    width: 20px;
    height: 20px;
    display: block;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    pointer-events: none;
}

</style>

<script id="nodesky-global-svg-icon-engine">
(function () {
    'use strict';

    const I = {

        search:'<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',

        visibility:'<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12"/><circle cx="12" cy="12" r="2.5"/>',

        edit:'<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>',

        add:'<path d="M12 5v14"/><path d="M5 12h14"/>',

        close:'<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',

        check:'<path d="m5 12 4 4L19 6"/>',

        check_circle:'<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',

        error:'<circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 17h.01"/>',

        warning:'<path d="M10.3 3.6 2.4 18a2 2 0 0 0 1.8 3h15.6a2 2 0 0 0 1.8-3L13.7 3.6a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',

        groups:'<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><path d="M15 14h2a4 4 0 0 1 4 4v2"/>',

        group:'<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2"/><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/>',

        person_add:'<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a6 6 0 0 1 6-6h2"/><path d="M19 8v6"/><path d="M16 11h6"/>',

        person_remove:'<circle cx="9" cy="7" r="4"/><path d="M3 21v-2a6 6 0 0 1 6-6h2"/><path d="M16 11h6"/>',

        group_add:'<circle cx="8" cy="8" r="3"/><path d="M2 20v-2a5 5 0 0 1 5-5h2"/><path d="M17 8v6"/><path d="M14 11h6"/>',

        pause_circle:'<circle cx="12" cy="12" r="9"/><path d="M10 9v6"/><path d="M14 9v6"/>',

        home:'<path d="m3 11 9-8 9 8"/><path d="M5 10v11h14V10"/><path d="M9 21v-6h6v6"/>',

        home_work:'<path d="M3 21V9l9-6 9 6v12"/><path d="M9 21v-6h6v6"/><path d="M6 12h2"/><path d="M16 12h2"/>',

        apartment:'<rect x="5" y="3" width="14" height="18"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/>',

        domain:'<rect x="4" y="3" width="16" height="18"/><path d="M8 7h2M14 7h2M8 11h2M14 11h2M10 21v-5h4v5"/>',

        location_off:'<path d="M9 5.5A7 7 0 0 1 19 12c0 3-3 6.5-7 10"/><path d="M7 19c-2.5-3-4-5.2-4-7a8 8 0 0 1 1-4"/><path d="m3 3 18 18"/>',

        description:'<path d="M6 2h8l4 4v16H6Z"/><path d="M14 2v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/>',

        badge:'<rect x="3" y="5" width="18" height="16" rx="2"/><circle cx="9" cy="11" r="2.5"/><path d="M5.5 18a4 4 0 0 1 7 0"/><path d="M15 10h3M15 14h3"/>',

        settings:'<circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1l2-1.5-2-3.4-2.4 1A7 7 0 0 0 15 6l-.3-2.6h-4L10.5 6A7 7 0 0 0 9 7.1l-2.4-1-2 3.4 2 1.5A7 7 0 0 0 6.5 12a7 7 0 0 0 .1 1l-2 1.5 2 3.4 2.4-1A7 7 0 0 0 10.5 18l.3 2.6h4L15 18a7 7 0 0 0 1.5-1.1l2.4 1 2-3.4-2-1.5A7 7 0 0 0 19 12Z"/>',

        notifications:'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',

        help:'<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.7 2.7 0 1 1 4.5 2c-1.3 1-2 1.4-2 3"/><path d="M12 18h.01"/>',

        help_outline:'<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.7 2.7 0 1 1 4.5 2c-1.3 1-2 1.4-2 3"/><path d="M12 18h.01"/>',

        refresh:'<path d="M20 6v5h-5"/><path d="M4 18v-5h5"/><path d="M18 11a7 7 0 0 0-12-4L4 11"/><path d="M6 13a7 7 0 0 0 12 4l2-4"/>',

        sync:'<path d="M20 7h-5V2"/><path d="M4 17h5v5"/><path d="M18 11a7 7 0 0 0-12-5L4 8"/><path d="M6 13a7 7 0 0 0 12 5l2-2"/>',

        filter_list:'<path d="M4 6h16"/><path d="M7 12h10"/><path d="M10 18h4"/>',

        download:'<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>',

        upload:'<path d="M12 15V3"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/>',

        upload_file:'<path d="M6 2h8l4 4v16H6Z"/><path d="M14 2v5h5"/><path d="M12 18v-7"/><path d="m9 14 3-3 3 3"/>',

        cloud_upload:'<path d="M6 18a5 5 0 0 1 1-9.9A7 7 0 0 1 20 11a4 4 0 0 1-1 7H6Z"/><path d="M12 17V9"/><path d="m9 12 3-3 3 3"/>',

        table_view:'<rect x="3" y="4" width="18" height="16" rx="1"/><path d="M3 9h18"/><path d="M9 9v11"/><path d="M15 9v11"/>',

        history:'<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/><path d="M12 7v5l3 2"/>',

        folder:'<path d="M3 6h6l2 2h10v11H3Z"/>',

        lock:'<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',

        save:'<path d="M5 3h12l3 3v15H4V3Z"/><path d="M8 3v6h8V3"/><path d="M8 21v-7h8v7"/>',

        print:'<path d="M6 9V3h12v6"/><rect x="6" y="14" width="12" height="7"/><path d="M6 17H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/>',

        more_vert:'<circle cx="12" cy="5" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="19" r="1" fill="currentColor" stroke="none"/>',

        expand_more:'<path d="m6 9 6 6 6-6"/>',

        expand_less:'<path d="m6 15 6-6 6 6"/>',

        arrow_drop_down:'<path d="m7 10 5 5 5-5"/>',

        chevron_right:'<path d="m9 18 6-6-6-6"/>',

        chevron_left:'<path d="m15 18-6-6 6-6"/>',

        arrow_forward:'<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',

        arrow_back:'<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',

        arrow_downward:'<path d="M12 5v14"/><path d="m7 14 5 5 5-5"/>',

        calendar_today:'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/>',

        receipt_long:'<path d="M6 2h12v20l-3-2-3 2-3-2-3 2Z"/><path d="M9 7h6M9 11h6M9 15h4"/>',

        payments:'<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="12" cy="12" r="3"/>',

        money_off:'<path d="m3 3 18 18"/><path d="M8 7c1-1 2-1 4-1 3 0 5 1 5 3"/><path d="M7 15c1 2 3 3 6 3 1 0 2-.2 3-.6"/>',

        account_balance:'<path d="m3 9 9-6 9 6"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8"/><path d="M3 21h18"/>',

        inventory_2:'<path d="M5 8h14v13H5Z"/><path d="M3 4h18v4H3Z"/><path d="M9 12h6"/>',

        speed:'<path d="M4 18a8 8 0 1 1 16 0"/><path d="m12 14 4-4"/>',

        assignment_late:'<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2"/><path d="M12 9v4"/><path d="M12 17h.01"/>',

        health_and_safety:'<path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6Z"/><path d="M12 8v6M9 11h6"/>',

        water_drop:'<path d="M12 2S6 9 6 14a6 6 0 0 0 12 0c0-5-6-12-6-12Z"/>',

        electric_bolt:'<path d="m13 2-8 12h6l-1 8 9-13h-6Z"/>',

        database:'<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',

        cloud:'<path d="M6 18a5 5 0 0 1 1-9.9A7 7 0 0 1 20 11a4 4 0 0 1-1 7Z"/>',

        memory:'<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 1v5M15 1v5M9 18v5M15 18v5M1 9h5M1 15h5M18 9h5M18 15h5"/>',

        pie_chart:'<path d="M11 3a9 9 0 1 0 10 10H11Z"/><path d="M15 3v6h6a9 9 0 0 0-6-6Z"/>',

        trending_up:'<path d="m3 17 6-6 4 4 7-8"/><path d="M15 7h5v5"/>',

        build:'<path d="M14 7a5 5 0 0 0-6-4l3 3-3 3-3-3a5 5 0 0 0 6 6l7 7 3-3Z"/>',

        share:'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m9 11 6-4M9 13l6 4"/>',

        hourglass_empty:'<path d="M6 2h12M6 22h12"/><path d="M8 2c0 5 4 6 4 10s-4 5-4 10M16 2c0 5-4 6-4 10s4 5 4 10"/>',

        radio_button_unchecked:'<circle cx="12" cy="12" r="9"/>',

        task:'<path d="M6 2h8l4 4v16H6Z"/><path d="M14 2v5h5"/><path d="m9 15 2 2 4-5"/>',

        shield_person:'<path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6Z"/><circle cx="12" cy="10" r="2"/><path d="M9 16c.5-2 1.5-3 3-3s2.5 1 3 3"/>',

        verified_user:'<path d="M12 3 5 6v5c0 5 3 8 7 10 4-2 7-5 7-10V6Z"/><path d="m9 12 2 2 4-5"/>'
    };

    const fallback =
        '<circle cx="12" cy="12" r="9"/>' +
        '<circle cx="8" cy="12" r="1" fill="currentColor" stroke="none"/>' +
        '<circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/>' +
        '<circle cx="16" cy="12" r="1" fill="currentColor" stroke="none"/>';

    function convert(el) {

        if (!el || el.dataset.svgConverted === '1') {
            return;
        }

        const name = (el.textContent || '').trim();

        if (!name) {
            return;
        }

        /*
         * Preserve requested icon size when inline style contains one.
         */
        let size = 20;

        const inlineSize = parseInt(el.style.fontSize || '', 10);

        if (inlineSize >= 12 && inlineSize <= 64) {
            size = inlineSize;
        }

        el.dataset.materialName = name;
        el.dataset.svgConverted = '1';

        el.innerHTML =
            '<svg viewBox="0 0 24 24" ' +
            'width="' + size + '" height="' + size + '" ' +
            'style="width:' + size + 'px;height:' + size + 'px" ' +
            'aria-hidden="true" focusable="false">' +
            (I[name] || fallback) +
            '</svg>';

        el.style.width = size + 'px';
        el.style.minWidth = size + 'px';
        el.style.height = size + 'px';
    }


    function scan(root) {

        if (!root) return;

        if (
            root.nodeType === 1 &&
            root.classList &&
            root.classList.contains('material-symbols-outlined')
        ) {
            convert(root);
        }

        if (root.querySelectorAll) {
            root.querySelectorAll('.material-symbols-outlined')
                .forEach(convert);
        }
    }


    /*
     * Watch DOM immediately.
     * Raw ligature text is already hidden by CSS above.
     */
    const observer = new MutationObserver(function (mutations) {

        for (const mutation of mutations) {

            for (const node of mutation.addedNodes) {

                if (node.nodeType === 1) {
                    scan(node);
                }
            }
        }
    });

    observer.observe(document.documentElement, {
        childList: true,
        subtree: true
    });


    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                scan(document);
            },
            { once:true }
        );

    } else {

        scan(document);
    }

})();
</script>
