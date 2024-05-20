<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 21:34:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use App\Enums\Elasticsearch\ElasticsearchUserRequestTypeEnum;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Exception;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class IndexElasticsearchDocument
{
    use AsAction;
    use AsObject;

    public function handle(string $index, array $body, string $type = ElasticsearchUserRequestTypeEnum::VISIT->value, bool $isRestore = false): bool|Elasticsearch
    {
        $client = BuildElasticsearchClient::run();

        $params = [
            'index'  => $index,
            'type'   => $type,
            'synced' => !($client instanceof Exception),
            'body'   => $body
        ];

        try {
            if ($client instanceof Client) {
                return $client->index($params);
            }
        } catch (ClientResponseException $e) {
            //dd($e->getMessage());
            // manage the 4xx error
            return false;
        } catch (ServerResponseException $e) {
            //dd($e->getMessage());
            // manage the 5xx error
            return false;
        } catch (Exception $e) {
            //dd($e->getMessage());
            // eg. network error like NoNodeAvailableException
            return false;
        }

        return false;
    }

}
