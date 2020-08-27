<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 27 Aug 2020 14:16:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FetchIpGeolocationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:ip_geolocations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch pending geolocation data for the ip in ip_geolocations table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
