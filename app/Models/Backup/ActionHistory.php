<?php

namespace App\Models\Backup;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Backup\ActionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ActionHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActionHistory query()
 * @mixin Eloquent
 */

class ActionHistory extends Model
{
    protected $connection = 'backup';

    protected $casts = [
        'body'   => 'array',
    ];

    protected $attributes = [
        'body' => '{}',
    ];

    protected $guarded = [];
}
