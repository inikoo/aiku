<?php

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Procurement\PurchaseOrder\UI\ShowPurchaseOrder;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderDepositPayment;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Deposits are payments towards the purchase order's total including the supplier's estimated expenses. The
 * deposit counts as paid, dated by its last payment, once nothing is left to pay.
 */
class SyncPurchaseOrderDepositPaid
{
    use AsObject;

    public function handle(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        /** @var PurchaseOrderDepositPayment|null $lastPayment */
        $lastPayment = $purchaseOrder->depositPayments()->orderBy('date')->orderBy('id')->get()->last();
        $paid        = self::paidAmount($purchaseOrder);
        $isPaid      = $lastPayment && self::leftToPay($purchaseOrder) <= 0;

        $purchaseOrder->update([
            'deposit_amount'     => $paid > 0 ? $paid : null,
            'deposit_paid_at'    => $isPaid ? $lastPayment->date->toDateString() : null,
            'deposit_paid_by_id' => $isPaid ? $lastPayment->created_by : null,
        ]);

        return $purchaseOrder;
    }

    public static function totalToPay(PurchaseOrder $purchaseOrder): float
    {
        return round((float) $purchaseOrder->cost_total + ShowPurchaseOrder::make()->estimatedExpenses($purchaseOrder), 2);
    }

    public static function paidAmount(PurchaseOrder $purchaseOrder): float
    {
        return round((float) $purchaseOrder->depositPayments()->sum('amount'), 2);
    }

    public static function leftToPay(PurchaseOrder $purchaseOrder): float
    {
        return max(0, round(self::totalToPay($purchaseOrder) - self::paidAmount($purchaseOrder), 2));
    }
}
