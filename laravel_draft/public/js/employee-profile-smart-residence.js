(() => {
    if (window.__epSmartResidenceV1) return;
    window.__epSmartResidenceV1 = true;

    function boot() {
        const typeEl   = document.getElementById('residenceTypeSelect');
        const colonyEl = document.getElementById('residenceColonySelect');
        const blockEl  = document.getElementById('residenceBlockSelect');
        const roomEl   = document.getElementById('residenceTargetSelect');

        if (!typeEl || !colonyEl || !blockEl || !roomEl) return;

        if (!document.getElementById('epSmartResidenceStyles')) {
            const style = document.createElement('style');
            style.id = 'epSmartResidenceStyles';

            style.textContent = `
.ep-smart-native{
    display:none!important;
}

.ep-smart-combo{
    position:relative;
    width:100%;
}

.ep-smart-input{
    box-sizing:border-box;
    width:100%;
    min-height:38px;
    padding:8px 40px 8px 14px;
    border:1px solid #cfd8e6;
    border-radius:10px;
    background:#fff;
    color:#1f2937;
    font:inherit;
    outline:none;
}

.ep-smart-input:focus{
    border-color:#82aee8;
    box-shadow:0 0 0 3px rgba(37,99,235,.10);
}

.ep-smart-input:disabled{
    background:#f5f7fa;
    color:#8793a3;
    cursor:not-allowed;
}

.ep-smart-arrow{
    position:absolute;
    right:2px;
    top:2px;
    width:36px;
    height:34px;
    border:0;
    background:transparent;
    cursor:pointer;
    display:grid;
    place-items:center;
    color:#42526a;
    font-size:17px;
    z-index:2;
}

.ep-smart-arrow:disabled{
    cursor:not-allowed;
    opacity:.45;
}

.ep-smart-menu{
    position:absolute;
    left:0;
    right:0;
    top:calc(100% + 4px);
    z-index:20050;
    background:#fff;
    border:1px solid #cbd5e1;
    border-radius:8px;
    box-shadow:0 14px 32px rgba(15,23,42,.18);
    padding:4px;
    display:none;
    overflow-y:auto;

    /* approx 10 options */
    max-height:384px;
}

.ep-smart-menu.is-open{
    display:block;
}

.ep-smart-option{
    display:block;
    width:100%;
    min-height:38px;
    border:0;
    border-radius:6px;
    padding:8px 10px;
    background:#fff;
    color:#1f2937;
    text-align:left;
    cursor:pointer;
    font:inherit;
}

.ep-smart-option:hover,
.ep-smart-option.is-keyboard{
    background:#eef5ff;
    color:#0b5ed7;
}

.ep-smart-empty{
    padding:10px 12px;
    font-size:13px;
    color:#7a8493;
}
`;

            document.head.appendChild(style);
        }

        const normalize = value =>
            String(value ?? '').trim().toLowerCase();

        function createCombo(select, settings = {}) {
            select.classList.add('ep-smart-native');

            const wrap = document.createElement('div');
            wrap.className = 'ep-smart-combo';

            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'ep-smart-input';
            input.autocomplete = 'off';

            const arrow = document.createElement('button');
            arrow.type = 'button';
            arrow.className = 'ep-smart-arrow';
            arrow.textContent = '▼';
            arrow.setAttribute('aria-label', 'Open options');

            const menu = document.createElement('div');
            menu.className = 'ep-smart-menu';
            menu.setAttribute('role', 'listbox');

            wrap.appendChild(input);
            wrap.appendChild(arrow);
            wrap.appendChild(menu);

            let shownItems = [];
            let activeIndex = -1;
            let loading = false;
            let loadingToken = 0;
            let autoRunning = false;

            function placeholder() {
                const first = select.options[0];

                return first
                    ? String(first.textContent || '').trim()
                    : 'Select option';
            }

            function items() {
                return [...select.options]
                    .filter(option =>
                        String(option.value || '').trim() !== '' &&
                        !option.disabled
                    )
                    .map(option => ({
                        value: String(option.value),
                        label: String(
                            option.textContent || option.value
                        ).trim()
                    }))
                    .sort((a, b) =>
                        a.label.localeCompare(
                            b.label,
                            undefined,
                            {
                                sensitivity: 'base',
                                numeric: true
                            }
                        )
                    );
            }

            function findExact(value) {
                const n = normalize(value);

                return items().find(item =>
                    normalize(item.label) === n ||
                    normalize(item.value) === n
                ) || null;
            }

            function close() {
                menu.classList.remove('is-open');
                activeIndex = -1;
            }

            function paintActive() {
                const options = [
                    ...menu.querySelectorAll('.ep-smart-option')
                ];

                options.forEach((el, index) => {
                    el.classList.toggle(
                        'is-keyboard',
                        index === activeIndex
                    );
                });

                const active = options[activeIndex];

                if (active) {
                    active.scrollIntoView({
                        block: 'nearest'
                    });
                }
            }

            function render(showAll = false) {
                if (input.disabled) return;

                const allItems = items();

                const query = showAll
                    ? ''
                    : normalize(input.value);

                shownItems = query
                    ? allItems.filter(item =>
                        normalize(item.label).includes(query) ||
                        normalize(item.value).includes(query)
                    )
                    : allItems;

                menu.innerHTML = '';
                activeIndex = -1;

                if (!shownItems.length) {
                    const empty = document.createElement('div');
                    empty.className = 'ep-smart-empty';
                    empty.textContent = 'No valid options';
                    menu.appendChild(empty);
                } else {
                    shownItems.forEach((item, index) => {
                        const option = document.createElement('button');

                        option.type = 'button';
                        option.className = 'ep-smart-option';
                        option.textContent = item.label;
                        option.dataset.index = String(index);

                        option.addEventListener('mousedown', e => {
                            e.preventDefault();
                        });

                        option.addEventListener('click', () => {
                            choose(item);
                        });

                        menu.appendChild(option);
                    });
                }

                menu.classList.add('is-open');
            }

            function choose(item) {
                if (!item) return false;

                const previous = String(select.value || '');

                select.value = item.value;
                input.value = item.label;

                close();

                /*
                 * Important:
                 * same value re-select hone par dependent fields
                 * clear nahi honge.
                 */
                if (previous !== item.value) {
                    select.dispatchEvent(
                        new Event('change', {
                            bubbles: true
                        })
                    );
                }

                return true;
            }

            function clearInvalid() {
                const previous = String(select.value || '');

                select.value = '';
                input.value = '';
                close();

                if (previous !== '') {
                    select.dispatchEvent(
                        new Event('change', {
                            bubbles: true
                        })
                    );
                }
            }

            function refresh() {
                const currentItems = items();

                if (loading && currentItems.length) {
                    loading = false;
                }

                if (loading) {
                    input.value = '';
                    input.placeholder = 'Loading...';
                    input.disabled = true;
                    arrow.disabled = true;
                    close();
                    return;
                }

                const noOptions = currentItems.length === 0;

                input.disabled = !!select.disabled || noOptions;
                arrow.disabled = input.disabled;
                input.placeholder = placeholder();

                if (select.value) {
                    const selected = currentItems.find(
                        item => item.value === select.value
                    );

                    input.value = selected
                        ? selected.label
                        : '';
                } else if (
                    document.activeElement !== input ||
                    !menu.classList.contains('is-open')
                ) {
                    input.value = '';
                }

                /*
                 * Same rule as previous Residence dropdown:
                 * one Floor => auto select
                 * one Room  => auto select
                 */
                if (
                    settings.autoSingle &&
                    !select.value &&
                    currentItems.length === 1 &&
                    !autoRunning
                ) {
                    autoRunning = true;

                    setTimeout(() => {
                        choose(currentItems[0]);
                        autoRunning = false;
                    }, 0);
                }
            }

            function setLoading(value) {
                loading = !!value;
                const token = ++loadingToken;

                refresh();

                if (loading) {
                    setTimeout(() => {
                        if (
                            loading &&
                            token === loadingToken
                        ) {
                            loading = false;
                            refresh();
                        }
                    }, 8000);
                }
            }

            input.addEventListener('focus', () => {
                render(true);
            });

            input.addEventListener('click', () => {
                render(true);
            });

            input.addEventListener('input', () => {
                render(false);
            });

            input.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    close();
                    return;
                }

                if (
                    event.key === 'ArrowDown' ||
                    event.key === 'ArrowUp'
                ) {
                    event.preventDefault();

                    if (
                        !menu.classList.contains('is-open')
                    ) {
                        render(true);
                    }

                    if (!shownItems.length) return;

                    if (event.key === 'ArrowDown') {
                        activeIndex = Math.min(
                            activeIndex + 1,
                            shownItems.length - 1
                        );
                    } else {
                        activeIndex = Math.max(
                            activeIndex - 1,
                            0
                        );
                    }

                    paintActive();
                    return;
                }

                if (
                    event.key === 'Enter' &&
                    menu.classList.contains('is-open') &&
                    activeIndex >= 0
                ) {
                    event.preventDefault();

                    choose(
                        shownItems[activeIndex]
                    );
                }
            });

            input.addEventListener('blur', () => {
                setTimeout(() => {
                    const typed = String(input.value || '').trim();

                    if (typed === '') {
                        clearInvalid();
                        return;
                    }

                    const exact = findExact(typed);

                    if (exact) {
                        choose(exact);
                    } else {
                        /*
                         * Invalid custom text allowed nahi.
                         */
                        clearInvalid();
                    }
                }, 120);
            });

            arrow.addEventListener('mousedown', e => {
                e.preventDefault();
            });

            arrow.addEventListener('click', () => {
                if (input.disabled) return;

                input.focus();
                render(true);
            });

            document.addEventListener('mousedown', event => {
                if (!wrap.contains(event.target)) {
                    close();
                }
            });

            select.addEventListener('change', refresh);

            const observer = new MutationObserver(refresh);

            observer.observe(select, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['disabled']
            });

            refresh();

            return {
                refresh,
                setLoading,
                close
            };
        }

        const typeCombo = createCombo(typeEl);
        const colonyCombo = createCombo(colonyEl);

        const blockCombo = createCombo(blockEl, {
            autoSingle: true
        });

        const roomCombo = createCombo(roomEl, {
            autoSingle: true
        });

        /*
         * Existing cascade handlers continue doing the actual
         * API calls. These only provide Loading UI.
         */

        typeEl.addEventListener('change', () => {
            if (typeEl.value) {
                colonyCombo.setLoading(true);
            }

            blockCombo.setLoading(false);
            roomCombo.setLoading(false);
        });

        colonyEl.addEventListener('change', () => {
            if (typeEl.value && colonyEl.value) {
                blockCombo.setLoading(true);
            }

            roomCombo.setLoading(false);
        });

        blockEl.addEventListener('change', () => {
            if (
                typeEl.value &&
                colonyEl.value &&
                blockEl.value
            ) {
                roomCombo.setLoading(true);
            }
        });

        /*
         * Modal open hone par selects ki latest state
         * custom controls mein sync karo.
         */
        const modal = document.getElementById(
            'residenceActionModal'
        );

        if (modal) {
            new MutationObserver(() => {
                if (modal.classList.contains('is-open')) {
                    typeCombo.refresh();
                    colonyCombo.refresh();
                    blockCombo.refresh();
                    roomCombo.refresh();
                }
            }).observe(modal, {
                attributes: true,
                attributeFilter: ['class']
            });
        }

        console.log(
            'Employee Profile smart residence dropdowns v1 ready'
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            boot,
            {once:true}
        );
    } else {
        boot();
    }
})();
