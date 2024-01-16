<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Nov 2023 00:20:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Helpers\Query\GetQueryEloquentQueryBuilder;
use App\Actions\Helpers\Query\WithQueryCompiler;
use App\Actions\Traits\WithCheckCanContactByEmail;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Helpers\Query;
use App\Models\Leads\Prospect;
use App\Models\Mail\Mailshot;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\QueryBuilder\QueryBuilder;

class GetMailshotRecipientsQueryBuilder
{
    use AsObject;
    use WithCheckCanContactByEmail;
    use WithQueryCompiler;

    /**
     * @throws \Exception
     */
    public function handle(Mailshot $mailshot): ?QueryBuilder
    {
        return match (Arr::get($mailshot->recipients_recipe, 'recipient_builder_type')) {
            'query'                  => $this->getRecipientsFromQuery($mailshot),
            'prospects'              => $this->getRecipientsFromProspectsList(Arr::get($mailshot->recipients_recipe, 'recipient_builder_data.prospects')),
            'custom_prospects_query' => $this->getRecipientsFromCustomQuery($mailshot),
            default                  => null
        };
    }

    /**
     * @throws \Exception
     */
    private function getRecipientsFromQuery(Mailshot $mailshot): QueryBuilder
    {
        $query = Query::find(Arr::get($mailshot->recipients_recipe, 'recipient_builder_data.query.id'));

        $customArguments = null;
        if ($query->has_arguments) {
            $customArguments = $this->compileConstrains(Arr::get($mailshot->recipients_recipe, 'recipient_builder_data.query.data'));
        }


        return GetQueryEloquentQueryBuilder::run($query, $customArguments);
    }

    private function getRecipientsFromProspectsList(array $prospectIDs): QueryBuilder
    {
        return QueryBuilder::for(Prospect::class)
            ->whereIn('id', $prospectIDs)
            ->where('parent_type', 'Shop')
            ->whereNotNull('email')->where('dont_contact_me', false);
    }

    /**
     * @throws \Exception
     */
    private function getRecipientsFromCustomQuery(Mailshot $mailshot): QueryBuilder
    {
        $modelClass = match ($mailshot->type) {
            MailshotTypeEnum::PROSPECT_MAILSHOT => Prospect::class,
            default                             => Customer::class
        };

        $compiledQueryData = $this->compileConstrains(Arr::get($mailshot->recipients_recipe, 'recipient_builder_data.custom_prospects_query'));

        return GetQueryEloquentQueryBuilder::make()->buildQuery($modelClass, $mailshot->parent, $compiledQueryData);
    }

}
