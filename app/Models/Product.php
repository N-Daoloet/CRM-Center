<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'price',
        'real_price',
        'sale_price',
        'stock',
        'is_active',
        'low_stock_threshold',
        'description',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'real_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    
    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class)
            ->withPivot('special_price')
            ->withTimestamps();
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }
}
