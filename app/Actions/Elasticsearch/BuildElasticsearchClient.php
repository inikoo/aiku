<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Exception;
use Lorisleiva\Actions\Concerns\AsObject;

class BuildElasticsearchClient
{
    use AsObject;

    public function handle(): Client|Exception
    {

        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts(config('elasticsearch.hosts'));
        if(config('elasticsearch.api_key')) {
            $clientBuilder->setApiKey(config('elasticsearch.api_key'));
        }

        try {
            $client = $clientBuilder->build();
            $client->ping();

            return $client;
        } catch(Exception $e) {
            return $e;
        }

    }

}
