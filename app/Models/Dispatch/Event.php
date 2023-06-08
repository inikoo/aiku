<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use App\Models\Helpers\Issue;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 *
 */

/**
 * App\Models\Dispatch\Event
 *
 * @property-read Collection<int, Issue> $issues
 * @property-read \App\Models\Dispatch\Shipment $shipment
 * @method static Builder|Event newModelQuery()
 * @method static Builder|Event newQuery()
 * @method static Builder|Event onlyTrashed()
 * @method static Builder|Event query()
 * @method static Builder|Event withTrashed()
 * @method static Builder|Event withoutTrashed()
 * @mixin Eloquent
 */
class Event extends Model
{
    use SoftDeletes;
    use UsesTenantConnection;
    use HasFactory;

    protected $casts = [
        'data'   => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::created(
            function (event $event) {
                $event->shipment->update_state();
            }
        );
    }
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function issues(): MorphToMany
    {
        return $this->morphToMany(Issue::class, 'issuable');
    }
}
