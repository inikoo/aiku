<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Market\Shop\UI\IndexShops;
use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Enums\UI\CustomerTabsEnum;
use App\Enums\UI\InvoiceTabsEnum;
use App\Http\Resources\Accounting\InvoicesResource;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowInvoice extends OrgAction
{
    use AsAction;
    use WithInertia;


    private Organisation|Fulfilment|Shop $parent;

    public function handle(Invoice $invoice): Invoice
    {
        return $invoice;
    }

    public function authorize(ActionRequest $request): bool
    {

        if($this->parent instanceof Organisation) {
            return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
        } elseif($this->parent instanceof Shop) {
            //todo think about it
            return false;
        } elseif($this->parent instanceof Fulfilment) {
            return $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.view");
        }
        return false;
    }

    public function inOrganisation(Organisation $organisation, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent=$organisation;
        $this->initialisation($organisation, $request)->withTab(CustomerTabsEnum::values());

        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent=$shop;
        $this->initialisationFromShop($shop, $request)->withTab(CustomerTabsEnum::values());
        return $this->handle($invoice);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Invoice $invoice, ActionRequest $request): Invoice
    {
        $this->parent=$fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(CustomerTabsEnum::values());
        return $this->handle($invoice);
    }

    public function htmlResponse(Invoice $invoice, ActionRequest $request): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Org/Accounting/Invoice',
            [
                'title'                                 => __('invoice'),
                'breadcrumbs'                           => $this->getBreadcrumbs($invoice),
                'navigation'                            => [
                    'previous' => $this->getPrevious($invoice, $request),
                    'next'     => $this->getNext($invoice, $request),
                ],
                'pageHead'    => [
                    'title' => $invoice->number,


                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => InvoiceTabsEnum::navigation()
                ],

                InvoiceTabsEnum::SHOWCASE->value => $this->tab == InvoiceTabsEnum::SHOWCASE->value ?
                    fn () => GetInvoiceShowcase::run($invoice)
                    : Inertia::lazy(fn () => GetInvoiceShowcase::run($invoice)),

                InvoiceTabsEnum::PAYMENTS->value => $this->tab == InvoiceTabsEnum::PAYMENTS->value ?
                    fn () => PaymentsResource::collection(IndexPayments::run($invoice))
                    : Inertia::lazy(fn () => PaymentsResource::collection(IndexPayments::run($invoice))),



            ]
        )->table(IndexPayments::make()->tableStructure($invoice));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->hasPermissionTo('hr.edit'));
        $this->set('canViewUsers', $request->user()->hasPermissionTo('users.view'));
    }

    #[Pure] public function jsonResponse(Invoice $invoice): InvoicesResource
    {
        return new InvoicesResource($invoice);
    }


    public function getBreadcrumbs(Invoice $invoice): array
    {
        //TODO Pending
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'grp.shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $invoice->id,
                    'name'            => $invoice->number,
                    'index'           => [
                        'route'   => 'grp.org.shops.index',
                        'overlay' => __('Invoices list')
                    ],
                    'modelLabel' => [
                        'label' => __('invoice')
                    ],
                ],
            ]
        );
    }

    public function getPrevious(Invoice $invoice, ActionRequest $request): ?array
    {
        $previous = Invoice::where('number', '<', $invoice->number)->orderBy('number', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Invoice $invoice, ActionRequest $request): ?array
    {
        $next = Invoice::where('number', '>', $invoice->number)->orderBy('number')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Invoice $invoice, string $routeName): ?array
    {
        if(!$invoice) {
            return null;
        }
        return match ($routeName) {
            'grp.org.accounting.invoices.show'=> [
                'label'=> $invoice->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'invoice'=> $invoice->slug
                    ]

                ]
            ]
        };
    }
}
