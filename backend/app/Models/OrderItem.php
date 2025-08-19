<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
   protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    // 關聯到 Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 如果你想要直接取商品資訊
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
