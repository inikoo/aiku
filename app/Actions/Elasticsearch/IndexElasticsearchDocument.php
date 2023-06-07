<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 21:34:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use Lorisleiva\Actions\Concerns\AsObject;

class IndexElasticsearchDocument
{
    use AsObject;

    public function handle(string $index, array $body)
    {
        $index = config('elasticsearch.index_prefix').$index;

        if ($client = BuildElasticsearchClient::run()) {
            try {
                $res = $client->index(
                    [
                        'index' => $index,
                        'body'  => $body
                    ]
                );

                return true;
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
        }

        return false;
    }

}
