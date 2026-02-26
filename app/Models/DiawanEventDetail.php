<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanEventDetail extends Model {    
    public $softDelete = true;
    protected $primaryKey = 'event_detail_id';   
    protected $fillable = [
        'event_detail_event_id',
        'event_detail_item',
        'event_detail_amount',
        'event_detail_price',
    ];
}
