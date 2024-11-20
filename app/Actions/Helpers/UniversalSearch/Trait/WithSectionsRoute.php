<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Helpers\UniversalSearch\Trait;

trait WithSectionsRoute
{
    public function parseSections($routeName): array|null
    {
        if (str_starts_with($routeName, 'grp.org.')) {
            return $this->parseOrganisationSections(
                preg_replace('/^grp\.org./', '', $routeName)
            );
        }
        return null;
    }

    public function parseOrganisationSections($route): array|null
    {
        $routes = [
            'accounting.' => ['accounting'],
            // 'productions.' => ['productions'],
            'procurement.' => ['procurement'],
            'websites.' => ['web'],
            'fulfilments.show.web.' => ['web'],
            'fulfilments.' => ['fulfilment'],
            'shops.show.billables.' => ['billables'],
            // 'reports.' => ['reports'],
            'shops.show.catalogue.' => ['catalogue'],
            // 'shops.show.mail.' => ['mail'],
            // 'shops.show.marketing.' => ['marketing'],
            'shops.show.discounts.' => ['discounts'],
            'shops.show.ordering.' => ['ordering', 'dispatching'],
            'shops.show.web.' => ['web'],
            'shops.show.crm.' => ['crm'],
            // 'shops.' => ['assets', 'catalogue', 'mail', 'marketing', 'discounts', 'ordering', 'dispatching', 'web', 'crm', 'billables'],
            'shops.' => ['billables', 'catalogue', 'discounts', 'ordering', 'dispatching', 'web', 'crm'],
            'hr.' => ['hr'],
            'warehouses.show.infrastructure.' => ['infrastructure'],
            'warehouses.show.dispatching' => ['dispatching'],
            'warehouses.' => ['infrastructure', 'inventory', 'dispatching']
        ];

        if (empty($route) || str_starts_with($route, "dashboard.") || str_starts_with($route, "settings.")) {
            return array_unique(array_merge(...array_values($routes)));
        }

        foreach ($routes as $prefix => $result) {
            if (str_starts_with($route, $prefix)) {
                return $result;
            }
        }


        return null;
    }
}
