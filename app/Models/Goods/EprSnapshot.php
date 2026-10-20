<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Goods;

use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Models\SysAdmin\User;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A packaging EPR return frozen for audit: the figures as they were produced for a scheme and period, and in its lines
 * every SKO moved with the component weights, polymers and RAM evidence used. Kept seven years; never edited.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property EprSchemeEnum $scheme
 * @property Carbon $date_from
 * @property Carbon $date_to
 * @property string $kg
 * @property array<string, mixed> $data
 * @property string|null $notes
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @mixin \Eloquent
 */
class EprSnapshot extends Model
{
    use InOrganisation;

    protected $casts = [
        'scheme'    => EprSchemeEnum::class,
        'date_from' => 'date',
        'date_to'   => 'date',
        'data'      => 'array',
    ];

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(EprSnapshotLine::class);
    }
}
