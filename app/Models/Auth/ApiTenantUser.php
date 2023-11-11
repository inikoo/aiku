<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jul 2023 08:58:34 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\Auth\ApiTenantUser
 *
 * @property int $id
 * @property string $userable_type
 * @property int $userable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tenancy\TenantPersonalAccessToken> $tokens
 * @property-read Model|\Eloquent $userable
 * @method static Builder|ApiTenantUser newModelQuery()
 * @method static Builder|ApiTenantUser newQuery()
 * @method static Builder|ApiTenantUser onlyTrashed()
 * @method static Builder|ApiTenantUser query()
 * @method static Builder|ApiTenantUser withTrashed()
 * @method static Builder|ApiTenantUser withoutTrashed()
 * @mixin \Eloquent
 */
class ApiTenantUser extends Model
{
    use HasApiTokens;
    use SoftDeletes;



    protected $guarded = [];

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }




}
