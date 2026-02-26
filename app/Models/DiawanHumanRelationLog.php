<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanHumanRelationLog extends Model {   
    protected $primaryKey = 'human_relation_log_id'; 
    protected $fillable = [
        'human_relation_log_human_relation_id',
        'human_relation_log_log_type',
        'human_relation_log_before',
        'human_relation_log_after',
        'human_relation_log_input_source',
    ];
}
