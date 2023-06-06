<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 13:08:39 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


return [
    [
        'webpage' => [
            'code'  => 'home',
            'purpose'  => 'structural',
            'type' => 'storefront',
        ],
        'webpage-variant' => [
            'components' => [
                'three-column' => [
                    'title'       => 'Shop by Collection',
                    'description' => 'Each season, we collaborate with world-class designers to create a collection inspired by the natural world.',
                    'categories'  => [
                        [
                            'name'        => 'Handcrafted Collection',
                            'href'        => '#',
                            'imageSrc'    => 'https://tailwindui.com/img/ecommerce-images/home-page-01-collection-01.jpg',
                            'imageAlt'    => 'Brown leather key ring with brass metal loops and rivets on wood table.',
                            'description' => 'Keep your phone, keys, and wallet together, so you can lose everything at once.',
                        ],
                        [
                            'name'        => 'Organized Desk Collection',
                            'href'        => '#',
                            'imageSrc'    => 'https://tailwindui.com/img/ecommerce-images/home-page-01-collection-02.jpg',
                            'imageAlt'    => 'Natural leather mouse pad on white desk next to porcelain mug and keyboard.',
                            'description' => 'The rest of the house will still be a mess, but your desk will look great.',
                        ],
                        [
                            'name'        => 'Focus Collection',
                            'href'        => '#',
                            'imageSrc'    => 'https://tailwindui.com/img/ecommerce-images/home-page-01-collection-03.jpg',
                            'imageAlt'    => 'Person placing task list card into walnut card holder next to felt carrying case on leather desk pad.',
                            'description' => 'Be more productive than enterprise project managers with a single piece of paper.',
                        ]
                    ]
                ]
            ]
        ]
    ]
];
