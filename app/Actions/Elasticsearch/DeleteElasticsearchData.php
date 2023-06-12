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

class DeleteElasticsearchData
{
    use AsObject;
    use AsAction;

    public string $commandSignature   = 'elasticsearch:destroy {indexName}';
    public string $commandDescription = 'Delete the data based on index name';

    public function handle(string $indexName): ?bool
    {
        $client = BuildElasticsearchClient::run();

        try {
            $params = [
                'index' => $indexName
            ];

            $response = $client->indices()->delete($params);

            if ($response['acknowledged']) {
                echo "Data successfully deleted \n";

                return true;
            }

            echo "Delete data failed \n";

            return false;
        } catch(Exception) {
            echo "Delete data failed \n";

            return false;
        }
    }

    public function asCommand(Command $command): ?bool
    {
        return $this->handle($command->argument('indexName'));
    }
}
