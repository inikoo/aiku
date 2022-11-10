<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 20 Sept 2022 19:26:54 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Stancl\Tenancy\Contracts\Syncable;


/**
 * App\Models\Central\TenantUser
 *
 * @property int $id
 * @property string $tenant_id
 * @property string $global_user_id
 * @property bool $status
 * @property-read \App\Models\Central\Tenant $tenant
 * @method static Builder|TenantUser newModelQuery()
 * @method static Builder|TenantUser newQuery()
 * @method static Builder|TenantUser query()
 * @method static Builder|TenantUser whereGlobalUserId($value)
 * @method static Builder|TenantUser whereId($value)
 * @method static Builder|TenantUser whereStatus($value)
 * @method static Builder|TenantUser whereTenantId($value)
 * @mixin \Eloquent
 */
class TenantUser extends Pivot
{
    public $incrementing = true;

    public $table='tenant_users';

    public static function boot()
    {
        parent::boot();


        static::saved(function (self $pivot) {
            $parent = $pivot->pivotParent;

            if ($parent instanceof Syncable) {
                $parent->triggerSyncEvent();
            }
        });
    }



    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

}
