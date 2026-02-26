<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanRelationType extends Model {    
    protected $primaryKey = 'relation_type_id';   
    protected $fillable = [
        'relation_type_name',
    ];
}
