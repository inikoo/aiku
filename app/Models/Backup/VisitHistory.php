<?php

namespace App\Models\Backup;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Auth\VisitHistory
 *
 * @property int $id
 * @property string|null $type
 * @property string|null $index
 * @property array|null $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $parent
 * @method static Builder|VisitHistory newModelQuery()
 * @method static Builder|VisitHistory newQuery()
 * @method static Builder|VisitHistory onlyTrashed()
 * @method static Builder|VisitHistory permission($permissions)
 * @method static Builder|VisitHistory query()
 * @method static Builder|VisitHistory role($roles, $guard = null)
 * @method static Builder|VisitHistory withTrashed()
 * @method static Builder|VisitHistory withoutTrashed()
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
