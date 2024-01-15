<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Nov 2023 23:56:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Helpers\Query\GetQueryEloquentQueryBuilder;
use App\Actions\Helpers\Query\WithQueryCompiler;
use App\Actions\Traits\WithCheckCanContactByEmail;
use App\Models\Helpers\Query;
use App\Models\Leads\Prospect;
use App\Models\Market\Shop;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

class GetEstimatedNumberRecipients
{
    use AsObject;
    use WithCheckCanContactByEmail;
    use WithQueryCompiler;

    /**
     * @throws \Exception
     */
    public function handle(Shop $parent, array $recipientsData): int
    {
        return match (Arr::get($recipientsData, 'recipient_builder_type')) {
            'query' => $this->getEstimatedNumberRecipientsQuery(
                $parent,
                Arr::get($recipientsData, 'recipient_builder_data.query')
            ),
            'prospects'              => $this->getEstimatedNumberRecipientsProspects(Arr::get($recipientsData, 'recipient_builder_data.prospects')),
            'custom_prospects_query' => $this->getEstimatedNumberRecipientsCustomProspectsQuery(
                $parent,
                Arr::get($recipientsData, 'recipient_builder_data.custom_prospects_query')
            ),
            default => 0
        };
    }

    /**
     * @throws \Exception
     */
    private function getEstimatedNumberRecipientsQuery($parent, $queryData): int
    {
        $counter = 0;

        $query = Query::find(Arr::get($queryData, 'id'));

        if ($query) {
            if ($query->has_arguments) {
                return $this->getEstimatedNumberRecipientsCustomProspectsQuery(
                    $parent,
                    Arr::get($queryData, 'data')
                );
            } else {
                $queryBuilder = GetQueryEloquentQueryBuilder::run($query);
                $queryBuilder->chunk(
                    1000,
                    function ($recipients) use (&$counter) {
                        foreach ($recipients as $recipient) {
                            if (!$this->canContactByEmail($recipient)) {
                                continue;
                            }
                            $counter++;
                        }
                    }
                );
            }
        }

        return $counter;
    }

    private function getEstimatedNumberRecipientsProspects($prospectIDs): int
    {
        $counter = 0;

        foreach ($prospectIDs as $prospectID) {
            // dd($prospectID);
            $prospect = Prospect::find($prospectID);
            if (!$this->canContactByEmail($prospect)) {
                continue;
            }
            $counter++;
        }

        return $counter;
    }

    /**
     * @throws \Exception
     */
    private function getEstimatedNumberRecipientsCustomProspectsQuery($parent, $queryData): int
    {
        $counter = 0;

        if (count($queryData) == 0) {
            return $counter;
        }


        $compiledQueryData = $this->compileConstrains($queryData);


        $queryBuilder = GetQueryEloquentQueryBuilder::make()->buildQuery(Prospect::class, $parent, $compiledQueryData);


        $queryBuilder->chunk(
            1000,
            function ($recipients) use (&$counter) {
                foreach ($recipients as $recipient) {
                    if (!$this->canContactByEmail($recipient)) {
                        continue;
                    }
                    $counter++;
                }
            }
        );

        return $counter;
    }

}
