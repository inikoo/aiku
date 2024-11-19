<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Actions\Comms\PostRoom\UI\ShowPostRoom;
use App\Actions\UI\Marketing\MarketingHub;
use App\Models\Comms\Outbox;

trait HasUIOutbox
{
    public function getBreadcrumbs(string $routeName, Outbox $outbox): array
    {
        $headCrumb = function (array $routeParameters = []) use ($outbox, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $outbox->id,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('outboxes list')
                    ],
                    'modelLabel' => [
                        'label' => __('outboxes')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'mail.outboxes.show' => array_merge(
                (new MarketingHub())->getBreadcrumbs(),
                $headCrumb([$outbox->slug])
            ),
            'mail.post_rooms.show.outboxes.show' => array_merge(
                (new ShowPostRoom())->getBreadcrumbs($outbox->parent),
                $headCrumb([$outbox->id, $outbox->slug])
            ),
            default => []
        };
    }
}
