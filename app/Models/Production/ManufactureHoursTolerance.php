<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 16:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Production;

use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * How far an artisan's recorded day may be from their shift before it is flagged, dated so a change applies from a day on.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $production_id
 * @property int $tolerance_minutes
 * @property \Illuminate\Support\Carbon $effective_from
 * @property int|null $set_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $setBy
 * @mixin \Eloquent
 */
class ManufactureHoursTolerance extends Model
{
    public const int DEFAULT_MINUTES = 30;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
        ];
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by_user_id');
    }

    public static function minutesOn(int $productionId, string $date): int
    {
        return self::where('production_id', $productionId)
            ->where('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->value('tolerance_minutes') ?? self::DEFAULT_MINUTES;
    }
}
