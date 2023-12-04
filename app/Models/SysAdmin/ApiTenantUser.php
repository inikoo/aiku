<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:30:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\SysAdmin\ApiTenantUser
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
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
