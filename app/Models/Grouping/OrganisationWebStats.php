<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Nov 2023 00:05:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Grouping;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organisation\OrganisationWebStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_websites
 * @property int $number_websites_under_maintenance
 * @property int $number_websites_type_info
 * @property int $number_websites_type_b2b
 * @property int $number_websites_type_b2c
 * @property int $number_websites_type_dropshipping
 * @property int $number_websites_type_fulfilment
 * @property int $number_websites_state_in_process
 * @property int $number_websites_state_live
 * @property int $number_websites_state_closed
 * @property int $number_websites_engine_aurora
 * @property int $number_websites_engine_iris
 * @property int $number_websites_engine_other
 * @property int $number_webpages
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Grouping\Organisation $organisation
 * @method static Builder|OrganisationWebStats newModelQuery()
 * @method static Builder|OrganisationWebStats newQuery()
 * @method static Builder|OrganisationWebStats query()
 * @mixin Eloquent
 */
class OrganisationWebStats extends Model
{
    protected $table = 'organisation_web_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
