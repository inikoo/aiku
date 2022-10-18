<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 18:13:18 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Central\TenantMarketingStats
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $number_shops
 * @property int $number_shops_type_shop
 * @property int $number_shops_type_fulfilment_house
 * @property int $number_shops_type_agent
 * @property int $number_shops_subtype_b2b
 * @property int $number_shops_subtype_b2c
 * @property int $number_shops_subtype_storage
 * @property int $number_shops_subtype_fulfilment
 * @property int $number_shops_subtype_dropshipping
 * @property int $number_orphan_families
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 * @method static Builder|TenantMarketingStats newModelQuery()
 * @method static Builder|TenantMarketingStats newQuery()
 * @method static Builder|TenantMarketingStats query()
 * @method static Builder|TenantMarketingStats whereCreatedAt($value)
 * @method static Builder|TenantMarketingStats whereId($value)
 * @method static Builder|TenantMarketingStats whereNumberOrphanFamilies($value)
 * @method static Builder|TenantMarketingStats whereNumberShops($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsSubtypeB2b($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsSubtypeB2c($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsSubtypeDropshipping($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsSubtypeFulfilment($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsSubtypeStorage($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsTypeAgent($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsTypeFulfilmentHouse($value)
 * @method static Builder|TenantMarketingStats whereNumberShopsTypeShop($value)
 * @method static Builder|TenantMarketingStats whereTenantId($value)
 * @method static Builder|TenantMarketingStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TenantMarketingStats extends Model
{
    protected $table = 'tenant_marketing_stats';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
