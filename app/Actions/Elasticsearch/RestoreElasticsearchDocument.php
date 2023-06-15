<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Jun 2023 15:06:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Elasticsearch;

use App\Models\Backup\ActionHistory;
use App\Models\Backup\VisitHistory;
use Exception;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;

class RestoreElasticsearchDocument
{
    use AsObject;
    use AsAction;

    public string $commandSignature = 'elasticsearch:restore';
    public string $commandDescription = 'Restore the data from backup database';

    public function handle(): void
    {
        try {
            $actionHistories = ActionHistory::orderBy('id', 'ASC')->get();
            foreach ($actionHistories as $actionHistory) {
                IndexElasticsearchDocument::dispatch($actionHistory->index, $actionHistory->body, $actionHistory->type, true);
            }

            $visitHistories = VisitHistory::orderBy('id', 'ASC')->get();
            foreach ($visitHistories as $visitHistory) {
                IndexElasticsearchDocument::dispatch($visitHistory->index, $visitHistory->body, $visitHistory->type, true);
            }

            echo "Success import data \n";

        } catch (Exception) {
            echo "Failed import data \n";
        }
    }

    public function asCommand(): void
    {
       $this->handle();
    }
}
