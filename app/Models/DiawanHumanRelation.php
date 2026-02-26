<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanHumanRelation extends Model {   
    protected $primaryKey = 'human_relation_id'; 
    protected $fillable = [
        'human_relation_human_uuid1',
        'human_relation_human_uuid2',
        'human_relation_relation_type',
        'human_relation_data',
    ];
}
