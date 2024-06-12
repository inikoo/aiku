<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Outbox\UI;

use App\Actions\Mail\PostRoom\UI\ShowPostRoom;
use App\Actions\UI\Marketing\MarketingHub;
use App\Models\Mail\Outbox;

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
