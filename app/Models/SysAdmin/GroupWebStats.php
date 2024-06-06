<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jun 2024 15:10:51 Central European Summer Time, Plane Malaga - Abu Dhabi
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property int $group_id
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
 * @property int $number_websites_engine_aiku
 * @property int $number_websites_engine_other
 * @property int $number_webpages
 * @property int $number_webpages_state_in_process
 * @property int $number_webpages_state_ready
 * @property int $number_webpages_state_live
 * @property int $number_webpages_state_closed
 * @property int $number_webpages_type_storefront
 * @property int $number_webpages_type_shop
 * @property int $number_webpages_type_checkout
 * @property int $number_webpages_type_content
 * @property int $number_webpages_type_small_print
 * @property int $number_webpages_type_engagement
 * @property int $number_webpages_type_auth
 * @property int $number_webpages_type_blog
 * @property int $number_webpages_purpose_storefront
 * @property int $number_webpages_purpose_product_overview
 * @property int $number_webpages_purpose_product_list
 * @property int $number_webpages_purpose_category_preview
 * @property int $number_webpages_purpose_shopping_cart
 * @property int $number_webpages_purpose_info
 * @property int $number_webpages_purpose_privacy
 * @property int $number_webpages_purpose_cookies_policy
 * @property int $number_webpages_purpose_terms_and_conditions
 * @property int $number_webpages_purpose_appointment
 * @property int $number_webpages_purpose_contact
 * @property int $number_webpages_purpose_login
 * @property int $number_webpages_purpose_register
 * @property int $number_webpages_purpose_blog
 * @property int $number_webpages_purpose_article
 * @property int $number_webpages_purpose_content
 * @property int $number_webpages_purpose_other_small_print
 * @property int $number_webpages_purpose_shop
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @method static Builder|GroupWebStats newModelQuery()
 * @method static Builder|GroupWebStats newQuery()
 * @method static Builder|GroupWebStats query()
 * @mixin Eloquent
 */
class GroupWebStats extends Model
{
    protected $table = 'group_web_stats';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
