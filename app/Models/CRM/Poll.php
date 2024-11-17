<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 15:00:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use App\Enums\CRM\Poll\PollTypeEnum;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property string $slug
 * @property string $name
 * @property string $label
 * @property bool $in_registration
 * @property bool $in_registration_required
 * @property PollTypeEnum $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CRM\PollOption> $options
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
 * @property-read \App\Models\CRM\PollStat|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Poll query()
 * @mixin \Eloquent
 */
class Poll extends Model
{
    use InShop;

    protected $casts = [
        'type' => PollTypeEnum::class
    ];

    protected $attributes = [
    ];

    protected $guarded = [];

    public function stats(): HasOne
    {
        return $this->hasOne(PollStat::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }
}
