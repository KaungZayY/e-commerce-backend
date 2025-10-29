<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_name',
        'images',
        'price',
        'qty',
        'description',
        'is_popular',
        'discount_type',
        'discount_amount',
        'created_by',
    ];

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['product_name'])) {
            $query->where('product_name', 'like', '%' . $filters['product_name'] . '%');
        }
        if (!empty($filters['price_range']['start']) && !empty($filters['price_range']['end'])) {
            $start = floatval($filters['price_range']['start']);
            $end = floatval($filters['price_range']['end']);

            $query->whereBetween('price', [$start, $end]);
        }
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
