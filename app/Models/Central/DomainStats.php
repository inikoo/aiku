<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 28 Jun 2023 10:39:54 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\DomainStats
 *
 * @property int $id
 * @property int $domain_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|DomainStats newModelQuery()
 * @method static Builder|DomainStats newQuery()
 * @method static Builder|DomainStats query()
 * @mixin Eloquent
 */
class DomainStats extends Model
{
    use UsesLandlordConnection;
}
