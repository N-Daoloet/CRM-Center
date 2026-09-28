<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'shipping_address',
        'shipping_province_id',
        'shipping_district_id',
        'shipping_sub_district_id',
        'shipping_zipcode',
        'shipping_address_line',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'subtotal',
        'discount_amount',
        'vat_rate',
        'vat_amount',
        'sale_date',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
