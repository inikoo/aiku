<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 17:25:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use App\Models\Assets\Currency;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Mail\Mailroom;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Traits\HasLogo;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SysAdmin\Group
 *
 * @property int $id
 * @property string $ulid
 * @property string $slug
 * @property string|null $subdomain
 * @property string $code
 * @property string $name
 * @property int $country_id
 * @property int $language_id
 * @property int $timezone_id
 * @property int $currency_id customer accounting currency
 * @property int|null $logo_id
 * @property int $number_organisations
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Agent> $agents
 * @property-read Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $employees
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SysAdmin\Guest> $guests
 * @property-read \App\Models\SysAdmin\GroupHumanResourcesStats|null $humanResourcesStats
 * @property-read \App\Models\SysAdmin\GroupInventoryStats|null $inventoryStats
 * @property-read \App\Models\Media\Media|null $logo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Mailroom> $mailrooms
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SysAdmin\Organisation> $organisations
 * @property-read \App\Models\SysAdmin\GroupProcurementStats|null $procurementStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockFamily> $stockFamilies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Supplier> $suppliers
 * @property-read GroupSysAdminStats|null $sysadminStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Guest> $users
 * @method static \Database\Factories\SysAdmin\GroupFactory factory($count = null, $state = [])
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group onlyTrashed()
 * @method static Builder|Group query()
 * @method static Builder|Group withTrashed()
 * @method static Builder|Group withoutTrashed()
 * @mixin Eloquent
 */
class Group extends Model implements HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use HasFactory;
    use InteractsWithMedia;
    use HasLogo;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function stockFamilies(): HasMany
    {
        return $this->hasMany(StockFamily::class);
    }
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function humanResourcesStats(): HasOne
    {
        return $this->hasOne(GroupHumanResourcesStats::class);
    }
    public function procurementStats(): HasOne
    {
        return $this->hasOne(GroupProcurementStats::class);
    }
    public function sysadminStats(): HasOne
    {
        return $this->hasOne(GroupSysAdminStats::class);
    }

    public function inventoryStats(): HasOne
    {
        return $this->hasOne(GroupInventoryStats::class);
    }

    public function organisations(): HasMany
    {
        return $this->hasMany(Organisation::class);
    }

    public function mailrooms(): HasMany
    {
        return $this->hasMany(Mailroom::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }
}
