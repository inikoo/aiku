<?php

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class LunaClient extends Model
{
    use HasApiTokens;
}
