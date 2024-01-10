<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateAccounting implements ShouldBeUnique
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $paymentRecords = Payment::count();
        $refunds        = Payment::where('type', 'refund')->count();

        $amountTenantCurrencySuccessfullyPaid = Payment::where('type', 'payment')
            ->where('status', 'success')
            ->sum('oc_amount');
        $amountTenantCurrencyRefunded         = Payment::where('type', 'refund')
            ->where('status', 'success')
            ->sum('oc_amount');

        $stats = [
            'number_payment_service_providers' => PaymentServiceProvider::count(),
            'number_payment_accounts'          => PaymentAccount::count(),
            'number_payment_records'           => $paymentRecords,
            'number_payments'                  => $paymentRecords - $refunds,
            'number_refunds'                   => $refunds,
            'oc_amount'                        => $amountTenantCurrencySuccessfullyPaid + $amountTenantCurrencyRefunded,
            'oc_amount_successfully_paid'      => $amountTenantCurrencySuccessfullyPaid,
            'oc_amount_refunded'               => $amountTenantCurrencyRefunded,
        ];

        $stateCounts = Payment::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payment_records_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts = Payment::where('type', 'payment')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_payments_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $stateCounts = Payment::where('type', 'refund')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::cases() as $state) {
            $stats["number_refunds_state_{$state->snake()}"] = Arr::get($stateCounts, $state->value, 0);
        }

        $organisation->accountingStats()->update($stats);
    }
}
