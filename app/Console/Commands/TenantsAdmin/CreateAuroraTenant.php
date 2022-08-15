<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 17:41:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Console\Commands\TenantsAdmin;

use Illuminate\Console\Command;

class CreateAuroraTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:aurora {--aurora_db=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crete new Aurora tenant';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
