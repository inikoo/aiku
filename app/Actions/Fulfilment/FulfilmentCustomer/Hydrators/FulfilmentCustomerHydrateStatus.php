<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 15:19:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\FulfilmentCustomer\FulfilmentCustomerStatus;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentCustomerHydrateStatus
{
    use AsAction;
    use WithEnumStats;

    private FulfilmentCustomer $fulfilmentCustomer;

    public function __construct(FulfilmentCustomer $fulfilmentCustomer)
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->fulfilmentCustomer->id))->dontRelease()];
    }

    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $status = FulfilmentCustomerStatus::NO_RENTAL_AGREEMENT;

        if ($fulfilmentCustomer->rentalAgreement) {
            if ($fulfilmentCustomer->rentalAgreement->state == RentalAgreementStateEnum::ACTIVE) {
                $status = FulfilmentCustomerStatus::ACTIVE;

            } elseif ($fulfilmentCustomer->rentalAgreement->state == RentalAgreementStateEnum::EXPIRED) {
                $status = FulfilmentCustomerStatus::LOST;
            }
        }
        $fulfilmentCustomer->update(
            ['status' => $status]
        );
    }

}
