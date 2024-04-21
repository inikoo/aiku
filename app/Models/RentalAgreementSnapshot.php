<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $rental_agreement_id
 * @property string $data
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementSnapshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementSnapshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreementSnapshot query()
 * @mixin \Eloquent
 */
class RentalAgreementSnapshot extends Model
{
}
