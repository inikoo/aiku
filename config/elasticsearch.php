<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 14:52:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


return [
    'hosts'       => explode(',', env('ELASTICSEARCH_HOSTS', 'localhost:9200')),
    'index_prefix'=> env('ELASTICSEARCH_INDEX_PREFIX', 'aiku_')
];
