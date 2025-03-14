<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Mar 2025 21:03:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Dashboards\Settings;

use Illuminate\Support\Arr;

trait WithDashboardModelStateSettings
{
    public function dashboardModelStateSettings(array $settings, string $align = 'right'): array
    {
        $id = 'model_state';

        return [
            'id'      => $id,
            'align'   => $align,
            'type'    => 'toggle',
            'value'   => Arr::get($settings, $id, 'open'),
            'options' => [
                [
                    'value' => 'open',
                    'label' => __('Open')
                ],
                [
                    'value' => 'closed',
                    'label' => __('Closed')
                ]
            ]
        ];
    }

}
