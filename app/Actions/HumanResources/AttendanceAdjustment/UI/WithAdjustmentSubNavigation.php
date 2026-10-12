<?php

namespace App\Actions\HumanResources\AttendanceAdjustment\UI;

use Lorisleiva\Actions\ActionRequest;

trait WithAdjustmentSubNavigation
{
    protected function getAdjustmentSubNavigation(ActionRequest $request): array
    {
        return [
            [
                'label'    => __('Dashboard'),
                'route'    => [
                    'name'       => 'grp.org.hr.adjustments.dashboard',
                    'parameters' => $request->route()->originalParameters(),
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-tachometer-alt'],
                    'tooltip' => __('Dashboard'),
                ],
            ],
            [
                'label'    => __('Clocking adjustment'),
                'route'    => [
                    'name'       => 'grp.org.hr.adjustments.index',
                    'parameters' => $request->route()->originalParameters(),
                ],
                'leftIcon' => [
                    'icon'        => ['fal', 'fa-clock'],
                    'icon_layers' => [['fal', 'fa-clock'], ['fal', 'fa-pencil']],
                    'tooltip'     => __('Clocking adjustment'),
                ],
            ],
        ];
    }
}
