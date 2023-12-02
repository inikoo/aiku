<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Grouping;

use App\Models\Assets\Currency;
use App\Models\Auth\Guest;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Mail\Mailroom;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Organisation\Group
 *
 * @property int $id
 * @property int|null $owner_id Organisation who owns this model
 * @property string $ulid
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property int $currency_id
 * @property int $number_organisations
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Agent> $agents
 * @property-read Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $employees
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Guest> $guests
 * @property-read \App\Models\Grouping\GroupHumanResourcesStats|null $humanResourcesStats
 * @property-read \App\Models\Grouping\GroupInventoryStats|null $inventoryStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Mailroom> $mailrooms
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grouping\Organisation> $organisations
 * @property-read \App\Models\Grouping\GroupProcurementStats|null $procurementStats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockFamily> $stockFamilies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stock> $stocks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Supplier> $suppliers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Guest> $users
 * @method static \Database\Factories\Organisation\GroupFactory factory($count = null, $state = [])
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group onlyTrashed()
 * @method static Builder|Group query()
 * @method static Builder|Group withTrashed()
 * @method static Builder|Group withoutTrashed()
 * @mixin Eloquent
 */
class Group extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasFactory;

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
}
