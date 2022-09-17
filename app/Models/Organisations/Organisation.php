<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 16 Aug 2022 20:48:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Models\Organisations;


use App\Models\HumanResources\JobPosition;
use App\Models\JobPositionOrganisation;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use App\Models\Delivery\OrganisationShipper;
use App\Models\Delivery\Shipper;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Stock;
use App\Models\Inventory\Warehouse;
use App\Models\Marketing\Shop;
use App\Models\Marketing\TradeUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


/**
 * App\Models\Organisations\Organisation
 *
 * @property int $id
 * @property string $type
 * @property string $code
 * @property string $name
 * @property array $data
 * @property int $country_id
 * @property int $language_id
 * @property int $timezone_id
 * @property int $number_users
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Organisations\UserLinkCode[] $userLinkCodes
 * @property-read int|null $user_link_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SysAdmin\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|Organisation newModelQuery()
 * @method static Builder|Organisation newQuery()
 * @method static \Illuminate\Database\Query\Builder|Organisation onlyTrashed()
 * @method static Builder|Organisation query()
 * @method static Builder|Organisation whereCode($value)
 * @method static Builder|Organisation whereCountryId($value)
 * @method static Builder|Organisation whereCreatedAt($value)
 * @method static Builder|Organisation whereData($value)
 * @method static Builder|Organisation whereDeletedAt($value)
 * @method static Builder|Organisation whereId($value)
 * @method static Builder|Organisation whereLanguageId($value)
 * @method static Builder|Organisation whereName($value)
 * @method static Builder|Organisation whereNumberUsers($value)
 * @method static Builder|Organisation whereTimezoneId($value)
 * @method static Builder|Organisation whereType($value)
 * @method static Builder|Organisation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Organisation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Organisation withoutTrashed()
 * @mixin \Eloquent
 * @property int $currency_id Organisation accounting currency
 * @property-read \Illuminate\Database\Eloquent\Collection|Employee[] $employees
 * @property-read int|null $employees_count
 * @method static Builder|Organisation whereCurrencyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|Shop[] $shops
 * @property-read int|null $shops_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Shipper[] $shippers
 * @property-read int|null $shippers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Warehouse[] $warehouses
 * @property-read int|null $warehouses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Stock[] $stocks
 * @property-read int|null $stocks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TradeUnit[] $tradeUnits
 * @property-read int|null $trade_units_count
 * @property-read \Illuminate\Database\Eloquent\Collection|JobPosition[] $jobPositions
 * @property-read int|null $job_positions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Guest[] $guests
 * @property-read int|null $guests_count
 * @property-read \App\Models\Organisations\OrganisationStats|null $stats
 * @property-read \App\Models\Organisations\OrganisationInventoryStats|null $inventoryStats
 * @property-read Warehouse|null $firstWarehouse
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
        return $this->belongsToMany(User::class)->using(OrganisationUser::class)->withTimestamps();
    }

    public function userLinkCodes(): HasMany
    {
        return $this->hasMany(UserLinkCode::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function shippers(): BelongsToMany
    {
        return $this->belongsToMany(Shipper::class)->using(OrganisationShipper::class)->withTimestamps();
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
    public function firstWarehouse() {
        return $this->hasOne(Warehouse::class)->oldestOfMany();

    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'owner');
    }

    public function tradeUnits(): HasMany
    {
        return $this->hasMany(TradeUnit::class);
    }

    public function jobPositions(): BelongsToMany
    {
        return $this->belongsToMany(JobPosition::class)
            ->using(JobPositionOrganisation::class)
            ->withTimestamps()
            ->withPivot(['number_employees','number_work_time','share_work_time']);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(OrganisationStats::class);
    }

    public function inventoryStats(): HasOne
    {
        return $this->hasOne(OrganisationInventoryStats::class);
    }

}
