<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 01:41:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Catalogue\ShopStats
 *
 * @property int $id
 * @property int $shop_id
 * @property int $number_departments
 * @property int $number_current_departments
 * @property int $number_departments_state_in_process
 * @property int $number_departments_state_active
 * @property int $number_departments_state_discontinuing
 * @property int $number_departments_state_discontinued
 * @property int $number_collection_categories
 * @property int $number_collections
 * @property int $number_sub_departments
 * @property int $number_current_sub_departments state: active+discontinuing
 * @property int $number_sub_departments_state_in_process
 * @property int $number_sub_departments_state_active
 * @property int $number_sub_departments_state_discontinuing
 * @property int $number_sub_departments_state_discontinued
 * @property int $number_families
 * @property int $number_current_families state: active+discontinuing
 * @property int $number_families_state_in_process
 * @property int $number_families_state_active
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_orphan_families
 * @property int $number_assets
 * @property int $number_current_assets state: active+discontinuing
 * @property int $number_historic_assets
 * @property int $number_assets_state_in_process
 * @property int $number_assets_state_active
 * @property int $number_assets_state_discontinuing
 * @property int $number_assets_state_discontinued
 * @property int $number_assets_type_product
 * @property int $number_assets_type_service
 * @property int $number_assets_type_subscription
 * @property int $number_assets_type_rental
 * @property int $number_assets_type_charge
 * @property int $number_assets_type_shipping_zone
 * @property int $number_products
 * @property int $number_current_products state: active+discontinuing
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_rentals
 * @property int $number_rentals_state_in_process
 * @property int $number_rentals_state_active
 * @property int $number_rentals_state_discontinued
 * @property int $number_services
 * @property int $number_services_state_in_process
 * @property int $number_services_state_active
 * @property int $number_services_state_discontinued
 * @property int $number_subscriptions
 * @property int $number_subscriptions_state_in_process
 * @property int $number_subscriptions_state_active
 * @property int $number_subscriptions_state_discontinued
 * @property int $number_product_variants
 * @property int $number_charges
 * @property int $number_charges_state_in_process
 * @property int $number_charges_state_active
 * @property int $number_charges_state_discontinued
 * @property int $number_shipping_zone_schemas
 * @property int $number_shipping_zone_schemas_state_in_process
 * @property int $number_shipping_zone_schemas_state_live
 * @property int $number_shipping_zone_schemas_state_decommissioned
 * @property int $number_shipping_zones
 * @property int $number_adjustments
 * @property int $number_adjustments_type_error_net
 * @property int $number_adjustments_type_error_tax
 * @property int $number_adjustments_type_credit
 * @property int $number_uploads
 * @property int $number_upload_records
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Shop $shop
 * @method static Builder<static>|ShopStats newModelQuery()
 * @method static Builder<static>|ShopStats newQuery()
 * @method static Builder<static>|ShopStats query()
 * @mixin Eloquent
 */
class ShopStats extends Model
{
    protected $table = 'shop_stats';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
