<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 14:12:54 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Dashboard;

use App\Models\CRM\Customer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDashboard
{
    use AsAction;


    protected bool $canEditBanners = false;


    public function handle(Customer $customer, ActionRequest $request): array
    {


        return [];
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEditBanners = $request->user()->hasPermissionTo('portfolio.banners.edit');
        return $request->user()->hasPermissionTo('portfolio.banners.view');

    }


    public function asController(ActionRequest $request): Response
    {
        $request->validate();
        $customer = $request->get('customer');
        $data     = $this->handle($customer, $request);

        return Inertia::render('Dashboard', $data);
    }

    public function getBreadcrumbs($label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-home',
                    'label' => $label,
                    'route' => [
                        'name' => 'retina.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
