<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Mail\Mailshot\UI;

use App\Actions\Mail\PostRoom\UI\ShowPostRoom;
use App\Actions\UI\Marketing\MarketingHub;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
use App\Models\SysAdmin\Organisation;

trait HasUIMailshots
{
    public function getBreadcrumbs(string $routeName, array $routeParameters, Outbox|PostRoom|Organisation $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('mailshot')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.marketing.mailshots.index' =>
            array_merge(
                (new MarketingHub())->getBreadcrumbs($routeName, $routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.marketing.mailshots.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('mailshots'),
                            'icon'  => 'fal fa-bars'
                        ],
                    ],
                ]
            ),
            'mail.post_rooms.show.mailshots.show' =>
            array_merge(
                (new ShowPostRoom())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}
