<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .order-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .order-items th, .order-items td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .totals {
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
        .button {
            display: inline-block;
            background-color: #645F7D;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="The Start" class="logo">
        <h1>Order Confirmation</h1>
    </div>

    <p>Dear {{ $order->user ? $order->user->name : $order->guest_name }},</p>
    <p>Thank you for your order! Your order has been received and is being processed.</p>

    <div class="order-details">
        <strong>Order Number:</strong> {{ $order->order_number }}<br>
        <strong>Order Date:</strong> {{ $order->created_at->format('F j, Y, g:i a') }}<br>
        <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}<br>
        <strong>Order Status:</strong> {{ ucfirst($order->status) }}<br>
        <strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}
    </div>

    <h3>Order Items</h3>
    <table class="order-items">
        <thead>
            <tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price_at_purchase, 2) }} DZD</td>
                <td>{{ number_format($item->price_at_purchase * $item->quantity, 2) }} DZD</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p><strong>Subtotal:</strong> {{ number_format($order->total_price - $order->shipping_cost, 2) }} DZD</p>
        @if($order->coupon)
            <p><strong>Coupon Discount:</strong> -{{ number_format($order->coupon->discount_value ?? 0, 2) }} DZD</p>
        @endif
        <p><strong>Shipping:</strong> {{ number_format($order->shipping_cost, 2) }} DZD</p>
        <p><strong>Total:</strong> {{ number_format($order->total_price, 2) }} DZD</p>
    </div>

    <p>If you have any questions, please contact us at {{ $settings['contact_email'] ?? 'support@thestart.com' }}.</p>

    <div style="text-align: center;">
        <a href="{{ route('orders.show', $order->order_number) }}" class="button">View Your Order</a>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} The Start. All rights reserved.<br>
        {{ $settings['site_address'] ?? '123 Algiers Street, Algiers, Algeria' }}
    </div>
</body>
</html>