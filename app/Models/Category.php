<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'descriptions'];

    public function product(){
        return $this->hasMany(Product::class, 'category_id');
    }
}
