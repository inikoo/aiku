<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Grouping;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Grouping\OrganisationMarketStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_shops
 * @property int $number_shops_state_in_process
 * @property int $number_shops_state_open
 * @property int $number_shops_state_closing_down
 * @property int $number_shops_state_closed
 * @property int $number_shops_type_shop
 * @property int $number_shops_type_fulfilment_house
 * @property int $number_shops_type_agent
 * @property int $number_shops_subtype_b2b
 * @property int $number_shops_subtype_b2c
 * @property int $number_shops_subtype_fulfilment
 * @property int $number_shops_subtype_dropshipping
 * @property int $number_shops_state_subtype_in_process_b2b
 * @property int $number_shops_state_subtype_in_process_b2c
 * @property int $number_shops_state_subtype_in_process_fulfilment
 * @property int $number_shops_state_subtype_in_process_dropshipping
 * @property int $number_shops_state_subtype_open_b2b
 * @property int $number_shops_state_subtype_open_b2c
 * @property int $number_shops_state_subtype_open_fulfilment
 * @property int $number_shops_state_subtype_open_dropshipping
 * @property int $number_shops_state_subtype_closing_down_b2b
 * @property int $number_shops_state_subtype_closing_down_b2c
 * @property int $number_shops_state_subtype_closing_down_fulfilment
 * @property int $number_shops_state_subtype_closing_down_dropshipping
 * @property int $number_shops_state_subtype_closed_b2b
 * @property int $number_shops_state_subtype_closed_b2c
 * @property int $number_shops_state_subtype_closed_fulfilment
 * @property int $number_shops_state_subtype_closed_dropshipping
 * @property int $number_departments
 * @property int $number_departments_state_in_process
 * @property int $number_departments_state_active
 * @property int $number_departments_state_discontinuing
 * @property int $number_departments_state_discontinued
 * @property int $number_families
 * @property int $number_families_state_in_process
 * @property int $number_families_state_active
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_orphan_families
 * @property int $number_products
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Grouping\Organisation $organisation
 * @method static Builder|OrganisationMarketStats newModelQuery()
 * @method static Builder|OrganisationMarketStats newQuery()
 * @method static Builder|OrganisationMarketStats query()
 * @mixin Eloquent
 */
class OrganisationMarketStats extends Model
{
    protected $table = 'organisation_market_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
