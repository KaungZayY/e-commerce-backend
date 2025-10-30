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
        'is_popular' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function scopeFilter($query, $filters)
    {
        // Product name search
        if (!empty($filters['product_name'])) {
            $query->where('product_name', 'like', '%' . $filters['product_name'] . '%');
        }

        // Price range filter
        if (!empty($filters['price_range'])) {
            $start = (float) ($filters['price_range']['start'] ?? 0);
            $end = $filters['price_range']['end'] ?? null;

            $query->when(
                $end !== null && $end !== '',
                fn($q) => $q->whereBetween('price', [$start, (float) $end]),
                fn($q) => $q->where('price', '>=', $start)
            );
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Popular filter
        if (isset($filters['is_popular'])) {
            $query->where('is_popular', $filters['is_popular']);
        }

        // Stock status filters
        if (!empty($filters['on_sale'])) {
            $query->where(function ($q) {
                $q->whereNotNull('discount_amount')
                    ->where('discount_amount', '>', 0);
            });
        }

        if (!empty($filters['in_stock'])) {
            $query->where('qty', '>', 0);
        }

        if (!empty($filters['on_backorder'])) {
            $query->where('qty', '=', 0);
        }

        return $query;
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
