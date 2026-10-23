<?php

/*
 * Author Louis Perez
 * Created on 13-07-2026-09h-27m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Ordering\Transaction;

use App\Actions\Dispatching\DeliveryNote\CalculateDeliveryNoteTotalAmounts;
use App\Actions\Dispatching\DeliveryNote\WithDeliveryNoteQuantitySync;
use App\Actions\Ordering\Order\CalculateOrderTotalAmounts;
use App\Actions\Ordering\Order\UpdateOrderPaymentsStatus;
use App\Actions\Ordering\UpcomingTransaction\UpdateUpcomingTransaction;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\Ordering\WithOrderingEditAuthorisation;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Events\BroadcastTransactionUpdated;
use App\Models\Catalogue\Product;
use App\Models\Ordering\Order;
use App\Enums\Ordering\Transaction\UpcomingTransactionStateEnum;
use App\Models\Ordering\Transaction;
use App\Models\Ordering\UpcomingTransaction;
use App\Models\SysAdmin\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

class UpdateTransactionProductQuantityOrdered extends OrgAction
{
    use WithOrderingEditAuthorisation;
    use WithDeliveryNoteQuantitySync;

    /**
     * @throws \Throwable
     */
    public function handle(Transaction $transaction, array $modelData, User $user): Transaction
    {
        if ($transaction->model_type != class_basename(Product::class)) {
            abort(422, __('Unable to modify this transaction (Not a valid product transaction)'));
        }

        $order = $transaction->order;

        if ($order->shop->type == ShopTypeEnum::EXTERNAL) {
            abort(422, __('Orders of external shops follow the external platform data and cannot be modified here'));
        }

        if ($order->platform && $order->platform->type != PlatformTypeEnum::MANUAL) {
            abort(422, __('Platform orders cannot be modified here'));
        }

        if (in_array($order->state, [OrderStateEnum::CANCELLED, OrderStateEnum::FINALISED, OrderStateEnum::DISPATCHED])) {
            abort(422, __('This order can no longer be modified'));
        }

        if ($transaction->is_gift) {
            $modelData = $this->giftQuantityData($transaction, $modelData);
        }

        $product   = $transaction->model()->with('orgStocks')->first();
        $orgStocks = $product->orgStocks->keyBy('id');

        $transaction = DB::transaction(function () use ($transaction, $modelData, $user, $order, $orgStocks) {
            $deliveryNotes = $order
                ->deliveryNotes()
                ->whereNotIn('state', [
                    DeliveryNoteStateEnum::DISPATCHED,
                    DeliveryNoteStateEnum::CANCELLED,
                    DeliveryNoteStateEnum::FINALISED
                ])
                ->lockForUpdate()
                ->get();

            $orderIsInWarehouse = !in_array($order->state, [OrderStateEnum::CREATING, OrderStateEnum::SUBMITTED]);
            if ($deliveryNotes->isEmpty() && ($orderIsInWarehouse || $order->deliveryNotes()->whereNot('state', DeliveryNoteStateEnum::CANCELLED)->exists())) {
                abort(409, __('No editable delivery note available'));
            }

            $transaction = UpdateTransaction::make()->action($transaction, $modelData);
            $transaction->refresh();

            if ($transaction->is_gift && (float)$transaction->quantity_bonus == 0 && in_array($order->state, [OrderStateEnum::CREATING, OrderStateEnum::SUBMITTED])) {
                $transaction = DeleteTransaction::run($transaction);
            }

            if (!$transaction->trashed()) {
                foreach ($deliveryNotes as $deliveryNote) {
                    $this->syncDeliveryNote($deliveryNote, $transaction, $orgStocks, $user);
                }
            }

            CalculateOrderTotalAmounts::run($order);
            UpdateOrderPaymentsStatus::run($order->refresh());
            foreach ($deliveryNotes as $deliveryNote) {
                CalculateDeliveryNoteTotalAmounts::run($deliveryNote);
            }

            if ($transaction->is_gift && (float)$transaction->quantity_bonus == 0) {
                $this->releaseUpcomingTransaction($transaction);
            }

            $this->recordModification($order, $transaction, $modelData, $user);

            return $transaction;
        });

        BroadcastTransactionUpdated::dispatch($transaction, $order);

        return $transaction;
    }

    /**
     * A gift line orders nothing: its quantity is the free quantity_bonus. Staff may lower or remove
     * it, never raise it, so a gift can not be used to hand out free stock (HELP-4082).
     */
    protected function giftQuantityData(Transaction $transaction, array $modelData): array
    {
        if (Arr::has($modelData, 'units_ordered') || !Arr::has($modelData, 'quantity_ordered')) {
            abort(422, __('A free gift can only be changed by its quantity'));
        }

        $quantity = (float)$modelData['quantity_ordered'];
        if ($quantity > (float)$transaction->quantity_bonus + 0.000001) {
            abort(422, __('A free gift can be lowered or removed, not raised'));
        }

        return ['quantity_bonus' => $quantity];
    }

    /**
     * A removed claim follow-on or customer service gift goes back to the customer's upcoming
     * items, so it ships with their next order instead of being lost.
     */
    protected function releaseUpcomingTransaction(Transaction $transaction): void
    {
        foreach (UpcomingTransaction::where('transaction_id', $transaction->id)->get() as $upcomingTransaction) {
            UpdateUpcomingTransaction::run($upcomingTransaction, [
                'order_id'       => null,
                'transaction_id' => null,
                'state'          => UpcomingTransactionStateEnum::READY,
            ]);
        }
    }

    protected function recordModification(Order $order, Transaction $transaction, array $modelData, User $user): void
    {
        $modifications   = $order->post_submit_modification_data ?? [];
        $modifications[] = [
            'date_time'   => Carbon::now()->toDateTimeString(),
            'modified_by' => $user->username,
            'data'        => [
                'transaction_id' => $transaction->id,
                ...$modelData
            ]
        ];

        $order->update([
            'post_submit_modification_data' => $modifications
        ]);
    }

    public function rules(): array
    {
        return [
            'units_ordered'    => ['sometimes', 'numeric', 'integer', 'min:0'],
            'quantity_ordered' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(Transaction $transaction, ActionRequest $request): Transaction
    {
        $this->initialisationFromShop($transaction->shop, $request);

        return $this->handle($transaction, $this->validatedData, $request->user());
    }

    /**
     * @throws \Throwable
     */
    public function action(Transaction $transaction, array $modelData, User $user): Transaction
    {
        $this->asAction = true;
        $this->initialisationFromShop($transaction->shop, $modelData);

        return $this->handle($transaction, $this->validatedData, $user);
    }

    public function jsonResponse(Transaction $transaction): Transaction
    {
        return $transaction;
    }

    public function htmlResponse(Transaction $transaction): RedirectResponse
    {
        return back();
    }
}
