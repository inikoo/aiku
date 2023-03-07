<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 12:39:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Central\Admin
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property string $email
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Central\AdminUser|null $adminUser
 * @method static Builder|Admin newModelQuery()
 * @method static Builder|Admin newQuery()
 * @method static Builder|Admin onlyTrashed()
 * @method static Builder|Admin query()
 * @method static Builder|Admin withTrashed()
 * @method static Builder|Admin withoutTrashed()
 * @mixin \Eloquent
 */
class Admin extends Model
{
    use UsesLandlordConnection;
    use HasSlug;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(8);
    }

    public function adminUser(): MorphOne
    {
        return $this->morphOne(AdminUser::class, 'userable');
    }
}
