<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['sale_no', 'customer_id', 'total_amount', 'payment_method', 'payment_status', 'created_by'];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function item(){
        return $this->hasMany(SaleItem::class, 'sale_id');
    }
}
