<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiawanHuman extends Model {
    use HasUuids;
    use SoftDeletes;
    protected $primaryKey = 'human_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'human_first_name',
        'human_last_name',
        'human_ktp',
        'human_birth_date',
        'human_phone_number',
        'human_email',
    ];
}
