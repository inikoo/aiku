<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 12:08:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateAccounting implements ShouldBeUnique
{
    use AsAction;

    public function handle(Tenant $tenant): void
    {
        $paymentRecords = Payment::count();
        $refunds        = Payment::where('type', 'refund')->count();

        $dCAmountSuccessfullyPaid = Payment::where('type', 'payment')
            ->where('status', 'success')
            ->sum('dc_amount');
        $dCAmountRefunded         = Payment::where('type', 'refund')
            ->where('status', 'success')
            ->sum('dc_amount');

        $stats = [
            'number_payment_service_providers' => PaymentServiceProvider::count(),
            'number_payment_accounts'          => PaymentAccount::count(),
            'number_payment_records'           => $paymentRecords,
            'number_payments'                  => $paymentRecords - $refunds,
            'number_refunds'                   => $refunds,
            'dc_amount'                        => $dCAmountSuccessfullyPaid + $dCAmountRefunded,
            'dc_amount_successfully_paid'      => $dCAmountSuccessfullyPaid,
            'dc_amount_refunded'               => $dCAmountRefunded
        ];

        $stateCounts = Payment::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::asDatabaseColumns() as $state) {
            $stats["number_payment_records_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $stateCounts = Payment::where('type', 'payment')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::asDatabaseColumns() as $state) {
            $stats["number_payments_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $stateCounts = Payment::where('type', 'refund')
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PaymentStateEnum::asDatabaseColumns() as $state) {
            $stats["number_refunds_state_$state"] = Arr::get($stateCounts, $state, 0);
        }

        $tenant->accountingStats()->update($stats);
    }

    public function getJobUniqueId(Tenant $tenant): string
    {
        return $tenant->id;
    }

    public function getJobTags(): array
    {
        /** @var Tenant $tenant */
        $tenant=app('currentTenant');
        return ['central','tenant:'.$tenant->code];
    }
}
