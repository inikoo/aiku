<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Aug 2024 12:57:59 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Incoming;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowIncomingHub extends OrgAction
{
    use AsAction;
    use WithInertia;

    public function handle($scope)
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("incoming.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation): Organisation
    {
        return $this->handle($organisation);
    }


    public function htmlResponse(Organisation|Shop $scope, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Incoming/IncomingHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => 'incoming',
                'pageHead'    => [
                    'icon'  => [
                        'icon'  => ['fal', 'fa-arrow-to-bottom'],
                        'title' => __('incoming')
                    ],
                    'title' => __('Goods in backlog'),
                ],


            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowDashboard::make()->getBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.incoming.backlog',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('Goods in'),
                    ]
                ]
            ]
        );
    }

}
