<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 14:50:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */


return
    [
        'supervisor' => [
            'admin_users',
            'look-and-field',
            'deliveries',
        ],
        'observer'   => [
            'deliveries',
            'users.view',
        ],
        'picker'      => [
            'deliveries',

        ],
        'packer'      => [
            'deliveries',
        ],
        'guest'      => [
            'deliveries.view',
        ],

    ];


