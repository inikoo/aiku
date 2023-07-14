<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use Exception;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsObject;

class DeleteAllElasticsearchDocument
{
    use AsObject;
    use AsAction;

    public string $commandSignature   = 'elasticsearch:flush';
    public string $commandDescription = 'Delete the all data elasticsearch';

    public function handle(): ?bool
    {
        $client = BuildElasticsearchClient::run();

        try {
            $response = $client->deleteByQuery([
                'index' => '_all',
                'body' => [
                    'query' => [
                        'match_all' => new \stdClass(),
                    ],
                ],
            ]);

            if ($response['deleted'] >= 0) {
                echo "🧼 Successfully {$response['deleted']} data deleted \n";

                return true;
            }

            echo "Delete data failed \n";

            return false;
        } catch(Exception) {
            echo "Delete data failed \n";

            return false;
        }
    }

    public function asCommand(): ?bool
    {
        return $this->handle();
    }
}
