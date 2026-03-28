@extends('layouts.app')
@section('page_title','Month Control')
@section('page_subtitle','Read-only operational month-state monitor sourced from util_month_cycle.')
@section('content')
<div class="grid">
<div class="col-12 card">
    <h3 class="section-title">Month States</h3>
    <table>
        <thead><tr><th>Month Cycle</th><th>State</th><th>Locked At</th><th>Finalized At</th></tr></thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row->month_cycle ?? '' }}</td>
                <td><span class="badge">{{ $row->state ?? '' }}</span></td>
                <td>{{ $row->locked_at ?? '—' }}</td>
                <td>{{ $row->finalized_at ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4"><div class="empty">No month state rows found.</div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection