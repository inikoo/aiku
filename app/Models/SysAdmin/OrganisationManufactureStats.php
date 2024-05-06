<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_productions
 * @property int $number_productions_state_in_process
 * @property int $number_productions_state_open
 * @property int $number_productions_state_closing_down
 * @property int $number_productions_state_closed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationManufactureStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationManufactureStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrganisationManufactureStats query()
 * @mixin \Eloquent
 */
class OrganisationManufactureStats extends Model
{
    protected $table = 'organisation_manufacture_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
