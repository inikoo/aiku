<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 12:56:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Actions\Hydrators\HydrateOrganisation;
use App\Models\Organisations\Organisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * App\Models\SysAdmin\Guest
 *
 * @property int $id
 * @property int $organisation_id
 * @property string $code
 * @property bool $status linked to user status
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
 * @property int|null $organisation_source_id
 * @method static Builder|Guest newModelQuery()
 * @method static Builder|Guest newQuery()
 * @method static Builder|Guest query()
 * @method static Builder|Guest whereCode($value)
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
 * @method static Builder|Guest whereOrganisationId($value)
 * @method static Builder|Guest whereOrganisationSourceId($value)
 * @method static Builder|Guest wherePhone($value)
 * @method static Builder|Guest whereStatus($value)
 * @method static Builder|Guest whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read Organisation $organisation
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
            function (Guest $guest) {
                HydrateOrganisation::make()->userStats($guest->organisation);
            }
        );
        static::deleted(
            function (Guest $guest) {
                HydrateOrganisation::make()->userStats($guest->organisation);
            }
        );
        static::updated(function (Guest $guest) {
            if ($guest->wasChanged('status')) {
                HydrateOrganisation::make()->userStats($guest->organisation);
            }
        });
    }


    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable','organisation_user');
    }

}
