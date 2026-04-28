<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'inventory_category_id', 'inventory_unit_id',
        'current_stock', 'alert_quantity', 'description', 'image', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function unit()
    {
        return $this->belongsTo(InventoryUnit::class, 'inventory_unit_id');
    }
}
