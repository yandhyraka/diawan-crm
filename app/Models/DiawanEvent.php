<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanEvent extends Model {    
    public $softDelete = true;
    protected $primaryKey = 'event_type_id';   
    protected $fillable = [
        'event_human_uuid',
        'event_event_type',
        'event_place_id',
    ];
}
