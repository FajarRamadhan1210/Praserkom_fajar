<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function orderDetails()
    {
        return $this->hasMany(order_details::class, 'order_id');
    }
}