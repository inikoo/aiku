<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Accounting\InvoiceResource;
use App\Models\Accounting\Invoice;
use App\Models\Marketing\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowInvoice extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Invoice $invoice): Invoice
    {
        return $invoice;
    }

    public function authorize(ActionRequest $request): bool
    {
        //
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Invoice $invoice): Invoice
    {
        return $this->handle($invoice);
    }

    public function inShop(Shop $shop, Invoice $invoice, Request $request): Invoice
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($invoice);
    }

    public function htmlResponse(Invoice $invoice): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Invoice',
            [
                'title'       => __('invoice'),
                'breadcrumbs' => $this->getBreadcrumbs($invoice),
                'pageHead'    => [
                    'title' => $invoice->number,


                ],
                'invoice' => new InvoiceResource($invoice),
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice);
    }


    public function getBreadcrumbs(Invoice $invoice): array
    {
        //TODO Pending
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $invoice->id,
                    'name'            => $invoice->number,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Invoices list')
                    ],
                    'modelLabel' => [
                        'label' => __('invoice')
                    ],
                ],
            ]
        );
    }
}
