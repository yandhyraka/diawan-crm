<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiawanEvent extends Model { 
    use SoftDeletes;
    protected $primaryKey = 'event_id';   
    protected $fillable = [
        'event_human_uuid',
        'event_event_type',
        'event_place_id',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
