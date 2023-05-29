<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 26 May 2023 13:25:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Traits;

use App\Models\Auth\User;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

trait WithElasticsearch
{
    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    public function init(): Client
    {
        return ClientBuilder::create()->build();
    }

    public function storeElastic(string $indexName, array $data = []): void
    {
        $data = array_merge($data, ['date' => now()]);

        $this->init()->index([
            'index' => $indexName,
            'id' => auth()->user()->id ?? rand(),
            'body' => $data
        ]);
    }
}
