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
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class AttachAttachmentToModel extends OrgAction
{
    private Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $parent;

    public function handle(Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $model, array $modelData): Media
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
}
