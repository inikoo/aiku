<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:12:24 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SysAdmin\SysUser
 *
 * @property int $id
 * @property string $userable_type
 * @property int $userable_id
 * @property string $username
 * @property string $password
 * @property bool $status
 * @property int $language_id
 * @property int $timezone_id
 * @property string|null $email
 * @property array $data
 * @property array $settings
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read Model|\Eloquent $userable
 * @method static \Database\Factories\SysAdmin\SysUserFactory factory($count = null, $state = [])
 * @method static Builder|SysUser newModelQuery()
 * @method static Builder|SysUser newQuery()
 * @method static Builder|SysUser onlyTrashed()
 * @method static Builder|SysUser query()
 * @method static Builder|SysUser withTrashed()
 * @method static Builder|SysUser withoutTrashed()
 * @mixin Eloquent
 */
class SysUser extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;
    use SoftDeletes;
    use HasSlug;
    use UsesLandlordConnection;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array',
        'status'   => 'boolean',
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $hidden = [
        'password',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('username')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('username');
    }

    /**
     * @return string
     * Hack for laravel permissions to work
     */
    public function guardName(): string
    {
        return 'admin';
    }

    protected $guarded = [];

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }


}
