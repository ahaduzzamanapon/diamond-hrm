<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = ['requisition_id', 'product_id', 'qty_requested', 'qty_supplied'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
