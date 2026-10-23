<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $social_media_account_id
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $followers
 * @property int|null $new_follows
 * @property int|null $reach
 * @property int|null $impressions
 * @property int|null $engagements
 * @property int|null $profile_visits
 * @property int|null $link_clicks
 * @property int|null $ad_impressions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read SocialMediaAccount $socialMediaAccount
 * @mixin \Eloquent
 */
class SocialMediaAccountDailyStat extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function socialMediaAccount(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class);
    }
}
