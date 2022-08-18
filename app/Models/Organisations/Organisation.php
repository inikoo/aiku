<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 16 Aug 2022 20:48:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Organisations;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


/**
 * App\Models\Organisations\Organisation
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Organisations\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|Organisation newModelQuery()
 * @method static Builder|Organisation newQuery()
 * @method static Builder|Organisation query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $type
 * @property string $code
 * @property string $name
 * @property mixed $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Organisation whereCode($value)
 * @method static Builder|Organisation whereCreatedAt($value)
 * @method static Builder|Organisation whereData($value)
 * @method static Builder|Organisation whereId($value)
 * @method static Builder|Organisation whereName($value)
 * @method static Builder|Organisation whereType($value)
 * @method static Builder|Organisation whereUpdatedAt($value)
 * @property int $country_id
 * @property int $language_id
 * @property int $timezone_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Organisations\UserLinkCode[] $userLinkCodes
 * @property-read int|null $user_link_codes_count
 * @method static \Illuminate\Database\Query\Builder|Organisation onlyTrashed()
 * @method static Builder|Organisation whereCountryId($value)
 * @method static Builder|Organisation whereDeletedAt($value)
 * @method static Builder|Organisation whereLanguageId($value)
 * @method static Builder|Organisation whereTimezoneId($value)
 * @method static Builder|Organisation whereUsersCount($value)
 * @method static \Illuminate\Database\Query\Builder|Organisation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Organisation withoutTrashed()
 */
class Organisation extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    use SoftDeletes;

    protected $casts = [
        'data'     => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function userLinkCodes(): HasMany
    {
        return $this->hasMany(UserLinkCode::class);
    }


}
