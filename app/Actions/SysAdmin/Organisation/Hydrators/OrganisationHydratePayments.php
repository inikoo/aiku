<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydratePayments
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
        $amountOrganisationCurrencySuccessfullyPaid = $organisation->payments()->where('type', 'payment')
            ->where('status', 'success')
            ->sum('org_amount');
        $amountOrganisationCurrencyRefunded         = $organisation->payments()->where('type', 'refund')
            ->where('status', 'success')
            ->sum('org_amount');

        $stats = [
            'number_payments'                         => $organisation->payments()->count(),
            'number_org_payment_service_providers'    => $organisation->orgPaymentServiceProviders()->count(),
            'number_payment_accounts'                 => $organisation->paymentAccounts()->count(),
            'org_amount'                              => $amountOrganisationCurrencySuccessfullyPaid + $amountOrganisationCurrencyRefunded,
            'org_amount_successfully_paid'            => $amountOrganisationCurrencySuccessfullyPaid,
            'org_amount_refunded'                     => $amountOrganisationCurrencyRefunded,
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payments',
                field: 'type',
                enum: PaymentTypeEnum::class,
                models: Payment::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payments',
                field: 'state',
                enum: PaymentStateEnum::class,
                models: Payment::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );


        foreach (PaymentTypeEnum::cases() as $type) {
            $stats = array_merge(
                $stats,
                $this->getEnumStats(
                    model: "payments_type_{$type->snake()}",
                    field: 'state',
                    enum: PaymentStateEnum::class,
                    models: Payment::class,
                    where: function ($q) use ($organisation, $type) {
                        $q->where('organisation_id', $organisation->id)->where('type', $type->value);
                    }
                )
            );
        }

        $organisation->accountingStats()->update($stats);
    }
}
