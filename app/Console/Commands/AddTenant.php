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
    protected $signature = 'add:tenant {name} {subdomain} {database} {legacy_db} {legacy_code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create tenant";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {


        $tenant = new Tenant(
            [
                'name'      => $this->argument('name'),
                'subdomain' => $this->argument('subdomain'),
                'database'  => $this->argument('database'),
                'data'  => [
                    'legacy' => [
                        'db'   => $this->argument('legacy_db'),
                        'code' => $this->argument('legacy_code')
                    ]
                ]
            ]
        );

        $tenant->save();


        return 0;


    }


}


