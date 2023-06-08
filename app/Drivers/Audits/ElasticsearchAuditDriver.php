<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 08 Jun 2023 17:00:48 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Drivers\Audits;

use OwenIt\Auditing\Contracts\Audit;
use OwenIt\Auditing\Contracts\Auditable;

class ElasticsearchAuditDriver
{
    /**
     * @var string|null
     */
    protected ?string $client = null;

    /**
     * @var string|null
     */
    protected ?string $index = null;

    /**
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * ElasticSearch constructor.
     */
    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(Config::get('audit.drivers.es.client.hosts', ['localhost:9200']))->build();
        $this->index  = Config::get('audit.drivers.es.index', 'laravel_auditing');
        $this->type   = Config::get('audit.drivers.es.type', 'audits');
    }

    /**
     * Perform an audit.
     *
     * @param \OwenIt\Auditing\Contracts\Auditable $model
     *
     * @return \OwenIt\Auditing\Contracts\Audit
     */
    public function audit(Auditable $model): Audit
    {
        $implementation = Config::get('audit.implementation', AuditModel::class);

        $this->storeAudit($model->toAudit());

        return new $implementation();
    }

    /**
     * Remove older audits that go over the threshold.
     *
     * @param \OwenIt\Auditing\Contracts\Auditable $model
     *
     * @return bool
     */
    public function prune(Auditable $model): bool
    {
        if ($model->getAuditThreshold() > 0) {
            return $this->destroyAudit($model);
        }

        return false;
    }

    public function storeAudit($model)
    {
        $model['created_at'] = Carbon::now()->toDateTimeString();

        if (Config::get('audit.queue', false)) {
            return $this->indexQueueAuditDocument($model);
        }

        return $this->indexAuditDocument($model);
    }

    public function indexQueueAuditDocument($model)
    {
        dispatch((new AuditIndexQueuedModels($model))
            ->onQueue($this->syncWithSearchUsingQueue())
            ->onConnection($this->syncWithSearchUsing()));

        return true;
    }

    public function destroyAudit($model)
    {
        if (Config::get('audit.queue', false)) {
            return $this->deleteQueueAuditDocument($model);
        }

        return $this->deleteAuditDocument($model);
    }

    public function deleteQueueAuditDocument($model)
    {
        dispatch((new AuditDeleteQueuedModels($model))
            ->onQueue($this->syncWithSearchUsingQueue())
            ->onConnection($this->syncWithSearchUsing()));

        return true;
    }

    /**
     * Get the queue that should be used with syncing
     *
     * @return  string
     */
    public function syncWithSearchUsingQueue()
    {
        return config('audit.queue.queue');
    }

    /**
     * Get the queue connection that should be used when syncing.
     *
     * @return string
     */
    public function syncWithSearchUsing()
    {
        return config('audit.queue.connection') ?: config('queue.default');
    }

    public function indexAuditDocument($model)
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => Uuid::uuid4(),
            'body'  => $model
        ];

        try {
            return $this->client->index($params);
        } catch (\Exception $e) {
        }
    }

    public function searchAuditDocument($model)
    {
        $skip = $model->getAuditThreshold() - 1;

        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'size'  => 10000 - $skip,
            'from'  => $skip,
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'auditable_id' => $model->id
                                ]
                            ],
                            [
                                'term' => [
                                    'auditable_type' => $model->getMorphClass()
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => [
                    'created_at' => [
                        'order' => 'desc'
                    ]
                ]
            ]
        ];

        return $this->client->search($params);
    }

    public function deleteAuditDocument($model)
    {
        $audits = $this->searchAuditDocument($model);
        $audits = $audits['hits']['hits'];

        if (count($audits)) {
            $audit_ids = array_column($audits, '_id');

            foreach ($audit_ids as $audit_id) {
                $params['body'][] = [
                    'delete' => [
                        '_index' => $this->index,
                        '_type'  => $this->type,
                        '_id'    => $audit_id
                    ]
                ];

            }

            return (bool) $this->client->bulk($params);
        }

        return false;
    }

    public function createIndex()
    {
        $params = [
            'index' => $this->index,
            'body'  => [
                'settings' => [
                    'number_of_shards'   => 3,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        return $this->client->indices()->create($params);
    }

    public function updateAliases()
    {
        $params['body'] = [
            'actions' => [
                [
                    'add' => [
                        'index' => $this->index,
                        'alias' => $this->index.'_write'
                    ]
                ]
            ]
        ];

        return $this->client->indices()->updateAliases($params);
    }

    public function deleteIndex()
    {
        $deleteParams = [
            'index' => $this->index
        ];

        return $this->client->indices()->delete($deleteParams);
    }

    public function existsIndex()
    {
        $params = [
            'index' => $this->index
        ];

        return $this->client->indices()->exists($params);
    }

    public function putMapping()
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => [
                $this->type => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'event' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed'
                        ],
                        'auditable_type' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed'
                        ],
                        'ip_address' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed'
                        ],
                        'url' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed'
                        ],
                        'user_agent' => [
                            'type'  => 'string',
                            'index' => 'not_analyzed'
                        ],
                        'created_at' => [
                            'type'   => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss'
                        ],
                        'new_values' => [
                            'properties' => [
                                'created_at' => [
                                    'type'   => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                ],
                                'updated_at' => [
                                    'type'   => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                ],
                                'deleted_at' => [
                                    'type'   => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                ]
                            ]
                        ],
                        'old_values' => [
                            'properties' => [
                                'created_at' => [
                                    'type'   => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                ],
                                'updated_at' => [
                                    'type'   => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                ],
                                'deleted_at' => [
                                    'type'   => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->client->indices()->putMapping($params);
    }
}
