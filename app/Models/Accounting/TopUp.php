<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Aug 2024 10:16:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Enums\Accounting\TopUp\TopUpStatusEnum;
use App\Models\Helpers\Currency;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property TopUpStatusEnum $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\Accounting\CreditTransaction|null $creditTransaction
 * @property-read Currency|null $currency
 * @property-read \App\Models\CRM\Customer|null $customer
 * @property-read \App\Models\SysAdmin\Group|null $group
 * @property-read \App\Models\SysAdmin\Organisation|null $organisation
 * @property-read \App\Models\Accounting\Payment|null $payment
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|TopUp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TopUp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TopUp query()
 * @mixin \Eloquent
 */
class TopUp extends Model implements Auditable
{
    use HasSlug;
    use HasUniversalSearch;
    use InCustomer;
    use HasHistory;

    protected $casts = [
        'status'         => TopUpStatusEnum::class,
        'data'           => 'array',
        'amount'         => 'decimal:2',
        'grp_exchange'   => 'decimal:4',
        'org_exchange'   => 'decimal:4',
        'grp_amount'     => 'decimal:2',
        'org_amount'     => 'decimal:2',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['accounting'];
    }

    protected array $auditInclude = [
        'reference',
        'status',
        'amount',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function creditTransaction(): HasOne
    {
        return $this->hasOne(CreditTransaction::class);
    }

}
