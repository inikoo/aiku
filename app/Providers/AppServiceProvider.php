<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 23:16:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Providers;

use App\Managers\Organisation\OrganisationManager;
use App\Managers\Organisation\SourceOrganisationManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Lorisleiva\Actions\Facades\Actions;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(SourceOrganisationManager::class, function () {
            return new OrganisationManager();
        });
    }


    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            Actions::registerCommands();
        }

        Relation::morphMap(
            [
                'User' => 'App\Models\Organisations\User',
                'Employee' => 'App\Models\HumanResources\Employee',
            ]
        );
    }
}
