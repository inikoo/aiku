<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 20 Aug 2022 13:07:59 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Organisations;

use App\Actions\Hydrators\HydrateOrganisation;
use App\Actions\Hydrators\HydrateUser;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;



/**
 * App\Models\Organisations\OrganisationUser
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $organisation_id
 * @property string|null $userable_type
 * @property int|null $userable_id
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organisations\Organisation|null $organisation
 * @property-read User $user
 * @method static Builder|OrganisationUser newModelQuery()
 * @method static Builder|OrganisationUser newQuery()
 * @method static Builder|OrganisationUser query()
 * @method static Builder|OrganisationUser whereCreatedAt($value)
 * @method static Builder|OrganisationUser whereId($value)
 * @method static Builder|OrganisationUser whereOrganisationId($value)
 * @method static Builder|OrganisationUser whereStatus($value)
 * @method static Builder|OrganisationUser whereUpdatedAt($value)
 * @method static Builder|OrganisationUser whereUserId($value)
 * @method static Builder|OrganisationUser whereUserableId($value)
 * @method static Builder|OrganisationUser whereUserableType($value)
 * @mixin \Eloquent
 */
class OrganisationUser extends Pivot
{
    public $incrementing = true;


    public static function boot()
    {
        parent::boot();

        static::created(function ($item) {
            HydrateOrganisation::make()->userStats($item->organisation);
            HydrateUser::make()->organisationStats($item->user);
        });

        static::deleted(function ($item) {
            HydrateOrganisation::make()->userStats($item->organisation);
            HydrateUser::make()->organisationStats($item->user);
        });

        static::updated(function ($item) {
            if(!$item->wasRecentlyCreated) {
                if ($item->wasChanged('status')) {
                    HydrateOrganisation::make()->userStats($item->organisation);
                    HydrateUser::make()->organisationStats($item->user);
                }
            }

        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

}
