<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'generated_id',
        'first_name',
        'last_name',
        'customer_email',
        'customer_phone',
        'city',
        'state',
        'postal_code',
        'customer_address',
        'note',
        'total_amount',
        'status',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('qty', 'final_unit_price', 'subtotal')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
