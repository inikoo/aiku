<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 02:04:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Leads;

use App\Actions\Helpers\ReadableRandomStringGenerator;
use App\Actions\Utils\Abbreviate;
use App\Enums\Leads\Prospect\ProspectStateEnum;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;
use App\Models\Sales\Customer;
use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasTenantAddress;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Leads\Prospect
 *
 * @property int $id
 * @property int $shop_id
 * @property int|null $customer_id
 * @property string $slug
 * @property string|null $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $identity_document_type
 * @property string|null $identity_document_number
 * @property string|null $contact_website
 * @property array $location
 * @property ProspectStateEnum $state
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Collection<int, Address> $addresses
 * @property-read Customer|null $customer
 * @property-read Shop $shop
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Leads\ProspectFactory factory($count = null, $state = [])
 * @method static Builder|Prospect newModelQuery()
 * @method static Builder|Prospect newQuery()
 * @method static Builder|Prospect onlyTrashed()
 * @method static Builder|Prospect query()
 * @method static Builder|Prospect withTrashed()
 * @method static Builder|Prospect withoutTrashed()
 * @mixin Eloquent
 */
class Prospect extends Model
{
    use UsesTenantConnection;
    use SoftDeletes;
    use HasTenantAddress;
    use HasSlug;
    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'data'     => 'array',
        'location' => 'array',
        'state'    => ProspectStateEnum::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'location' => '{}',
        'state'    => 'registered'
    ];


    protected static function booted(): void
    {
        static::creating(
            function (Prospect $prospect) {
                $prospect->name = $prospect->company_name == '' ? $prospect->contact_name : $prospect->company_name;
            }
        );


        static::updated(function (Prospect $prospect) {
            if ($prospect->wasChanged(['company_name', 'contact_name'])) {
                $prospect->name = $prospect->company_name == '' ? $prospect->contact_name : $prospect->company_name;
            }
        });
    }

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                $name = $this->company_name == '' ? $this->contact_name : $this->company_name;
                if ($name != '') {
                    return Abbreviate::run($name);
                }

                return ReadableRandomStringGenerator::run();
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
