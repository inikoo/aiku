<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use App\Models\Traits\UsesGroupConnection;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Tenancy\TenantMarketingStats
 *
 * @property int $id
 * @property int $tenant_id
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
 * @property int $number_orphan_families
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Tenancy\Tenant $tenant
 * @method static Builder|TenantMarketingStats newModelQuery()
 * @method static Builder|TenantMarketingStats newQuery()
 * @method static Builder|TenantMarketingStats query()
 * @mixin Eloquent
 */
class TenantMarketingStats extends Model
{
    use UsesGroupConnection;

    protected $table = 'tenant_marketing_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
