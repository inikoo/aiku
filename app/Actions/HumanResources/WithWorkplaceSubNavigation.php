<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 26 May 2024 00:04:36 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources;

use App\Models\HumanResources\Workplace;

trait WithWorkplaceSubNavigation
{
    protected function getWorkplaceSubNavigation(Workplace $workplace): array
    {
        return [
            [
                'label'    => $workplace->name,
                'href'     => [
                    'name'       => 'grp.org.hr.workplaces.show',
                    'parameters' => [$this->organisation->slug, $workplace->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'building'],
                    'tooltip' => __('Workplace')
                ]
            ],
            [
                'label'    => __('Clocking machines'),
                'number'   => $workplace->stats->number_clocking_machines,
                'href'     => [
                    'name'       => 'grp.org.hr.workplaces.show.clocking_machines.index',
                    'parameters' => [$this->organisation->slug, $workplace->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'chess-clock'],
                    'tooltip' => __('clocking machines')
                ]
            ],
            [
                'label'    => __('Clockings'),
                'number'   => $workplace->stats->number_clockings,
                'href'     => [
                    'name'       => 'grp.org.hr.workplaces.show.clockings.index',
                    'parameters' => [$this->organisation->slug, $workplace->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'clock'],
                    'tooltip' => __('clockings')
                ]
            ]
        ];
    }

}
