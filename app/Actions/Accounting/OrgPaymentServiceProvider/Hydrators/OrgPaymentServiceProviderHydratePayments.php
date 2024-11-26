<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 11:29:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\OrgPaymentServiceProvider\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentTypeEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\OrgPaymentServiceProvider;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgPaymentServiceProviderHydratePayments
{
    use AsAction;
    use WithEnumStats;

    private OrgPaymentServiceProvider $orgPaymentServiceProvider;

    public function __construct(OrgPaymentServiceProvider $orgPaymentServiceProvider)
    {
        $this->orgPaymentServiceProvider = $orgPaymentServiceProvider;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgPaymentServiceProvider->id))->dontRelease()];
    }

    public function handle(OrgPaymentServiceProvider $orgPaymentServiceProvider): void
    {
        $amountOrganisationCurrencySuccessfullyPaid = $orgPaymentServiceProvider->payments()
            ->where('payments.type', 'payment')
            ->where('status', 'success')
            ->sum('sales_org_currency');
        $amountOrganisationCurrencyRefunded         = $orgPaymentServiceProvider->payments()
            ->where('payments.type', 'refund')
            ->where('status', 'success')
            ->sum('sales_org_currency');

        $stats = [
            'number_payments'              => $orgPaymentServiceProvider->payments()->count(),
            'sales_org_currency'                   => $amountOrganisationCurrencySuccessfullyPaid + $amountOrganisationCurrencyRefunded,
            'sales_org_currency_successfully_paid' => $amountOrganisationCurrencySuccessfullyPaid,
            'sales_org_currency_refunded'          => $amountOrganisationCurrencyRefunded
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'payments',
                field: 'type',
                enum: PaymentTypeEnum::class,
                models: Payment::class,
                where: function ($q) use ($orgPaymentServiceProvider) {
                    $q->where('org_payment_service_provider_id', $orgPaymentServiceProvider->id);
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
                where: function ($q) use ($orgPaymentServiceProvider) {
                    $q->where('org_payment_service_provider_id', $orgPaymentServiceProvider->id);
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
                    where: function ($q) use ($orgPaymentServiceProvider, $type) {
                        $q->where('org_payment_service_provider_id', $orgPaymentServiceProvider->id)->where('type', $type->value);
                    }
                )
            );
        }

        $orgPaymentServiceProvider->stats()->update($stats);
    }


}
