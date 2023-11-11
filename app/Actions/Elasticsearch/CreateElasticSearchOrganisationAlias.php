<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use App\Models\Organisation\Organisation;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateElasticSearchOrganisationAlias
{
    use AsAction;

    public string $commandSignature = 'es:organisation-alias {organisation}';

    public function getCommandDescription(): string
    {
        return 'Create Universal Search organisation aliases';
    }

    public function handle(Organisation $organisation)
    {
        $client = BuildElasticsearchClient::run();

        $params['body'] = array(
            'actions' => array(
                array(
                    'add' => array(
                        'index'   => config('app.universal_search_index'),
                        'alias'   => config('app.universal_search_index').'_'.$organisation->slug,
                        'routing' => $organisation->slug,
                        'filter'  => [
                            'term' => [
                                "organisation_id" => $organisation->id
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
        $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();

        $response=$this->handle($organisation);

        if ($response['acknowledged']) {
            $command->line("Alias added 🫡");

            return 0;
        }

        return 1;
    }
}
