<?php

namespace App\Models\Backup;

use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Backup\VisitHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|VisitHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VisitHistory query()
 * @mixin Eloquent
 */

class VisitHistory extends Model
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
