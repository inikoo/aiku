<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 25 Jan 2024 16:42:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RentalAgreement\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\RentalAgreementClause\RentalAgreementClauseTypeEnum;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Fulfilment\RentalAgreementClause;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class RentalAgreementHydrateClauses
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
            'number_rental_agreement_clauses' => $rentalAgreement->clauses()->count()
        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'rental_agreement_clauses',
            field: 'type',
            enum: RentalAgreementClauseTypeEnum::class,
            models: RentalAgreementClause::class,
            where: function ($q) use ($rentalAgreement) {
                $q->where('rental_agreement_id', $rentalAgreement->id);
            }
        ));

        $rentalAgreement->stats->update($stats);

    }
}
