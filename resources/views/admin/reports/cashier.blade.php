@extends('layouts.admin')
@section('title', 'Cashier Performance')
@section('heading', 'Cashier Performance')
@section('subheading', \Illuminate\Support\Carbon::parse($from)->format('d M Y').' – '.\Illuminate\Support\Carbon::parse($to)->format('d M Y'))

@section('content')
    <style>
        .report-toolbar { display:flex; flex-wrap:wrap; gap:8px; align-items:center; background:#fff; border:1px solid #ececec; border-radius:16px; padding:12px 14px; margin-bottom:16px; }
        .report-chip { border:1px solid #e5e7eb; background:#fff; color:#121212; border-radius:10px; padding:8px 12px; font-weight:800; font-size:13px; text-decoration:none; }
        .report-chip.is-active { background:linear-gradient(135deg,#e2c15a,#d4af37); border-color:#d4af37; }
        .report-filter { display:flex; flex-wrap:wrap; gap:8px; margin-left:auto; align-items:end; }
        .report-filter input, .report-filter select { margin:0 !important; }
    </style>

    <div class="report-toolbar">
        <a class="report-chip {{ $preset === 'today' ? 'is-active' : '' }}" href="{{ route('admin.reports.cashier', ['preset' => 'today']) }}">Today</a>
        <a class="report-chip {{ $preset === 'week' ? 'is-active' : '' }}" href="{{ route('admin.reports.cashier', ['preset' => 'week']) }}">This week</a>
        <a class="report-chip {{ $preset === 'month' ? 'is-active' : '' }}" href="{{ route('admin.reports.cashier', ['preset' => 'month']) }}">This month</a>
        <a class="btn btn-secondary" href="{{ route('admin.reports.index', ['preset' => $preset, 'from' => $from, 'to' => $to]) }}">Back to reports</a>
        <a class="btn" href="{{ route('admin.reports.cashier.export', ['preset' => $preset, 'from' => $from, 'to' => $to, 'cashier_id' => $cashierId]) }}">Export PDF</a>
        <form class="report-filter" method="GET" action="{{ route('admin.reports.cashier') }}">
            <input type="date" name="from" value="{{ $from }}">
            <input type="date" name="to" value="{{ $to }}">
            <select name="cashier_id">
                <option value="">All cashiers</option>
                @foreach($cashiers as $cashier)
                    <option value="{{ $cashier->id }}" @selected((string) $cashierId === (string) $cashier->id)>{{ $cashier->name }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Cashier</th>
                        <th>Tickets</th>
                        <th>Net sales</th>
                        <th>Discounts</th>
                        <th>Returns</th>
                        <th>Cash</th>
                        <th>M-Pesa</th>
                        <th>Card</th>
                        <th>Bank</th>
                        <th>Shift variance</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td><strong>{{ $row->cashier }}</strong></td>
                        <td>{{ number_format($row->transactions) }}</td>
                        <td><strong>KES {{ number_format($row->net, 2) }}</strong></td>
                        <td>KES {{ number_format($row->discounts, 2) }}</td>
                        <td>KES {{ number_format($row->returns, 2) }}</td>
                        <td>KES {{ number_format($row->cash, 2) }}</td>
                        <td>KES {{ number_format($row->mobile_money, 2) }}</td>
                        <td>KES {{ number_format($row->card, 2) }}</td>
                        <td>KES {{ number_format($row->bank, 2) }}</td>
                        <td style="color: {{ $row->variance < 0 ? '#b91c1c' : ($row->variance > 0 ? '#15803d' : 'inherit') }}">
                            KES {{ number_format($row->variance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="empty-cell">No cashier activity in this period.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
