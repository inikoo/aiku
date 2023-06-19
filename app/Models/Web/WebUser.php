<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 27 Oct 2022 20:51:13 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Web;

use App\Actions\Sales\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Enums\Web\WebUser\WebUserLoginVersionEnum;
use App\Enums\Web\WebUser\WebUserTypeEnum;
use App\Models\Sales\Customer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Web\WebUser
 *
 * @property int $id
 * @property string $slug
 * @property string $type
 * @property int $website_id
 * @property int $customer_id
 * @property bool $status
 * @property string $username
 * @property string|null $email
 * @property string|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property int $number_api_tokens
 * @property array $data
 * @property array $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property WebUserLoginVersionEnum $login_version
 * @property int|null $source_id
 * @property string|null $source_password source password
 * @property WebUserTypeEnum $state
 * @property-read Customer $customer
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @method static Builder|WebUser newModelQuery()
 * @method static Builder|WebUser newQuery()
 * @method static Builder|WebUser onlyTrashed()
 * @method static Builder|WebUser query()
 * @method static Builder|WebUser withTrashed()
 * @method static Builder|WebUser withoutTrashed()
 * @mixin Eloquent
 */
class WebUser extends Authenticatable
{
    use UsesTenantConnection;
    use HasApiTokens;
    use SoftDeletes;
    use HasSlug;

    protected $guarded = [
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [

        'data'          => 'array',
        'settings'      => 'array',
        'state'         => WebUserTypeEnum::class,
        'login_version' => WebUserLoginVersionEnum::class,
    ];


    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $slug = $this->username;
                if (filter_var($this->username, FILTER_VALIDATE_EMAIL)) {
                    $slug = strstr($this->username, '@', true);
                }

                return $slug;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(12);
    }

    protected static function booted(): void
    {
        static::updated(function (WebUser $webUser) {
            if ($webUser->wasChanged('status')) {
                CustomerHydrateWebUsers::dispatch($webUser->customer);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
