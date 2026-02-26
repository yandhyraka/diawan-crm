<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiawanPlace extends Model {    
    protected $primaryKey = 'place_id';   
    protected $fillable = [
        'place_name',
    ];
}
