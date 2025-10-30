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
        'moq',
        'category_id',
    ];

    protected $casts = [
        'images' => 'array',
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
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
