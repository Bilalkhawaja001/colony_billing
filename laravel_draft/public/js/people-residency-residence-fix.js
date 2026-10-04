(() => {
    if (window.__rmResidenceFixV2) return;
    window.__rmResidenceFixV2 = true;

    function boot() {
        const drawer = document.getElementById('rmResidenceDrawer');
        const tree = window.RM_TREE || {};

        if (!drawer || !Object.keys(tree).length) return;

        const $ = id => document.getElementById(id);

        const hu = $('rmResidenceUnit');
        const hr = $('rmResidenceRoom');
        const saveBtn = $('rmSaveResidence');

        if (!hu || !hr || !saveBtn) return;

        if (!document.getElementById('rmResidenceSmartDropdownStyles')) {
            const style = document.createElement('style');
            style.id = 'rmResidenceSmartDropdownStyles';

            style.textContent = `
.rm-smart-combo{
    position:relative;
    width:100%;
}
.rm-smart-combo>.rm-input{
    width:100%;
    padding-right:40px!important;
}
.rm-smart-arrow{
    position:absolute;
    right:1px;
    top:1px;
    width:38px;
    height:calc(100% - 2px);
    border:0;
    background:transparent;
    cursor:pointer;
    color:#5f6673;
    display:grid;
    place-items:center;
    z-index:2;
}
.rm-smart-arrow:after{
    content:'⌄';
    font-size:19px;
    line-height:1;
    transform:translateY(-2px);
}
.rm-smart-menu{
    position:absolute;
    left:0;
    right:0;
    top:calc(100% + 4px);
    z-index:12050;
    background:#fff;
    border:1px solid #cfd5df;
    border-radius:8px;
    box-shadow:0 12px 28px rgba(15,23,42,.16);
    padding:4px;
    max-height:384px;
    overflow-y:auto;
    display:none;
}
.rm-smart-menu.is-open{
    display:block;
}
.rm-smart-option{
    width:100%;
    min-height:52px;
    padding:8px 10px;
    border:0;
    background:#fff;
    border-radius:6px;
    text-align:left;
    cursor:pointer;
    color:#202938;
    font:inherit;
    display:block;
}

.rm-smart-option-main{
    display:block;
    width:100%;
    font-size:14px;
    font-weight:700;
    line-height:1.3;
    white-space:normal;
    overflow:visible;
    text-overflow:clip;
    color:#172033;
}

.rm-smart-option-meta{
    display:block;
    width:100%;
    margin-top:3px;
    padding:0;
    background:transparent;
    border-radius:0;
    font-size:12px;
    font-weight:500;
    line-height:1.25;
    color:#64748b;
    white-space:normal;
}
.rm-smart-option:hover,
.rm-smart-option.is-keyboard{
    background:#f1f5f9;
    color:#0056b3;
}
.rm-smart-empty{
    padding:10px 12px;
    color:#7a8290;
    font-size:13px;
}
`;

            document.head.appendChild(style);
        }

        /*
         * Clone inputs so old native datalist input handlers
         * cannot interfere with the new controlled dropdowns.
         */
        function replaceInput(id) {
            const old = $(id);
            if (!old) return null;

            const clone = old.cloneNode(true);

            clone.removeAttribute('list');
            clone.setAttribute('autocomplete', 'off');

            old.replaceWith(clone);

            return clone;
        }

        const ci = replaceInput('rmResColony');
        const fi = replaceInput('rmResFloor');
        const ri = replaceInput('rmResRoomPick');

        if (!ci || !fi || !ri) return;

        const norm = value =>
            String(value ?? '').trim().toLowerCase();

        const alpha = values =>
            [...new Set(
                values.filter(v => String(v).trim() !== '')
            )].sort((a, b) =>
                String(a).localeCompare(
                    String(b),
                    undefined,
                    {
                        sensitivity: 'base',
                        numeric: true
                    }
                )
            );

        const colonyOptions = () =>
            alpha(Object.keys(tree));

        const floorOptions = () =>
            alpha(
                tree[ci.value]
                    ? Object.keys(tree[ci.value])
                    : []
            );

        const roomRows = () =>
            (
                tree[ci.value] &&
                tree[ci.value][fi.value]
            )
                ? tree[ci.value][fi.value]
                : [];

        const roomOptions = () =>
            alpha(
                roomRows().map(row => row.room)
            );

        function canonical(value, options) {
            const n = norm(value);

            return options.find(
                option => norm(option) === n
            ) || '';
        }

        function findPath(unit, room) {
            const unitN = norm(unit);
            const roomN = norm(room);

            for (const colony of Object.keys(tree)) {
                for (
                    const floor
                    of Object.keys(tree[colony] || {})
                ) {
                    for (
                        const row
                        of tree[colony][floor] || []
                    ) {
                        if (
                            norm(row.unit) === unitN &&
                            (
                                !roomN ||
                                norm(row.room) === roomN
                            )
                        ) {
                            return {
                                colony,
                                floor,
                                room: row.room,
                                unit: row.unit
                            };
                        }
                    }
                }
            }

            return null;
        }

        /*
         * Rebuild the actual values submitted by Assign.
         */
        function syncHidden() {
            const colony = canonical(
                ci.value,
                colonyOptions()
            );

            const floor = canonical(
                fi.value,
                floorOptions()
            );

            const room = canonical(
                ri.value,
                roomOptions()
            );

            if (!colony || !floor || !room) {
                hu.value = '';
                hr.value = '';
                return false;
            }

            ci.value = colony;
            fi.value = floor;
            ri.value = room;

            const row = roomRows().find(
                item => norm(item.room) === norm(room)
            );

            hu.value = row
                ? String(row.unit || '')
                : '';

            hr.value = row
                ? String(row.room || '')
                : '';

            return !!(hu.value && hr.value);
        }

        function makeCombo(
            input,
            getOptions,
            onCommit,
            onInvalid,
            labelFor = value => value
        ) {
            const wrap = document.createElement('div');
            wrap.className = 'rm-smart-combo';

            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);

            const arrow = document.createElement('button');
            arrow.type = 'button';
            arrow.className = 'rm-smart-arrow';
            arrow.setAttribute(
                'aria-label',
                'Open options'
            );

            wrap.appendChild(arrow);

            const menu = document.createElement('div');
            menu.className = 'rm-smart-menu';
            menu.setAttribute('role', 'listbox');

            wrap.appendChild(menu);

            let committed = canonical(
                input.value,
                getOptions()
            );

            if (committed) {
                input.value = committed;
            }

            let activeIndex = -1;
            let shown = [];

            const options = () =>
                alpha(getOptions());

            function close() {
                menu.classList.remove('is-open');
                activeIndex = -1;
            }

            function draw(showAll) {
                const all = options();

                const query = showAll
                    ? ''
                    : norm(input.value);

                shown = query
                    ? all.filter(
                        value =>
                            norm(value).includes(query)
                    )
                    : all;

                menu.innerHTML = '';
                activeIndex = -1;

                if (!shown.length) {
                    const empty =
                        document.createElement('div');

                    empty.className =
                        'rm-smart-empty';

                    empty.textContent =
                        'No valid options';

                    menu.appendChild(empty);
                } else {
                    shown.forEach((value, index) => {
                        const option =
                            document.createElement(
                                'button'
                            );

                        option.type = 'button';
                        option.className =
                            'rm-smart-option';

                        const display = labelFor(value);

                        if (
                            display &&
                            typeof display === 'object'
                        ) {
                            const main =
                                document.createElement('span');

                            main.className =
                                'rm-smart-option-main';

                            main.textContent =
                                display.primary || value;

                            const meta =
                                document.createElement('span');

                            meta.className =
                                'rm-smart-option-meta';

                            meta.textContent =
                                display.meta || '';

                            option.appendChild(main);
                            option.appendChild(meta);
                        } else {
                            option.textContent = display;
                        }
                        option.dataset.index =
                            String(index);

                        option.addEventListener(
                            'mousedown',
                            event =>
                                event.preventDefault()
                        );

                        option.addEventListener(
                            'click',
                            () => choose(value)
                        );

                        menu.appendChild(option);
                    });
                }

                menu.classList.add('is-open');
            }

            function paintActive() {
                [
                    ...menu.querySelectorAll(
                        '.rm-smart-option'
                    )
                ].forEach(
                    (el, index) =>
                        el.classList.toggle(
                            'is-keyboard',
                            index === activeIndex
                        )
                );

                const active =
                    menu.querySelector(
                        '.rm-smart-option[data-index="' +
                        activeIndex +
                        '"]'
                    );

                if (active) {
                    active.scrollIntoView({
                        block: 'nearest'
                    });
                }
            }

            function choose(value, notify = true) {
                const valid = canonical(
                    value,
                    options()
                );

                if (!valid) return false;

                const previous = committed;

                input.value = valid;
                committed = valid;

                input.setCustomValidity('');
                close();

                if (notify) {
                    onCommit(valid, previous);
                }

                return true;
            }

            function clear(notify = false) {
                const previous = committed;

                input.value = '';
                committed = '';

                input.setCustomValidity('');
                close();

                if (notify && previous) {
                    onInvalid(previous);
                }
            }

            function setValue(value) {
                const valid = canonical(
                    value,
                    options()
                );

                input.value = valid || '';
                committed = valid || '';

                input.setCustomValidity('');
                close();

                return !!valid;
            }

            function refresh() {
                const valid = canonical(
                    input.value,
                    options()
                );

                if (valid) {
                    input.value = valid;
                    committed = valid;
                } else if (input.value) {
                    input.value = '';
                    committed = '';
                }
            }

            /*
             * Click/focus = show complete valid list.
             */
            input.addEventListener(
                'click',
                () => draw(true)
            );

            input.addEventListener(
                'focus',
                () => draw(true)
            );

            /*
             * Typing = contains search.
             * Does NOT clear child fields yet.
             */
            input.addEventListener(
                'input',
                () => {
                    input.setCustomValidity('');
                    draw(false);
                }
            );

            input.addEventListener(
                'keydown',
                event => {
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
                            !menu.classList.contains(
                                'is-open'
                            )
                        ) {
                            draw(true);
                        }

                        if (!shown.length) return;

                        if (
                            event.key ===
                            'ArrowDown'
                        ) {
                            activeIndex = Math.min(
                                activeIndex + 1,
                                shown.length - 1
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
                        menu.classList.contains(
                            'is-open'
                        ) &&
                        activeIndex >= 0
                    ) {
                        event.preventDefault();

                        choose(
                            shown[activeIndex]
                        );
                    }
                }
            );

            /*
             * Only exact valid values survive blur.
             */
            input.addEventListener(
                'blur',
                () => {
                    setTimeout(() => {
                        const valid = canonical(
                            input.value,
                            options()
                        );

                        if (valid) {
                            if (
                                norm(valid) !==
                                norm(committed)
                            ) {
                                choose(
                                    valid,
                                    true
                                );
                            } else {
                                input.value =
                                    valid;
                            }
                        } else if (
                            String(
                                input.value
                            ).trim() !== ''
                        ) {
                            const previous =
                                committed;

                            input.value = '';
                            committed = '';

                            onInvalid(previous);
                        }

                        close();
                    }, 120);
                }
            );

            arrow.addEventListener(
                'mousedown',
                event =>
                    event.preventDefault()
            );

            arrow.addEventListener(
                'click',
                () => {
                    if (input.disabled) return;

                    input.focus();
                    draw(true);
                }
            );

            document.addEventListener(
                'mousedown',
                event => {
                    if (
                        !wrap.contains(
                            event.target
                        )
                    ) {
                        close();
                    }
                }
            );

            return {
                choose,
                clear,
                setValue,
                refresh,
                options
            };
        }

        let colonyCombo;
        let floorCombo;
        let roomCombo;

        function resetRoom() {
            roomCombo.clear(false);

            hu.value = '';
            hr.value = '';
        }

        function resetFloorAndRoom() {
            floorCombo.clear(false);
            resetRoom();
        }

        function autoRoomIfSingle() {
            const rooms = roomOptions();

            if (rooms.length === 1) {
                roomCombo.choose(
                    rooms[0],
                    true
                );
            }
        }

        function autoFloorIfSingle() {
            const floors = floorOptions();

            if (floors.length === 1) {
                floorCombo.choose(
                    floors[0],
                    true
                );
            }
        }

        colonyCombo = makeCombo(
            ci,
            colonyOptions,

            (value, previous) => {
                /*
                 * Clear children only after
                 * a DIFFERENT valid colony is selected.
                 */
                if (
                    norm(value) !==
                    norm(previous)
                ) {
                    resetFloorAndRoom();
                }

                floorCombo.refresh();
                autoFloorIfSingle();
            },

            () => {
                resetFloorAndRoom();
            }
        );

        floorCombo = makeCombo(
            fi,
            floorOptions,

            (value, previous) => {
                /*
                 * Room clears only after
                 * Floor actually changes.
                 */
                if (
                    norm(value) !==
                    norm(previous)
                ) {
                    resetRoom();
                }

                roomCombo.refresh();
                autoRoomIfSingle();
            },

            () => {
                resetRoom();
            }
        );

        roomCombo = makeCombo(
            ri,
            roomOptions,

            () => {
                syncHidden();
            },

            () => {
                hu.value = '';
                hr.value = '';
            },

            value => {
                const row = roomRows().find(
                    item => norm(item.room) === norm(value)
                );

                const count = row
                    ? Number(row.n || 0)
                    : 0;

                if (count <= 0) {
                    return {
                        primary: value,
                        meta: 'Vacant'
                    };
                }

                return {
                    primary: value,
                    meta: count + ' ' +
                        (count === 1
                            ? 'occupant'
                            : 'occupants')
                };
            }
        );

        /*
         * Called each time Assign / Transfer drawer opens.
         */
        function syncDrawerState() {
            const existingUnit =
                String(hu.value || '').trim();

            const existingRoom =
                String(hr.value || '').trim();

            /*
             * Existing resident:
             * display the path belonging to that employee.
             */
            if (existingUnit) {
                const path =
                    findPath(
                        existingUnit,
                        existingRoom
                    );

                if (path) {
                    colonyCombo.setValue(
                        path.colony
                    );

                    floorCombo.refresh();

                    floorCombo.setValue(
                        path.floor
                    );

                    roomCombo.refresh();

                    roomCombo.setValue(
                        path.room
                    );

                    hu.value = path.unit;
                    hr.value = path.room;

                    return;
                }

                /*
                 * Existing value not present in valid tree:
                 * do not display invalid legacy values.
                 */
                colonyCombo.clear(false);
                floorCombo.clear(false);
                roomCombo.clear(false);

                return;
            }

            /*
             * NEW / next employee:
             *
             * Keep the previous visible selection
             * for fast repeated allocation, but rebuild
             * Unit_ID + Room hidden state.
             */
            const colony = canonical(
                ci.value,
                colonyOptions()
            );

            if (!colony) return;

            colonyCombo.setValue(colony);

            floorCombo.refresh();

            const floor = canonical(
                fi.value,
                floorOptions()
            );

            if (!floor) {
                autoFloorIfSingle();
                return;
            }

            floorCombo.setValue(floor);

            roomCombo.refresh();

            const room = canonical(
                ri.value,
                roomOptions()
            );

            if (room) {
                roomCombo.setValue(room);
                syncHidden();
            } else {
                autoRoomIfSingle();
            }
        }

        /*
         * openResidence() changes hidden Unit/Room first.
         * Observer runs immediately afterwards and reconciles
         * visible + hidden state.
         */
        const observer =
            new MutationObserver(() => {
                if (
                    drawer.classList.contains(
                        'is-open'
                    )
                ) {
                    setTimeout(
                        syncDrawerState,
                        0
                    );
                }
            });

        observer.observe(
            drawer,
            {
                attributes: true,
                attributeFilter: ['class']
            }
        );

        /*
         * Capture phase runs BEFORE the existing Assign handler.
         * Therefore same Colony/Floor/Room can immediately be
         * reused for the next employee.
         */
        document.addEventListener(
            'click',
            event => {
                if (
                    !event.target.closest(
                        '#rmSaveResidence'
                    )
                ) {
                    return;
                }

                if (syncHidden()) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                ri.setCustomValidity(
                    'Select a valid Colony, Floor and Room from the list.'
                );

                ri.reportValidity();

                ri.setCustomValidity('');
            },
            true
        );

        console.log(
            'Residence dropdown/state fix v2 ready'
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
