<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 16:29:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

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
