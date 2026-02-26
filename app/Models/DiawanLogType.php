<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanLogType extends Model {    
    protected $primaryKey = 'log_type_id';   
    protected $fillable = [
        'log_type_name',
    ];
}
