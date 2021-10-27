<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function product_galeries()
    {
        return $this->hasMany(ProductGalery::class, 'products_id', 'id');
    }

    public function product_category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_categories_id', 'id');
    }
}
