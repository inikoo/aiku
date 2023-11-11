<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:03:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\HumanResources;

use App\Models\Auth\Role;
use App\Models\Traits\HasHistory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\HumanResources\JobPosition
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property string|null $department
 * @property string|null $team
 * @property array $data
 * @property int $number_employees
 * @property int $number_roles
 * @property float $number_work_time
 * @property string|null $share_work_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read array $es_audits
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @method static Builder|JobPosition newModelQuery()
 * @method static Builder|JobPosition newQuery()
 * @method static Builder|JobPosition query()
 * @mixin Eloquent
 */
class JobPosition extends Model implements Auditable
{
    use HasSlug;
    use HasHistory;


    protected $casts = [
        'data'  => 'array',
    ];

    protected $attributes = [
        'data'  => '{}',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(8);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $guarded = [];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
