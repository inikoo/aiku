<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 12:39:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;


/**
 * App\Models\Central\Deployment
 *
 * @property int $id
 * @property string $version
 * @property string $hash
 * @property string $state
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Deployment newModelQuery()
 * @method static Builder|Deployment newQuery()
 * @method static Builder|Deployment query()
 * @mixin \Eloquent
 */
class Deployment extends Model
{
    use UsesLandlordConnection;

    protected $guarded = [];
    protected $attributes = [
        'data' => '{}',
    ];
    protected $casts = [
        'data' => 'array'
    ];




}
