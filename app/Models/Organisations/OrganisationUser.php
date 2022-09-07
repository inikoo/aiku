<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 20 Aug 2022 13:07:59 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Organisations;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|OrganisationUser newModelQuery()
 * @method static Builder|OrganisationUser newQuery()
 * @method static Builder|OrganisationUser query()
 * @method static Builder|OrganisationUser whereCreatedAt($value)
 * @method static Builder|OrganisationUser whereId($value)
 * @method static Builder|OrganisationUser whereOrganisationId($value)
 * @method static Builder|OrganisationUser whereUpdatedAt($value)
 * @method static Builder|OrganisationUser whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Organisations\Organisation|null $organisation
 * @property-read \App\Models\SysAdmin\User|null $user
 */
class OrganisationUser extends Pivot
{
    public $incrementing = true;


    public static function boot()
    {
        parent::boot();

        static::created(function ($item) {
            $item->organisation->update(
                [
                    'number_users' => $item->organisation->users->count()
                ]
            );

            $numberOrganisations= $item->user->organisations->count();

            $item->user->number_organisations=$numberOrganisations;
            if($numberOrganisations==1){
                $item->user->organisation_id=$item->organisation->id;
            }
            $item->user->save();

        });

        static::deleted(function ($item) {
            $item->organisation->update(
                [
                    'number_users' => $item->organisation->users->count()
                ]
            );

            $numberOrganisations= $item->user->organisations->count();

            $item->user->number_organisations=$numberOrganisations;

            if($item->user->organisation_id==$item->organisation->id){

                if($currentOrganisation=$item->user->organisation_id=$item->user->organisations()->first()){
                    $item->user->organisation_id=$currentOrganisation->id;

                }

            }
            $item->user->save();

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
