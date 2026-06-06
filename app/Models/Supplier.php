<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function purchase(){
        return $this->hasMany(Purchase::class, 'supplier_id');
    }
}
