<h2>Thank you for your order, {{ $order->customer_name }}</h2>
<p>Your order <strong>{{ $order->order_number }}</strong> has been received.</p>
<p>Payment method: <strong>{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</strong></p>
<p>Total: <strong>KES {{ number_format((float) $order->total_amount, 2) }}</strong></p>

<h3>Items</h3>
<ul>
    @foreach($order->items as $item)
        <li>{{ $item->product_name }} x{{ $item->quantity }} - KES {{ number_format((float) $item->line_total, 2) }}</li>
    @endforeach
</ul>

<p>We will contact you with shipping updates soon.</p>
