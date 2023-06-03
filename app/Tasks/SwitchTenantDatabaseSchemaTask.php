<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 04 Mar 2023 13:10:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Tasks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Exceptions\InvalidConfiguration;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantDatabaseSchemaTask implements SwitchTenantTask
{
    use UsesMultitenancyConfig;

    /**
     * @throws \Spatie\Multitenancy\Exceptions\InvalidConfiguration
     */
    public function makeCurrent(Tenant $tenant): void
    {
        $this->setTenantConnectionDatabaseName($tenant->schema(), $tenant->group->schema());
    }

    /**
     * @throws \Spatie\Multitenancy\Exceptions\InvalidConfiguration
     */
    public function forgetCurrent(): void
    {
        $this->setTenantConnectionDatabaseName(null, null);
    }

    /**
     * @throws \Spatie\Multitenancy\Exceptions\InvalidConfiguration
     */
    protected function setTenantConnectionDatabaseName(?string $databaseName, ?string $groupSearchPath): void
    {
        $tenantConnectionName = $this->tenantDatabaseConnectionName();

        if ($tenantConnectionName === $this->landlordDatabaseConnectionName()) {
            throw InvalidConfiguration::tenantConnectionIsEmptyOrEqualsToLandlordConnection();
        }

        if (is_null(config("database.connections.$tenantConnectionName"))) {
            throw InvalidConfiguration::tenantConnectionDoesNotExist($tenantConnectionName);
        }


        config([
                   "database.connections.$tenantConnectionName.search_path" => $databaseName.' , extensions',
                   "database.connections.group.search_path"                 => $groupSearchPath.' , extensions'

        ]);
        app('db')->extend($tenantConnectionName, function ($config, $name) use ($databaseName) {

            $config['search_path'] = $databaseName.' , extensions';
            return app('db.factory')->make($config, $name);
        });

        DB::purge($tenantConnectionName);
        DB::purge('group');

        // Octane will have an old `db` instance in the Model::$resolver.
        Model::setConnectionResolver(app('db'));
    }
}
