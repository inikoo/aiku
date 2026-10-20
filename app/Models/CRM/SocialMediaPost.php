<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Enums\CRM\SocialMedia\SocialMediaPostTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $social_media_account_id
 * @property string $external_id
 * @property SocialMediaPostTypeEnum $type
 * @property string|null $description
 * @property string|null $permalink
 * @property \Illuminate\Support\Carbon $published_at
 * @property int $reach
 * @property int $impressions
 * @property int $reactions
 * @property int $comments
 * @property int $shares
 * @property int $saves
 * @property int $link_clicks
 * @property int $follows
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read SocialMediaAccount $socialMediaAccount
 * @mixin \Eloquent
 */
class SocialMediaPost extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type'         => SocialMediaPostTypeEnum::class,
            'published_at' => 'datetime',
        ];
    }

    public function socialMediaAccount(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class);
    }
}
