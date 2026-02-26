<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanInputSource extends Model {  
    protected $primaryKey = 'input_source_id';   
    protected $fillable = [
        'input_source_name',
    ];
}
