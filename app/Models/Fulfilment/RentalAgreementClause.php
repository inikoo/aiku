<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 16:29:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $fulfilment_customer_id
 * @property int $rental_id
 * @property string $agreed_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read \App\Models\Fulfilment\Rental $rental
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause query()
 * @mixin \Eloquent
 */
class RentalAgreementClause extends Model
{
    protected $guarded = [];


    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }


}
