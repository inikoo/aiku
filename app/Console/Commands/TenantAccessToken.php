<?php

namespace App\Console\Commands;

use App\Tenant;
use App\User;
use Illuminate\Console\Command;
use Spatie\Multitenancy\Commands\Concerns\TenantAware;

class TenantAccessToken extends Command
{
    use TenantAware;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:access_token {handle}  {--tenant=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an direct tenant access token';

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
     * @return integer
     */
    public function handle()
    {
        /**
         * @var $admin \App\Models\System\Admin
         */
        $admin= (new User)->firstWhere('userable_type', 'Admin')->userable;
        $token=$admin->createDirectAccessCode($this->argument('handle'));
        print ('The tenant is '. Tenant::current()->subdomain."\t\t".$token."\n");
        return 0;

    }
}
