<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:11 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Dispatching\DeliveryNote\StoreDeliveryNote;
use App\Actions\Dispatching\DeliveryNoteItem\StoreDeliveryNoteItem;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasOrderingAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStatusEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Ordering\Order;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class SendOrderToWarehouse extends OrgAction
{
    use WithActionUpdate;
    use HasOrderHydrators;
    use HasOrderingAuthorisation;


    private Order $order;

    public function __construct()
    {
        $this->authorisationType = 'update';
    }

    public function handle(Order $order, array $modelData): Order
    {
        $modelData = ['state' => OrderStateEnum::IN_WAREHOUSE];
        $date = now();

        if (Arr::exists($modelData, 'warehouse_id')){
            $warehouseId = Arr::pull($modelData, 'warehouse_id');
        } else {
            $warehouseId = $this->warehouse_id;
        }

        if ($order->state == OrderStateEnum::SUBMITTED || $order->in_warehouse_at == null) {
            data_set($modelData, 'in_warehouse_at', $date);
        }

        $transactions = $order->transactions()->where('state', TransactionStateEnum::SUBMITTED)->get();
        foreach ($transactions as $transaction) {
            $transactionData = ['state' => TransactionStateEnum::IN_WAREHOUSE];
            if ($transaction->in_warehouse_at == null) {
                data_set($transactionData, 'in_warehouse_at', $date);
            }
            $transaction->update($transactionData);
        }

        $deliveryAddress = $order->deliveryAddress;
        $deliverynoteAddress = Arr::except($deliveryAddress, 'id');
        // $warehouse = $order->organisation->warehouses()->first();
        $deliveryNoteData   = [
            'delivery_address' => $deliverynoteAddress,
            'date'             => $date,
            'reference'        => $order->reference,
            'state'            => DeliveryNoteStateEnum::SUBMITTED,
            'status'           => DeliveryNoteStatusEnum::HANDLING,
            'warehouse_id'     => $warehouseId
        ];

        $deliveryNote = StoreDeliveryNote::make()->action($order, $deliveryNoteData);
        
        $transactionProducts = $order->transactions()->where('model_type', 'Product')->where('state', TransactionStateEnum::SUBMITTED)->get();

        foreach ($transactionProducts as $transactionProduct)
        {
            $product = Product::find($transactionProduct->model_id);
            foreach($product->orgStocks as $orgStock)
            {
                $quantity = $orgStock->pivot->quantity * $transactionProduct->ordered_quantity;
                $deliveryNoteItemData = [
                    'org_stock_id' => $orgStock->id,
                    'transaction_id' => $transactionProduct->id,
                    'quantity_required' => $quantity
                ];
                StoreDeliveryNoteItem::make()->action($deliveryNote, $deliveryNoteItemData);
            }
        }

        $this->update($order, $modelData);
        $this->orderHydrators($order);

        return $order;
    }

    public function rules(): array
    {
        return [
            'warehouse_id'     => [
                'required',
                Rule::exists('warehouses', 'id')
                    ->where('organisation_id', $this->organisation->id),
            ],
        ];
    }


    public function afterValidator(Validator $validator): void
    {

        if ($this->order->state == OrderStateEnum::CREATING) {
            $validator->errors()->add('state', __('Only submitted orders can be send to warehouse'));
        } elseif ($this->order->state == OrderStateEnum::SUBMITTED && !$this->order->transactions->count()) {
            $validator->errors()->add('state', __('Order dont have any transactions to be send to warehouse'));
        } elseif ($this->order->state == OrderStateEnum::IN_WAREHOUSE || $this->order->state == OrderStateEnum::HANDLING || $this->order->state == OrderStateEnum::PACKED) {
            $validator->errors()->add('state', __('Order already in warehouse'));
        } elseif($this->order->state == OrderStateEnum::FINALISED) {
            $validator->errors()->add('state', __('Order is already finalised'));
        } elseif ($this->order->state == OrderStateEnum::DISPATCHED) {
            $validator->errors()->add('state', __('Order is already dispatched'));
        } elseif ($this->order->state == OrderStateEnum::CANCELLED) {
            $validator->errors()->add('state', __('Order has been cancelled'));
        }
    }

    public function action(Order $order, $modelData): Order
    {
        $this->asAction = true;
        $this->scope    = $order->shop;
        $this->order    = $order;
        $this->initialisationFromShop($order->shop, []);

        return $this->handle($order, $modelData);
    }

    public function asController(Order $order, ActionRequest $request)
    {
        $this->order = $order;
        $this->scope = $order->shop;
        $this->initialisationFromShop($order->shop, $request);
        return $this->handle($order, $this->validatedData);
    }

    public function prepareForValidation(ActionRequest $request): void
    {

        if(!$this->has('warehouse_id')) {
            $warehouse = $this->shop->organisation->warehouses()->first();
            $this->set('warehouse_id', $warehouse->id);
        }

    }


}
