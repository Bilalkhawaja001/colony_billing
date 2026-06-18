@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Department Master</h4>
            <div class="text-muted small">
                Read-only view from employees_master.department / section / sub_section
            </div>
        </div>
        <span class="badge bg-secondary">No DB Write</span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Department</label>
                    <select id="departmentFilter" class="form-select">
                        <option value="">ALL</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Section</label>
                    <select id="sectionFilter" class="form-select">
                        <option value="">ALL</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input id="searchBox" class="form-control" placeholder="Department / Section / Sub Section">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="reloadBtn" class="btn btn-primary w-100">Load</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header fw-bold">Department Summary</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th class="text-end">Employees</th>
                                </tr>
                            </thead>
                            <tbody id="departmentSummaryBody">
                                <tr><td colspan="2">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header fw-bold">Department / Section / Sub Section</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Section</th>
                                    <th>Sub Section</th>
                                    <th class="text-end">Employees</th>
                                </tr>
                            </thead>
                            <tbody id="detailBody">
                                <tr><td colspan="4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer small text-muted" id="metaText">
                    Source: employees_master | Read-only
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const listUrl = "{{ url('/department-master/list') }}";

    const departmentFilter = document.getElementById('departmentFilter');
    const sectionFilter = document.getElementById('sectionFilter');
    const searchBox = document.getElementById('searchBox');
    const reloadBtn = document.getElementById('reloadBtn');
    const departmentSummaryBody = document.getElementById('departmentSummaryBody');
    const detailBody = document.getElementById('detailBody');
    const metaText = document.getElementById('metaText');

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (ch) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[ch];
        });
    }

    function uniqueOptions(rows, key) {
        const set = new Set();
        rows.forEach(row => {
            if (row[key]) set.add(row[key]);
        });
        return Array.from(set).sort();
    }

    function fillSelect(select, options, current) {
        select.innerHTML = '<option value="">ALL</option>';
        options.forEach(value => {
            const opt = document.createElement('option');
            opt.value = value;
            opt.textContent = value;
            if (value === current) opt.selected = true;
            select.appendChild(opt);
        });
    }

    async function loadData() {
        departmentSummaryBody.innerHTML = '<tr><td colspan="2">Loading...</td></tr>';
        detailBody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';

        const params = new URLSearchParams();
        if (departmentFilter.value) params.set('department', departmentFilter.value);
        if (sectionFilter.value) params.set('section', sectionFilter.value);
        if (searchBox.value.trim()) params.set('q', searchBox.value.trim());

        const res = await fetch(listUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) {
            departmentSummaryBody.innerHTML = '<tr><td colspan="2">Load failed</td></tr>';
            detailBody.innerHTML = '<tr><td colspan="4">Load failed</td></tr>';
            return;
        }

        const data = await res.json();

        const currentDept = departmentFilter.value;
        const currentSection = sectionFilter.value;

        fillSelect(
            departmentFilter,
            (data.department_summary || []).map(row => row.department),
            currentDept
        );

        fillSelect(
            sectionFilter,
            uniqueOptions(data.section_summary || [], 'section'),
            currentSection
        );

        departmentSummaryBody.innerHTML = (data.department_summary || []).map(row => `
            <tr>
                <td>${esc(row.department)}</td>
                <td class="text-end">${esc(row.total)}</td>
            </tr>
        `).join('') || '<tr><td colspan="2">No data</td></tr>';

        detailBody.innerHTML = (data.rows || []).map(row => `
            <tr>
                <td>${esc(row.department)}</td>
                <td>${esc(row.section)}</td>
                <td>${esc(row.sub_section)}</td>
                <td class="text-end">${esc(row.total)}</td>
            </tr>
        `).join('') || '<tr><td colspan="4">No data</td></tr>';

        metaText.textContent = 'Source: ' + (data.meta?.source_table || 'employees_master') + ' | Read-only | Rows shown: ' + (data.rows || []).length;
    }

    reloadBtn.addEventListener('click', loadData);
    departmentFilter.addEventListener('change', function () {
        sectionFilter.value = '';
        loadData();
    });
    sectionFilter.addEventListener('change', loadData);

    let timer = null;
    searchBox.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(loadData, 400);
    });

    loadData();
})();
</script>
@endsection
