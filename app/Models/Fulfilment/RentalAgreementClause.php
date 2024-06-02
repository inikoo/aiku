<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 16:29:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Models\Catalogue\Asset;
use App\Models\Traits\InFulfilmentCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $fulfilment_id
 * @property int $fulfilment_customer_id
 * @property int $asset_id
 * @property int $rental_agreement_id
 * @property string|null $type
 * @property string $agreed_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read Asset|null $product
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause query()
 * @mixin \Eloquent
 */
class RentalAgreementClause extends Model
{
    use InFulfilmentCustomer;

    protected $guarded = [];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }




}
