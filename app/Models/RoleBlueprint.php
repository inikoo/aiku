<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string $org_type
 * @property string $scope
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RoleBlueprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleBlueprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleBlueprint query()
 * @mixin \Eloquent
 */
class RoleBlueprint extends Model
{
    protected $guarded = [];
}
