<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 10:24:56 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models;

use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\AgentTenant
 *
 * @property int $id
 * @property int $agent_id
 * @property int $tenant_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_id
 * @method static \Illuminate\Database\Eloquent\Builder|AgentTenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentTenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AgentTenant query()
 * @mixin \Eloquent
 */
class AgentTenant extends Pivot
{
    use UsesGroupConnection;
    protected $table = 'agent_tenant';



}
