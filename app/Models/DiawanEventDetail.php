<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiawanEventDetail extends Model {
    use SoftDeletes;
    protected $primaryKey = 'event_detail_id';   
    protected $fillable = [
        'event_detail_event_id',
        'event_detail_item',
        'event_detail_amount',
        'event_detail_price',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
