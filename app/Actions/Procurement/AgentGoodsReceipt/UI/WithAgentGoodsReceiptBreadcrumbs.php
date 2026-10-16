<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:55
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentGoodsReceipt\UI;

use App\Actions\Procurement\UI\ShowProcurementDashboard;
use App\Models\Procurement\AgentGoodsReceipt;
use Lorisleiva\Actions\ActionRequest;

trait WithAgentGoodsReceiptBreadcrumbs
{
    protected function goodsReceiptBreadcrumbs(ActionRequest $request, ?AgentGoodsReceipt $receipt = null): array
    {
        $crumb = fn (string $label, string $routeName, array $parameters = []) => [
            'type'   => 'simple',
            'simple' => [
                'label' => $label,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [$this->organisation->slug, ...$parameters],
                ],
            ],
        ];

        return array_values(array_filter([
            ...ShowProcurementDashboard::make()->getBreadcrumbs($request->route()->originalParameters()),
            $crumb(__('Warehouse'), 'grp.org.agent.warehouse.index'),
            $crumb(__('Received'), 'grp.org.agent.warehouse.received.index'),
            $crumb(__('Goods receipts'), 'grp.org.agent.warehouse.received.goods_receipts.index'),
            $receipt ? $crumb($receipt->reference, 'grp.org.agent.warehouse.received.goods_receipts.show', [$receipt->slug]) : null,
        ]));
    }
}
