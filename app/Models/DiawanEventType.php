<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanEventType extends Model {    
    protected $primaryKey = 'event_type_id';   
    protected $fillable = [
        'event_type_name',
    ];
}
