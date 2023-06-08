<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:12:24 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Database\Factories\SysAdmin\AdminFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SysAdmin\Admin
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property string $email
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read SysUser|null $sysUser
 * @method static AdminFactory factory($count = null, $state = [])
 * @method static Builder|Admin newModelQuery()
 * @method static Builder|Admin newQuery()
 * @method static Builder|Admin onlyTrashed()
 * @method static Builder|Admin query()
 * @method static Builder|Admin withTrashed()
 * @method static Builder|Admin withoutTrashed()
 * @mixin Eloquent
 */
class Admin extends Model
{
    use UsesLandlordConnection;
    use HasSlug;
    use SoftDeletes;
    use HasFactory;

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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function sysUser(): MorphOne
    {
        return $this->morphOne(SysUser::class, 'userable');
    }
}
