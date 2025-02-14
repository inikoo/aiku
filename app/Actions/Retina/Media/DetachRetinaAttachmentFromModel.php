<?php
/*
 * author Arya Permana - Kirin
 * created on 14-02-2025-11h-20m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Media;

use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Media;
use App\Models\HumanResources\Employee;
use App\Models\Ordering\Order;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\StockDelivery;
use App\Models\SupplyChain\Supplier;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DetachRetinaAttachmentFromModel extends RetinaAction
{
    public function handle(PalletDelivery|PalletReturn $model, Media $attachment): PalletDelivery|PalletReturn
    {
        $model->attachments()->detach($attachment->id);


        return $model;
    }

    public function inPalletDelivery(PalletDelivery $palletDelivery, Media $attachment, ActionRequest $request): void
    {
        $this->initialisation($request);

        $this->handle($palletDelivery, $attachment);
    }

    public function inPalletReturn(PalletReturn $palletReturn, Media $attachment, ActionRequest $request): void
    {
        $this->initialisation($request);

        $this->handle($palletReturn, $attachment);
    }
}
