<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Jul 2023 19:48:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Elasticsearch;

use App\Actions\Elasticsearch\BuildElasticsearchClient;
use App\Models\Web\WebpageVariant;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWebVariantElasticsearch
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(WebpageVariant $webpageVariant): Elasticsearch
    {
        $client = BuildElasticsearchClient::run();

        $params = [
            'index'  => strtolower($webpageVariant->slug),
            'body'   => json_encode($webpageVariant->components)
        ];

        return $client->index($params);
    }
}
