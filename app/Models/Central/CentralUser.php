<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:25:23 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;


use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;



/**
 * App\Models\Central\CentralUser
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property string|null $about
 * @property array|null $data
 * @property int $number_tenants
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static Builder|CentralUser newModelQuery()
 * @method static Builder|CentralUser newQuery()
 * @method static Builder|CentralUser query()
 * @method static Builder|CentralUser whereAbout($value)
 * @method static Builder|CentralUser whereCreatedAt($value)
 * @method static Builder|CentralUser whereData($value)
 * @method static Builder|CentralUser whereEmail($value)
 * @method static Builder|CentralUser whereId($value)
 * @method static Builder|CentralUser whereNumberTenants($value)
 * @method static Builder|CentralUser wherePassword($value)
 * @method static Builder|CentralUser whereUpdatedAt($value)
 * @method static Builder|CentralUser whereUsername($value)
 * @mixin \Eloquent
 */
class CentralUser extends Model
{
    use HasSlug;
    use UsesLandlordConnection;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];
    public $timestamps = false;


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('username')
            ->saveSlugsTo('username');
    }


    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            Tenant::class,
            'tenant_users',
            'global_user_id',
            'tenant_id',
            'global_id',
            'id'
        )->using(TenantUser::class);
    }

    public function getTenantModelName(): string
    {
        return User::class;
    }

    public function getGlobalIdentifierKey()
    {
        return $this->getAttribute($this->getGlobalIdentifierKeyName());
    }

    public function getGlobalIdentifierKeyName(): string
    {
        return 'global_id';
    }

    public function getCentralModelName(): string
    {
        return static::class;
    }

    public function getSyncedAttributeNames(): array
    {
        return [
            'username',
            'password',
            'email',
            'about',
            'number_tenants'
        ];
    }


}
