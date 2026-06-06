<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['purchase_no', 'supplier_id', 'purchase_date',
                            'created_by', 'total_amount', 'status'];
    
    public function supplier(){
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function item(){
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

}
