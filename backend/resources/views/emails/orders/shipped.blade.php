@component('mail::message')
# 訂單完成通知

訂單編號: **{{ $order->id }}**  
狀態: {{ $order->status }}

@component('mail::table')
| 商品 | 數量 | 小計 |
|------|------|------|
@foreach($order->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ $item->product->price * $item->quantity }} |
@endforeach
@endcomponent

總金額: ${{ $order->total }}

謝謝你使用我們的商店！
@endcomponent
