<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $fulfilment_customer_id
 * @property int $rental_id
 * @property string $agreed_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementClause query()
 * @mixin \Eloquent
 */
class RentalAgreementClause extends Model
{
}
