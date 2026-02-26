<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanHumanLog extends Model {   
    protected $primaryKey = 'human_log_id'; 
    protected $fillable = [
        'human_uuid',
        'human_log_type',
        'human_log_before',
        'human_log_after',
    ];
}
