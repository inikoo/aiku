<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 19:15:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\SysAdmin\SysUser;
use App\Models\Tenancy\Tenant;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;


/**
 * App\Models\Central\Domain
 *
 * @property int $id
 * @property string $slug
 * @property int $tenant_id
 * @property int $website_id
 * @property string $domain
 * @property string|null $cloudflare_id
 * @property string|null $cloudflare_status
 * @property string|null $irs_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\Central\DomainStats|null $stats
 * @property-read SysUser|null $sysUser
 * @property-read Tenant $tenant
 * @method static Builder|Domain newModelQuery()
 * @method static Builder|Domain newQuery()
 * @method static Builder|Domain onlyTrashed()
 * @method static Builder|Domain query()
 * @method static Builder|Domain withTrashed()
 * @method static Builder|Domain withoutTrashed()
 * @mixin Eloquent
 */
class Domain extends Model
{
    use HasSlug;
    use SoftDeletes;
    use UsesLandlordConnection;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('slug')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(DomainStats::class);
    }

    protected $guarded = [];

    public function sysUser(): MorphOne
    {
        return $this->morphOne(SysUser::class, 'userable');
    }
}
