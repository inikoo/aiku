<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Reports\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithAccountingReportsAuthorisation;
use App\Actions\UI\Reports\IndexReports;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexCostOfGoodsSoldReport extends OrgAction
{
    use WithAccountingReportsAuthorisation;

    public function asController(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->initialisation($organisation, $request);

        return $organisation;
    }

    public function htmlResponse(Organisation $organisation, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Reports/CostOfGoodsSoldReport',
            [
                'breadcrumbs'   => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'         => __('Cost of goods sold'),
                'pageHead'      => [
                    'icon'  => [
                        'icon'  => ['fal', 'fa-coins'],
                        'title' => __('Cost of goods sold'),
                    ],
                    'title' => __('Cost of goods sold'),
                ],
                'downloadRoute' => [
                    'name'       => 'grp.org.reports.cogs.export',
                    'parameters' => $request->route()->originalParameters(),
                ],
            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexReports::make()->getBreadcrumbs('grp.org.reports.index', $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'icon'  => 'fal fa-coins',
                        'label' => __('Cost of goods sold'),
                        'route' => [
                            'name'       => 'grp.org.reports.cogs',
                            'parameters' => $routeParameters,
                        ],
                    ],
                ],
            ]
        );
    }
}
