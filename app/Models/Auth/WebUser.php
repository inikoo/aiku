<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 12:55:25 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Enums\Auth\WebUser\WebUserAuthTypeEnum;
use App\Enums\Auth\WebUser\WebUserTypeEnum;
use App\Models\CRM\Customer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

/**
 * App\Models\Auth\WebUser
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
 * @property WebUserAuthTypeEnum $auth_type
 * @property string|null $remember_token
 * @property int $number_api_tokens
 * @property array $data
 * @property array $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property WebUserTypeEnum $state
 * @property-read Customer $customer
 * @property-read Collection<int, \App\Models\Organisation\TenantPersonalAccessToken> $tokens
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
    use IsWebUser;


    protected $guarded = [
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [

        'data'      => 'array',
        'settings'  => 'array',
        'state'     => WebUserTypeEnum::class,
        'auth_type' => WebUserAuthTypeEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected static function booted(): void
    {
        static::updated(function (WebUser $webUser) {
            if ($webUser->wasChanged('status')) {
                CustomerHydrateWebUsers::dispatch($webUser->customer);
            }
        });
    }


}
