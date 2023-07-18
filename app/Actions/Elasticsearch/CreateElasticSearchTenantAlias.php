<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use App\Models\Tenancy\Tenant;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateElasticSearchTenantAlias
{
    use AsAction;

    public string $commandSignature = 'es:tenant-alias {tenant}';

    public function getCommandDescription(): string
    {
        return 'Create Universal Search tenant aliases';
    }

    public function handle()
    {
        $tenant =app('currentTenant');
        $client = BuildElasticsearchClient::run();

        $params['body'] = array(
            'actions' => array(
                array(
                    'add' => array(
                        'index'   => config('app.name').'_search',
                        'alias'   => config('app.name').'_search'.'_'.$tenant->slug,
                        'routing' => $tenant->slug,
                        'filter'  => [
                            'term' => [
                                "tenant_id" => $tenant->id
                            ]
                        ]

                    )
                )
            )
        );
        return  $client->indices()->updateAliases($params);

    }


    public function asCommand(Command $command): int
    {
        $tenant = Tenant::where('slug', $command->argument('tenant'))->firstOrFail();
        $tenant->makeCurrent();

        $response=$this->handle();

        if ($response['acknowledged']) {
            $command->line("Alias added 🫡");

            return 0;
        }

        return 1;
    }
}
