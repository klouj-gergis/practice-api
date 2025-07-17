<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\OrderItem;


class Order extends Model
{
    //
    protected $fillable = [
        'user_id',
        'status',
        'shipping_name',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zipcode',
        'shipping_country',
        'shipping_phone',
        'subtotal',
        'tax',
        'shipping_cost',
        'payment_method',
        'payment_status',
        'order_number',
        'notes'
    ];

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function itmes(): HasMany
    {
        return $this->HasMany(OrderItem::class);
    }

    public static function generateOrderNumber()
    {
        $year = date('Y');
        $randomNumber = strtoupper(substr(uniqid(), -6));

        return "ORD-{$year}-{$randomNumber}";
    }
}
