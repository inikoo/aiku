<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class RefreshElasticsearch
{
    use AsCommand;

    public string $commandSignature = 'es:refresh';

    public function getCommandDescription(): string
    {
        return 'Refresh app elasticsearch indices';
    }

    public function handle()
    {
        $client = BuildElasticsearchClient::run();

        $params = [
            'index' => config('app.universal_search_index')
        ];

        $response = $client->indices()->exists($params);

        if ($response->getStatusCode() != 404) {
            $client->indices()->delete($params);
        }

        return $client->indices()->create($params);
    }

    public function asCommand(Command $command): int
    {
        try {
            $response = $this->handle();
            if ($response['acknowledged']) {
                $command->line("Elasticsearch indices successfully refreshed 🔃");

                return 0;
            }

            return 1;
        } catch (Exception $exception) {
            $msg = $exception->getMessage();
            $command->error("Elasticsearch indices refresh failed ($msg)");

            return 1;
        }
    }
}
