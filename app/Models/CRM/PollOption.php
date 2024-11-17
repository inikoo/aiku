<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 15:00:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property int $poll_id
 * @property string $slug
 * @property string $value
 * @property string $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\CRM\Poll $poll
 * @property-read \App\Models\CRM\PollOptionStat|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PollOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PollOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PollOption query()
 * @mixin \Eloquent
 */
class PollOption extends Model
{
    protected $casts = [
    ];

    protected $attributes = [
    ];

    protected $guarded = [];

    public function stats(): HasOne
    {
        return $this->hasOne(PollOptionStat::class);
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }
}
