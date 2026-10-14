<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order_details extends Model
{
    use HasFactory;
    
    protected $table = 'order_details';
    protected $guarded = ['id'];

    public function event()
    {
        return $this->belongsTo(events::class, 'event_id');
    }

    // Relasi ke Order induk
    public function order()
    {
        return $this->belongsTo(orders::class, 'order_id');
    }
}