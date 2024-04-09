<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateOrgPaymentServiceProviders
{
    use AsAction;
    use WithEnumStats;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_org_payment_service_providers' => $organisation->orgPaymentServiceProviders()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'org_payment_service_providers',
                field: 'type',
                enum: PaymentServiceProviderTypeEnum::class,
                models: OrgPaymentServiceProvider::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );


        $organisation->accountingStats()->update($stats);
    }
}
