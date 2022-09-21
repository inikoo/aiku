<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:03:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|JobPosition newModelQuery()
 * @method static Builder|JobPosition newQuery()
 * @method static Builder|JobPosition query()
 * @method static Builder|JobPosition whereCreatedAt($value)
 * @method static Builder|JobPosition whereData($value)
 * @method static Builder|JobPosition whereDepartment($value)
 * @method static Builder|JobPosition whereId($value)
 * @method static Builder|JobPosition whereName($value)
 * @method static Builder|JobPosition whereNumberEmployees($value)
 * @method static Builder|JobPosition whereNumberWorkTime($value)
 * @method static Builder|JobPosition whereRoles($value)
 * @method static Builder|JobPosition whereShareWorkTime($value)
 * @method static Builder|JobPosition whereSlug($value)
 * @method static Builder|JobPosition whereTeam($value)
 * @method static Builder|JobPosition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class JobPosition extends Model
{

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
