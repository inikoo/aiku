<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 09 Jun 2023 10:26:06 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Actions\Elasticsearch\BuildElasticsearchClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;

trait ElasticSearchAuditable
{
    public function esAudits($page = 1, $perPage = 10, $sort = 'latest'): array
    {
        $client = BuildElasticsearchClient::run();
        $index  = config('elasticsearch.index_prefix') . 'history_'.app('currentTenant')->group->slug;
        $type   = Config::get('audit.drivers.es.type', 'ACTION');

        $from  = ($page - 1) * $perPage;
        $order = $sort === 'latest' ? 'desc' : 'asc';

        $params = [
            'index' => $index,
            'type'  => $type,
            'size'  => $perPage,
            'from'  => $from,
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'auditable_id' => $this->id
                                ]
                            ],
                            [
                                'term' => [
                                    'auditable_type' => $this->getMorphClass()
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => [
                    'created_at' => [
                        'order' => $order
                    ]
                ],
                'track_scores' => true
            ]
        ];

        $results = $client->search($params);
        $hits    = $results['hits'];

        $collection = Collection::make();

        foreach ($hits['hits'] as $key => $result) {
            $audit['id']    = $result['_id'];
            $audit          = array_merge($audit, $result['_source']);
            $audit['score'] = $result['_score'];

            $collection->put($key, $audit);
        }

        return [
            'total'    => $hits['total'],
            'per_page' => $perPage,
            'data'     => $collection
        ];
    }

    public function getEsAuditsAttribute(): array
    {
        return $this->esAudits();
    }
}
