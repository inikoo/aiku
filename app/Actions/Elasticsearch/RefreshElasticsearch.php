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


    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $indices = ['search', 'retina_search', 'iris_search', 'content_blocks', 'web_users_requests', 'users_requests'];

        $client = BuildElasticsearchClient::run();
        if ($client instanceof Exception) {
            throw $client;
        } else {
            foreach ($indices as $index) {
                $params = [
                    'index' => config('elasticsearch.index_prefix').$index
                ];

                $response = $client->indices()->exists($params);

                if ($response->getStatusCode() != 404) {
                    $client->indices()->delete($params);
                }

                $client->indices()->create($params);
            }
        }
    }

    public function asCommand(Command $command): int
    {
        try {
            $this->handle();

            return 0;
        } catch (Exception $exception) {
            $msg = $exception->getMessage();
            $command->error("Elasticsearch indices refresh failed ($msg)");

            return 1;
        }
    }
}
