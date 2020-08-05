<?php

use Illuminate\Database\Seeder;
use Spatie\Multitenancy\Models\Tenant as Tenanto;
class DatabaseSeeder extends Seeder
{


    public function run()
    {
        Tenanto::checkCurrent()
            ? $this->runTenantSpecificSeeders()
            : $this->runLandlordSpecificSeeders();
    }

    public function runTenantSpecificSeeders()
    {
        $this->call(EmployeeSeeder::class);
<<<<<<< Updated upstream
       // $this->call(UserSeeder::class);
=======
        $this->call(WarehouseSeeder::class);
        $this->call(StoreSeeder::class);

>>>>>>> Stashed changes

    }

    public function runLandlordSpecificSeeders()
    {
        $this->call(TenantSeeder::class);
    }


}
