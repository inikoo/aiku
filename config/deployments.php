<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 15 Aug 2022 18:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

return [

    'repo_path'=>(env('APP_ENV')=='local'?base_path('.git'):env('REPO_DIR'))
];

