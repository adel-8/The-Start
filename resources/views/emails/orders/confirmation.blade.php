<x-mail::message>
# {{ __('messages.order_thank_you') }}

{{ __('messages.order_placed_successfully') }}

**{{ __('messages.order_number') }}:** {{ $order->order_number }}  
**{{ __('messages.total') }}:** ${{ number_format($order->total_price, 2) }}  
**{{ __('messages.payment_method') }}:** {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}

<x-mail::button :url="route('orders.show', $order->id)">
{{ __('messages.view_order') }}
</x-mail::button>

{{ __('messages.order_shipping_notify') }}

{{ __('messages.thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>