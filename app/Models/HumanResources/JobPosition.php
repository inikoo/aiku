<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:03:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\HumanResources;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\HumanResources\JobPosition
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $department
 * @property string|null $team
 * @property array $roles
 * @property array $data
 * @property int $number_employees
 * @property float $number_work_time
 * @property string|null $share_work_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|JobPosition newModelQuery()
 * @method static Builder|JobPosition newQuery()
 * @method static Builder|JobPosition query()
 * @mixin Eloquent
 */
class JobPosition extends Model
{
    use UsesTenantConnection;

    protected $casts = [
        'data'  => 'array',
        'roles' => 'array',
    ];

    protected $attributes = [
        'data'  => '{}',
        'roles' => '{}',
    ];


    protected $guarded = [];
}
