<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 12:56:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Actions\Central\Tenant\HydrateTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;



/**
 * App\Models\SysAdmin\Guest
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $gender
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\SysAdmin\User|null $user
 * @method static Builder|Guest newModelQuery()
 * @method static Builder|Guest newQuery()
 * @method static Builder|Guest query()
 * @method static Builder|Guest whereCreatedAt($value)
 * @method static Builder|Guest whereData($value)
 * @method static Builder|Guest whereDateOfBirth($value)
 * @method static Builder|Guest whereDeletedAt($value)
 * @method static Builder|Guest whereEmail($value)
 * @method static Builder|Guest whereGender($value)
 * @method static Builder|Guest whereId($value)
 * @method static Builder|Guest whereIdentityDocumentNumber($value)
 * @method static Builder|Guest whereIdentityDocumentType($value)
 * @method static Builder|Guest whereName($value)
 * @method static Builder|Guest wherePhone($value)
 * @method static Builder|Guest whereSourceId($value)
 * @method static Builder|Guest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Guest extends Model
{
    protected $casts = [
        'data'          => 'array',
        'date_of_birth' => 'datetime:Y-m-d',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    protected static function booted()
    {
        static::created(
            function () {
                HydrateTenant::make()->userStats();
            }
        );
        static::deleted(
            function () {
                HydrateTenant::make()->userStats();
            }
        );
        static::updated(function (Guest $guest) {
            if(!$guest->wasRecentlyCreated) {
                if ($guest->wasChanged('status')) {
                    HydrateTenant::make()->userStats();
                }
            }
        });
    }



    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'parent');
    }

}
