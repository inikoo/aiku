<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 12:28:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Helpers;

use App\Enums\Helpers\TaxNumber\TaxNumberStatusEnum;
use App\Enums\Helpers\TaxNumber\TaxNumberTypeEnum;
use App\Models\Assets\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class TaxNumber extends Model
{
    use UsesTenantConnection;

    protected $casts = [
        'data'       => 'array',
        'audited_at' => 'datetime',
        'status'     => TaxNumberStatusEnum::class,
        'type'       => TaxNumberTypeEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(
            function (TaxNumber $taxNumber) {
                if ($taxNumber->country) {
                    $taxNumber->country_code = $taxNumber->country->code;
                }
            }
        );
    }

    public function country(): HasOne
    {
        return $this->hasOne(Country::class);
    }
}
