<?php

namespace App\Models\Backup;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $type
 * @property string|null $index
 * @property array|null $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
