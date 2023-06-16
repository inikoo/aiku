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

class FillMissingItemsElasticsearchDocument
{
    use AsObject;
    use AsAction;

    public string $commandSignature   = 'elasticsearch:fill-missing-items';
    public string $commandDescription = 'Sync the data from backup database';

    public function handle(): void
    {
        try {
            $actionHistories = ActionHistory::where('synced', false)->orderBy('id', 'ASC')->get();
            foreach ($actionHistories as $actionHistory) {
                IndexElasticsearchDocument::dispatch($actionHistory->index, $actionHistory->body, $actionHistory->type, true);
                ActionHistory::find($actionHistory->id)->update(['synced' => true]);
            }

            $visitHistories = VisitHistory::where('synced', false)->orderBy('id', 'ASC')->get();
            foreach ($visitHistories as $visitHistory) {
                IndexElasticsearchDocument::dispatch($visitHistory->index, $visitHistory->body, $visitHistory->type, true);
                VisitHistory::find($visitHistory->id)->update(['synced' => true]);
            }

            echo "Success synchronize data \n";

        } catch (Exception) {
            echo "Failed synchronize data \n";
        }
    }

    public function asCommand(): void
    {
        $this->handle();
    }
}
