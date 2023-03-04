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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\Multitenancy\TenantCollection<int, \App\Models\Central\Tenant> $tenants
 * @method static Builder|CentralUser newModelQuery()
 * @method static Builder|CentralUser newQuery()
 * @method static Builder|CentralUser query()
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
            'central_user_tenant',
        )->using(CentralUserTenant::class);
    }



}
