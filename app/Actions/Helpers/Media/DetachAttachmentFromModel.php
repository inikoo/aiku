<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 17 Oct 2024 11:00:03 Central Indonesia Time, Office, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
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
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DetachAttachmentFromModel extends OrgAction
{
    use AsAction;

    public function handle(Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $model, Media $attachment): Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order
    {
        $model->attachments()->detach($attachment->id);


        return $model;
    }


    public function action(
        Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $model,
        Media $attachment,
        int $hydratorsDelay = 0,
        bool $strict = true
    ): Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationForGroup($model->group, []);

        return $this->handle($model, $attachment);
    }

    public function authorize(ActionRequest $request)
    {
        return true;
    }

    public function inEmployee(Employee $employee, Media $attachment)
    {
        $this->initialisation($employee->organisation, []);
        return $this->handle($employee, $attachment);
    }

}
