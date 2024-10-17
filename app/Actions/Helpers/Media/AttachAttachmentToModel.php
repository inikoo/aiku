<?php
/*
 * author Arya Permana - Kirin
 * created on 17-10-2024-14h-58m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Helpers\Media;

use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Media;
use App\Models\HumanResources\Employee;
use App\Models\Ordering\Order;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\StockDelivery;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class AttachAttachmentToModel extends OrgAction
{
    private Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $parent;

    public function handle (Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $model, array $modelData): Media
    {
        $file = $modelData['attachment'];
            $attachmentData = [
                'path'         => $file->getPathName(),
                'originalName' => $file->getClientOriginalName(),
                'scope'        => $modelData['scope']
            ];

            $attachment = SaveModelAttachment::make()->action($model, $attachmentData);

        return $attachment;
    }

    public function rules(): array
    {
        if ($this->parent instanceof Employee) {
            $allowedScopes = ['Other', 'CV', 'Contract'];
        } elseif ($this->parent instanceof TradeUnit) {
            $allowedScopes = ['Other'];
        } elseif ($this->parent instanceof Supplier) {
            $allowedScopes = ['Other'];
        } elseif ($this->parent instanceof Customer) {
            $allowedScopes = ['Other'];
        } elseif ($this->parent instanceof PurchaseOrder) {
            $allowedScopes = ['Other'];
        } elseif ($this->parent instanceof StockDelivery) {
            $allowedScopes = ['Other'];
        } elseif ($this->parent instanceof Order) {
            $allowedScopes = ['Other'];
        } else {
            $allowedScopes = ['Other'];
        }

        return [
            'attachment' => ['required', 'file', 'max:50000'],
            'scope'      => [
                'required',
                'string',
                Rule::in($allowedScopes),
            ],
        ];
    }

    public function inEmployee(Employee $employee, ActionRequest $request)
    {
        $this->parent = $employee;
        $this->initialisation($employee->organisation, $request);
        return $this->handle($employee, $this->validatedData);
    }

    public function inTradeUnit(TradeUnit $tradeUnit, ActionRequest $request)
    {
        $this->parent = $tradeUnit;
        $this->initialisation($tradeUnit->organisation, $request);
        return $this->handle($tradeUnit, $this->validatedData);
    }

    public function inSupplier(Supplier $supplier, ActionRequest $request)
    {
        $this->parent = $supplier;
        $this->initialisation($supplier->organisation, $request);
        return $this->handle($supplier, $this->validatedData);
    }

    public function inCustomer(Customer $customer, ActionRequest $request)
    {
        $this->parent = $customer;
        $this->initialisation($customer->organisation, $request);
        return $this->handle($customer, $this->validatedData);
    }

    public function inPurchaseOrder(PurchaseOrder $purchaseOrder, ActionRequest $request)
    {
        $this->parent = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $request);
        return $this->handle($purchaseOrder, $this->validatedData);
    }

    public function inStockDelivery(StockDelivery $stockDelivery, ActionRequest $request)
    {
        $this->parent = $stockDelivery;
        $this->initialisation($stockDelivery->organisation, $request);
        return $this->handle($stockDelivery, $this->validatedData);
    }

    public function inOrder(Order $order, ActionRequest $request)
    {
        $this->parent = $order;
        $this->initialisation($order->organisation, $request);
        return $this->handle($order, $this->validatedData);
    }
}

