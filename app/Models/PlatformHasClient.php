<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PlatformHasClient extends Pivot
{
    protected $table = 'platform_has_clients';

    protected $guarded = [];
}
