<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Thu, 27 Aug 2020 14:16:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Console\Commands;

use Exception;
use App\Models\Helpers\UserAgent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RelocateUserAgents extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relocate:user_agents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy ip user agent';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {

        //comment fetch_user_agent_device_data function in UserAgent class

        DB::connection('mysql');
        $table='`User Agent`';

        $res = DB::connection('mysql')->select('select count(*) as num from '.$table, []);

        $bar = $this->output->createProgressBar($res[0]->num);
        $bar->setFormat('debug');

        foreach (DB::connection('mysql')->select('select * from '.$table, []) as $legacy_data) {

            try {
                $raw_data   = json_decode($legacy_data->Data, true);
                $data       = $raw_data['parse'];
                $user_agent = new UserAgent;

                $user_agent->skip_fetch_user_agent_device_data = true;

                $user_agent->user_agent = $data['user_agent'];
                $user_agent->checksum   = md5(strtolower($data['user_agent']));


                $user_agent->description = $data['simple_software_string'];
                $user_agent->os_code     = $data['operating_system_name_code'];
                $user_agent->software    = $data['software_name'];

                unset($data['user_agent']);
                unset($data['simple_software_string']);
                unset($data['software_name']);
                unset($data['operating_system_name_code']);

                $user_agent->status = 'OK';
                $user_agent->save();
                $user_agent->data = $data;
                $user_agent->save();
                $user_agent->device_type = $user_agent->get_user_agent_device_type();
                $user_agent->save();
                $bar->advance();
            } catch (Exception $e) {
                $bar->advance();
            }


        }
        $bar->finish();
        print "\n";

        return 0;


    }

    public function set_legacy_connection($database_name) {


        $database_settings = data_get(config('database.connections'), 'landlord');

        data_set($database_settings, 'database', $database_name);
        config(['database.connections.legacy' => $database_settings]);
        DB::connection('legacy');

    }
}
