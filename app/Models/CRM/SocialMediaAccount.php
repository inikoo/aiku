<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Enums\CRM\SocialMedia\SocialMediaPlatformEnum;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property SocialMediaPlatformEnum $platform
 * @property string|null $external_id
 * @property string $name
 * @property string|null $username
 * @property int|null $followers
 * @property \Illuminate\Support\Carbon|null $followers_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SocialMediaPost> $posts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SocialMediaAccountDailyStat> $dailyStats
 * @mixin \Eloquent
 */
class SocialMediaAccount extends Model
{
    use InShop;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'platform'     => SocialMediaPlatformEnum::class,
            'followers_at' => 'date',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(SocialMediaAccountDailyStat::class);
    }
}
