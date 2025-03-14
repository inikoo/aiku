<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Nov 2023 10:49:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query\Seeders;

use App\Actions\Helpers\Query\Hydrators\QueryHydrateCount;
use App\Actions\Helpers\Query\StoreQuery;
use App\Actions\Helpers\Query\UpdateQuery;
use App\Actions\Traits\WithShopsArgument;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Prospect;
use App\Models\Helpers\Query;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class ProspectQuerySeeder
{
    use AsAction;
    use WithShopsArgument;

    /**
     * @throws \Exception
     */
    public function handle(Shop $shop): void
    {
        $data = [
            [
                'seed_code'       => 'prospects-not-contacted',
                'name'       => 'Prospects not contacted',
                'model' => class_basename(Prospect::class),
                'constrains' => [
                    'can_contact_by' =>
                        [
                            'logic'  => 'all',
                            'fields' => ['email']
                        ],
                    'prospect_last_contacted' => [
                        'state'     => false,
                        'argument'  => []
                    ]

                ],
            ],
            [
                'seed_code'       => 'prospects-last-contacted',
                'name'       => 'Prospects last contacted (within interval)',
                'model' => class_basename(Prospect::class),
                'constrains' => [
                    'can_contact_by'          =>
                        [
                            'logic'  => 'any',
                            'fields' => ['email']
                        ],
                    'prospect_last_contacted' => [
                        'state'     => true,
                        'argument'  => [
                            'unit'     => 'week',
                            'quantity' => 1
                        ]

                    ]
                ]
            ],
        ];

        foreach ($data as $queryData) {
            $queryData['model_type']  = 'Prospect';

            if ($query = Query::where('seed_code', $queryData['seed_code'])->where('shop_id', $shop->id)->first()) {
                UpdateQuery::make()->action($query, $queryData);
            } else {
                $query = StoreQuery::make()->action($shop, $queryData);
            }
            QueryHydrateCount::run($query);
        }
    }

    public string $commandSignature = 'query:seed-prospects {shops?*}';

    /**
     * @throws \Exception
     */
    public function asCommand(Command $command): int
    {
        $exitCode = 0;

        foreach ($this->getShops($command) as $shop) {
            $this->handle($shop);
            $command->line("Queries seeded for $shop->name");
        }

        return $exitCode;
    }

}
