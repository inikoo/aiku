<?php

namespace Tests\Feature;

use App\Actions\Central\Group\StoreGroup;
use App\Models\Central\Group;
use Database\Seeders\RestoreDatabaseSeeder;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;


    protected $seeder = RestoreDatabaseSeeder::class;

    public function test_create_group()
    {
        $this->artisan('create:group aw awa USD')->assertOk();
    }

/*    public function test_create_group_without_correct_currency()
    {
        $this->artisan('create:group aq awq XXX')->assertFailed();
    }

    public function test_create_group_without_currency()
    {
        $this->artisan('create:group az awz ')->assertFailed();
    }

    public function test_create_group_without_name()
    {
        $this->artisan('create:group  pa  USD')->assertFailed();
    }

    public function test_create_group_without_code()
    {
        $this->artisan('create:group  apz USD')->assertFailed();
    }

    public function test_duplicate_groups()
    {
        $this->artisan('create:group aw awa USD')->assertFailed();
    }*/

    public function restoreDatabase(): void
    {
        exec('pg_restore -U aiku -c -d aiku_test ./devops/devel/snapshots/seeded-central-db.dump');
    }
}
