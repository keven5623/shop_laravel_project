<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderShipped;
use App\Models\Order;
use App\Events\OrderProcessed;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle()
    {
        $order = Order::with('items.product', 'user')->find($this->orderId);
        if (!$order) return;

        // 1️⃣ 更新庫存
        foreach ($order->items as $item) {
            $product = $item->product;
            $product->stock = max($product->stock - $item->quantity, 0);
            $product->save();
        }

        // 2️⃣ 發送 Email
        // Mail::to($order->user->email)->send(new OrderShipped($order));

        try {
            Log::info('寄信開始', ['to' => $order->user->email]);
            Mail::to($order->user->email)->send(new OrderShipped($order));
            Log::info('寄信結束');
        } catch (\Throwable $e) {
            Log::error('寄信失敗', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        // 3️⃣ 推送前端通知
        broadcast(new OrderProcessed($order));
    }
}
