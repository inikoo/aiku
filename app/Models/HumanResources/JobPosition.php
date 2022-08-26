<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:03:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\HumanResources\JobPosition
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $department
 * @property string|null $team
 * @property array $roles
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\HumanResources\Employee[] $employees
 * @property-read int|null $employees_count
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobPosition whereUpdatedAt($value)
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

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)->withTimestamps();
    }
}
