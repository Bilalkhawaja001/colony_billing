document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('[data-global-navbar]').forEach(nav=>{const closeDesktop=()=>nav.querySelectorAll('[data-nav-group].is-open').forEach(g=>{g.classList.remove('is-open');const b=g.querySelector('[data-nav-trigger]');if(b)b.setAttribute('aria-expanded','false')});nav.querySelectorAll('[data-nav-trigger]').forEach(btn=>{btn.addEventListener('click',e=>{e.preventDefault();const g=btn.closest('[data-nav-group]');const open=g.classList.contains('is-open');closeDesktop();g.classList.toggle('is-open',!open);btn.setAttribute('aria-expanded',String(!open))});btn.addEventListener('keydown',e=>{if(['Enter',' '].includes(e.key)){e.preventDefault();btn.click()} if(e.key==='Escape'){closeDesktop();btn.focus()}})});document.addEventListener('click',e=>{if(!nav.contains(e.target))closeDesktop()});document.addEventListener('keydown',e=>{if(e.key==='Escape')closeDesktop()});const toggle=nav.querySelector('[data-mobile-toggle]');const panel=nav.querySelector('[data-mobile-panel]');if(toggle&&panel){toggle.addEventListener('click',()=>{const open=panel.hasAttribute('hidden');panel.toggleAttribute('hidden',!open);toggle.setAttribute('aria-expanded',String(open));toggle.querySelector('.material-symbols-outlined').textContent=open?'close':'menu'});toggle.addEventListener('keydown',e=>{if(['Enter',' '].includes(e.key)){e.preventDefault();toggle.click()} if(e.key==='Escape'&&!panel.hasAttribute('hidden'))toggle.click()})}nav.querySelectorAll('[data-mobile-trigger]').forEach(btn=>{btn.addEventListener('click',()=>{const sub=btn.parentElement.querySelector('.ns-mobile-submenu');const open=sub.hasAttribute('hidden');sub.toggleAttribute('hidden',!open);btn.setAttribute('aria-expanded',String(open))});btn.addEventListener('keydown',e=>{if(['Enter',' '].includes(e.key)){e.preventDefault();btn.click()}})})})});

/* =========================================================
   NodeSky Global Navbar Search
   Pages + Employees + Family + Units + Meters
   ========================================================= */

function initNodeSkyGlobalSearch() {
    window.__nodeskyGlobalSearchLoaded = 'v3';

    const navbar = document.querySelector('.ns-global-navbar');
    const searchBox = navbar?.querySelector('.ns-search');
    const input = searchBox?.querySelector('input');

    if (!navbar || !searchBox || !input) {
        return;
    }

    if (searchBox.dataset.searchReady === '1') {
        return;
    }

    const endpoint = searchBox.dataset.globalSearchUrl;

    searchBox.dataset.searchReady = '1';

    let dropdown = searchBox.querySelector(
        '.ns-global-search-results'
    );

    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.className = 'ns-global-search-results';
        dropdown.hidden = true;
        searchBox.appendChild(dropdown);
    }

    let timer = null;
    let requestNumber = 0;

    const normalize = value =>
        (value || '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();

    function getPageResults(query) {
        const q = normalize(query);
        const seen = new Set();
        const results = [];

        navbar
            .querySelectorAll('.ns-desktop-menu a[href]')
            .forEach(link => {
                const title = link.textContent.trim();
                const href = link.href;

                if (!title || !href || seen.has(href)) {
                    return;
                }

                seen.add(href);

                if (normalize(title).includes(q)) {
                    results.push({
                        group: 'Pages',
                        type: 'page',
                        title,
                        subtitle: 'Open module',
                        url: href
                    });
                }
            });

        return results.slice(0, 8);
    }

    function closeResults() {
        dropdown.hidden = true;
        dropdown.innerHTML = '';
    }

    function createResult(row) {
        const link = document.createElement('a');

        link.className =
            'ns-search-result ns-search-result-' +
            (row.type || 'item');

        link.href = row.url || '#';

        const body = document.createElement('span');
        body.className = 'ns-search-result-body';

        const title = document.createElement('span');
        title.className = 'ns-search-result-title';
        title.textContent = row.title || '';

        body.appendChild(title);

        if (row.subtitle) {
            const sub = document.createElement('span');
            sub.className = 'ns-search-result-subtitle';
            sub.textContent = row.subtitle;
            body.appendChild(sub);
        }

        link.appendChild(body);

        return link;
    }

    function render(query, remoteResults = [], loading = false) {
        const q = normalize(query);

        if (!q) {
            closeResults();
            return;
        }

        dropdown.innerHTML = '';

        const allResults = [
            ...getPageResults(q),
            ...remoteResults
        ];

        const groups = new Map();

        allResults.forEach(row => {
            const group = row.group || 'Results';

            if (!groups.has(group)) {
                groups.set(group, []);
            }

            groups.get(group).push(row);
        });

        groups.forEach((rows, groupName) => {
            const section = document.createElement('div');
            section.className = 'ns-search-group';

            const heading = document.createElement('div');
            heading.className = 'ns-search-group-title';
            heading.textContent = groupName;

            section.appendChild(heading);

            rows.forEach(row => {
                section.appendChild(createResult(row));
            });

            dropdown.appendChild(section);
        });

        if (loading) {
            const loadingRow = document.createElement('div');
            loadingRow.className = 'ns-search-status';
            loadingRow.textContent = 'Searching records...';
            dropdown.appendChild(loadingRow);
        }

        if (!allResults.length && !loading) {
            const empty = document.createElement('div');
            empty.className = 'ns-search-empty';
            empty.textContent = 'No matching record found';
            dropdown.appendChild(empty);
        }

        dropdown.hidden = false;
    }

    async function fetchGlobalResults(query, requestId) {
        if (!endpoint || normalize(query).length < 2) {
            render(query, [], false);
            return;
        }

        render(query, [], true);

        try {
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('q', query);

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(
                    'Search request failed: ' + response.status
                );
            }

            const data = await response.json();

            if (requestId !== requestNumber) {
                return;
            }

            render(
                query,
                Array.isArray(data.results)
                    ? data.results
                    : [],
                false
            );

        } catch (error) {
            if (requestId !== requestNumber) {
                return;
            }

            console.error('NodeSky global search:', error);

            render(query, [], false);
        }
    }

    input.addEventListener('input', () => {
        const query = input.value;
        const q = normalize(query);

        clearTimeout(timer);

        requestNumber++;
        const currentRequest = requestNumber;

        if (!q) {
            closeResults();
            return;
        }

        /*
         * Page results appear immediately.
         * Database search starts after 2 characters.
         */
        render(query, [], q.length >= 2);

        if (q.length < 2) {
            return;
        }

        timer = setTimeout(() => {
            fetchGlobalResults(query, currentRequest);
        }, 220);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim()) {
            input.dispatchEvent(
                new Event('input', { bubbles: true })
            );
        }
    });

    input.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeResults();
            input.blur();
            return;
        }

        if (event.key === 'Enter') {
            const first = dropdown.querySelector(
                '.ns-search-result'
            );

            if (first) {
                event.preventDefault();
                window.location.href = first.href;
            }
        }
    });

    document.addEventListener('click', event => {
        if (!searchBox.contains(event.target)) {
            closeResults();
        }
    });

    console.log(
        'NodeSky global search v3 ready'
    );
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initNodeSkyGlobalSearch,
        { once: true }
    );
} else {
    initNodeSkyGlobalSearch();
}
