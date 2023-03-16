<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 14:54:13 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Central\User
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property int $tenant_id
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property array $data
 * @property array $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Central\Tenant $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasSlug;
    use UsesLandlordConnection;
    use HasUniversalSearch;

    protected $guarded = [
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('username')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('username');
    }

    protected $casts = [

        'data'     => 'array',
        'settings' => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
