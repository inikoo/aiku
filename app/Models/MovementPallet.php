<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementPallet extends Model
{
    protected $guarded = [];
protected $casts   = [
        'moved_at'   => 'datetime'
    ];
}
