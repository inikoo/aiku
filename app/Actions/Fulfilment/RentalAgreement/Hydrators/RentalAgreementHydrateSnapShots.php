<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 25 Jan 2024 16:42:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Fulfilment\RentalAgreement;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class RentalAgreementHydrateSnapShots
{
    use AsAction;
    use WithEnumStats;

    private RentalAgreement $rentalAgreement;
    public function __construct(RentalAgreement $rentalAgreement)
    {
        $this->rentalAgreement = $rentalAgreement;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->rentalAgreement->id))->dontRelease()];
    }

    public function handle(RentalAgreement $rentalAgreement): void
    {
        $stats = [
            'number_rental_agreement_snapshots' => $rentalAgreement->snapshots()->count()
        ];


        $rentalAgreement->stats->update($stats);

    }
}
