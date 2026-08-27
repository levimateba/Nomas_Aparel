@extends('layouts.admin')
@section('title', $stockTake->reference)
@section('heading', $stockTake->reference)
@section('subheading', ($stockTake->stocktake_date?->format('d M Y') ?: $stockTake->created_at->format('d M Y')) . ' · ' . ucwords(str_replace('_', ' ', $stockTake->status)) . ' · ' . ($stockTake->user?->name ?: '—') . ($stockTake->completedByUser ? ' · Approved by ' . $stockTake->completedByUser->name : ''))

@section('content')
    @php $netValue = (float) $stockTake->positive_variance_value + (float) $stockTake->negative_variance_value; @endphp

    <div class="row-actions" style="margin-bottom:16px;">
        <a class="btn btn-secondary" href="{{ route('admin.stock-takes.index') }}">Back</a>
        @if($stockTake->status === 'draft')
            <form method="POST" action="{{ route('admin.stock-takes.start', $stockTake) }}">
                @csrf
                <button class="btn" type="submit">Start counting</button>
            </form>
        @endif
        @if($stockTake->status === 'counting')
            <form method="POST" action="{{ route('admin.stock-takes.review', $stockTake) }}">
                @csrf
                <button class="btn btn-secondary" type="submit">Send for review</button>
            </form>
        @endif
        @if($stockTake->canApprove())
            <form method="POST" action="{{ route('admin.stock-takes.approve', $stockTake) }}" onsubmit="return confirm('Approve this stocktake? Inventory will be adjusted.');">
                @csrf
                <button class="btn" type="submit">Approve &amp; apply</button>
            </form>
        @endif
        @if($stockTake->isEditable())
            <form method="POST" action="{{ route('admin.stock-takes.cancel', $stockTake) }}" onsubmit="return confirm('Cancel this stock take without changing stock?');">
                @csrf
                <button class="btn btn-secondary" type="submit">Cancel</button>
            </form>
        @endif
    </div>

    @if($stockTake->notes)
        <p class="muted" style="margin-top:-8px;">{{ $stockTake->notes }}</p>
    @endif

    <div class="admin-kpis">
        <div class="admin-kpi"><span>Items</span><strong>{{ number_format($items->count()) }}</strong></div>
        <div class="admin-kpi"><span>Positive variance</span><strong style="color:#15803d;">+{{ number_format($positiveQty) }}</strong><small>KES {{ number_format((float) $stockTake->positive_variance_value, 2) }}</small></div>
        <div class="admin-kpi"><span>Negative variance</span><strong style="color:#b91c1c;">−{{ number_format(abs($negativeQty)) }}</strong><small>KES {{ number_format(abs((float) $stockTake->negative_variance_value), 2) }}</small></div>
        <div class="admin-kpi"><span>Net value impact</span><strong style="color: {{ $netValue >= 0 ? '#15803d' : '#b91c1c' }};">KES {{ number_format($netValue, 2) }}</strong></div>
    </div>

    @if($stockTake->isEditable())
        <form class="admin-toolbar" method="GET" action="{{ route('admin.stock-takes.show', $stockTake) }}">
            <div class="admin-filters">
                <div class="field">
                    <label>Scan barcode / SKU</label>
                    <input type="text" name="q" placeholder="Scan to count +1" autofocus autocomplete="off">
                </div>
                <button class="btn" type="submit">Count</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.stock-takes.update', $stockTake) }}" class="card">
            @csrf
            @method('PUT')
            <div class="row-actions" style="margin-bottom:12px; justify-content:space-between;">
                <h3 style="margin:0;">Physical counts</h3>
                <button class="btn" type="submit">Save counts</button>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>System qty</th>
                            <th>Physical qty</th>
                            <th>Variance</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            <tr style="{{ $item->isCounted() && $item->variance !== 0 ? ($item->variance > 0 ? 'background:#f0fdf4;' : 'background:#fef2f2;') : '' }}">
                                <td><strong>{{ $item->product_name }}</strong></td>
                                <td class="muted">{{ $item->sku ?: $item->barcode ?: '—' }}</td>
                                <td>{{ number_format($item->system_qty) }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <input type="number" min="0" name="items[{{ $index }}][counted_qty]" value="{{ old("items.$index.counted_qty", $item->counted_qty) }}" style="width:90px;margin:0;">
                                </td>
                                <td>
                                    @if($item->isCounted())
                                        <strong style="color: {{ $item->variance > 0 ? '#15803d' : ($item->variance < 0 ? '#b91c1c' : 'inherit') }};">
                                            {{ $item->variance > 0 ? '+' : '' }}{{ number_format($item->variance) }}
                                        </strong>
                                        <br><span class="muted">KES {{ number_format((float) $item->variance_value, 2) }}</span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][reason]" value="{{ old("items.$index.reason", $item->reason) }}" placeholder="Optional" style="margin:0;min-width:8rem;">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    @else
        <div class="card">
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>System</th>
                            <th>Physical</th>
                            <th>Variance</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->product_name }} <span class="muted">({{ $item->sku ?: $item->barcode ?: '—' }})</span></td>
                                <td>{{ number_format($item->system_qty) }}</td>
                                <td>{{ $item->isCounted() ? number_format($item->counted_qty) : '—' }}</td>
                                <td>
                                    @if($item->isCounted())
                                        <strong style="color: {{ $item->variance > 0 ? '#15803d' : ($item->variance < 0 ? '#b91c1c' : 'inherit') }};">
                                            {{ $item->variance > 0 ? '+' : '' }}{{ number_format($item->variance) }}
                                        </strong>
                                    @else — @endif
                                </td>
                                <td>{{ $item->reason ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
