<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'barcode', 'buying_price', 'selling_price', 'stock_quantity', 'status'
    ];


    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function item(){
        return $this->hasMany(ProductItem::class, 'product_id');
    }
    public function stock(){
        return $this->hasOne(Stock::class, 'product_id');
    }
    public function saleItem(){
        return $this->hasMany(SaleItem::class, 'product_id');
    }
}
