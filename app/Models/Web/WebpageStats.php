<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 10:53:37 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Web\WebpageStats
 *
 * @property int $id
 * @property int $webpage_id
 * @property int $number_blocks
 * @property int $number_visible_blocks
 * @property int $number_published_blocks
 * @property int $number_snapshots
 * @property int $number_snapshots_state_unpublished
 * @property int $number_snapshots_state_live
 * @property int $number_snapshots_state_historic
 * @property int $number_deployments
 * @property string|null $last_deployed_at
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
 * @property-read \App\Models\Web\Webpage $webpage
 * @method static Builder|WebpageStats newModelQuery()
 * @method static Builder|WebpageStats newQuery()
 * @method static Builder|WebpageStats query()
 * @mixin Eloquent
 */
class WebpageStats extends Model
{
    protected $table = 'webpage_stats';

    protected $guarded = [];

    public function webpage(): BelongsTo
    {
        return $this->belongsTo(Webpage::class);
    }
}
