<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Nov 2023 12:38:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Enums\Helpers\Fetch\FetchTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Helpers\Fetch
 *
 * @property int $id
 * @property FetchTypeEnum $type
 * @property int $number_items
 * @property int $number_no_changes
 * @property int $number_updates
 * @property int $number_stores
 * @property int $number_errors
 * @property string|null $finished_at
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\FetchRecord> $records
 * @method static \Illuminate\Database\Eloquent\Builder|Fetch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fetch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fetch query()
 * @mixin \Eloquent
 */
class Fetch extends Model
{
    protected $casts = [
        'data'     => 'array',
        'type'     => FetchTypeEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded = [];

    public function records(): HasMany
    {
        return $this->hasMany(FetchRecord::class);
    }

}
