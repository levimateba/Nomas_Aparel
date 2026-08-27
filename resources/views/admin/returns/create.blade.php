@extends('layouts.admin')
@section('title', 'Process Return')
@section('heading', 'Process Return')
@section('subheading', 'Look up a POS receipt and restore stock.')

@section('content')
    <div class="card" style="margin-bottom:16px;">
        <form method="GET" action="{{ route('admin.returns.create') }}" class="admin-filters">
            <div class="field" style="flex:1;">
                <label>Receipt number</label>
                <input name="receipt" value="{{ request('receipt') }}" placeholder="POS-20260818-...">
            </div>
            <button class="btn" type="submit">Find sale</button>
        </form>
    </div>

    @if($order)
        <div class="card">
            <p><strong>{{ $order->order_number }}</strong></p>
            <p class="muted">{{ $order->customer_name }} · {{ $order->created_at?->format('d M Y H:i') }} · KES {{ number_format((float) $order->total_amount, 2) }}</p>

            @unless($order->isReturnable())
                <p class="muted">This sale cannot be returned.</p>
            @else
                <form method="POST" action="{{ route('admin.returns.store') }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <label>Refund method</label>
                    <select name="refund_method" required>
                        <option value="original">Original payment</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">M-Pesa</option>
                    </select>
                    <label>Reason</label>
                    <textarea name="reason" rows="3" required>{{ old('reason') }}</textarea>
                    <div class="table-wrap">
                        <table class="admin-table">
                            <thead><tr><th>Product</th><th>Sold</th><th>Already returned</th><th>Return qty</th></tr></thead>
                            <tbody>
                            @foreach($order->items as $index => $item)
                                <tr>
                                    <td>
                                        {{ $item->product_name }}
                                        <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->returned_quantity }}</td>
                                    <td>
                                        <input type="number" min="0" max="{{ $item->returnableQuantity() }}" name="items[{{ $index }}][quantity]" value="0" {{ $item->returnableQuantity() <= 0 ? 'disabled' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="submit">Process return</button>
                </form>
            @endunless
        </div>
    @endif
@endsection
