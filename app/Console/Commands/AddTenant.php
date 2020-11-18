<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Mon, 28 Sep 2020 18:18:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use App\Tenant;
use Illuminate\Console\Command;

class AddTenant extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:tenant {type} {name} {slug} {data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create tenant";


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {


        $tenant = new Tenant(
            [
                'name'     => $this->argument('name'),
                'slug'     => $this->argument('slug'),
                'type'     => $this->argument('type'),
                'data' => json_decode($this->argument('data'))
            ]
        );

        $tenant->save();


        return 0;


    }


}


