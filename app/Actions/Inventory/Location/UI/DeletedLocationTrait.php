<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Jun 2023 11:15:18 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location\UI;

use App\Enums\UI\LocationTabsEnum;
use App\Models\Inventory\Location;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

trait DeletedLocationTrait
{
    public function deletedHtmlResponse(Location $location, ActionRequest $request): Response
    {

        return Inertia::render(
            'Inventory/DeletedLocation',
            [
                'title'                                    => __("location"),
                'breadcrumbs'                              => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'    => [
                    'previous'  => $this->getPrevious($location, $request),
                    'next'      => $this->getNext($location, $request),
                ],
                'pageHead'                                 => [
                    'icon'          =>
                        [
                            'icon'  => ['fal', 'fa-inventory'],
                            'title' => __('location')
                        ],
                    'title'  => $location->slug,



                ],
                'tabs'                                     => [
                    'current'    => $this->tab,
                    'navigation' => LocationTabsEnum::navigation()
                ],
                LocationTabsEnum::SHOWCASE->value => $this->tab == LocationTabsEnum::SHOWCASE->value ?
                    fn () => GetLocationShowcase::run($location)
                    : Inertia::lazy(fn () => GetLocationShowcase::run($location)),

            ]
        );
    }

}
