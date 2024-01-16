<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Nov 2023 14:23:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use Exception;
use Illuminate\Support\Arr;

trait WithRecipientsInput
{
    /**
     * @throws \Exception
     */
    public function postProcessRecipients(array $recipients): array
    {
        switch (Arr::get($recipients, 'recipient_builder_type')) {
            case 'query':
                Arr::forget(
                    $recipients,
                    [
                    'recipient_builder_data.custom_prospects_query',
                    'recipient_builder_data.prospects']
                );

                break;
            case 'custom_prospects_query':
                Arr::forget($recipients, ['recipient_builder_data,query', 'recipient_builder_data,prospects']);

                data_set(
                    $recipients,
                    'recipient_builder_data.custom_prospects_query',
                    $this->cleanCustomProspectsQuery(Arr::get($recipients, 'recipient_builder_data.custom_prospects_query', []))
                );

                break;
            case 'prospects':
                Arr::forget($recipients, ['recipient_builder_data.custom_prospects_query', 'recipient_builder_data.query']);
                data_set($recipients, 'recipient_builder_data.prospects', [], overwrite: false);

                break;
            default:
                throw new Exception('Invalid recipient builder type');
        }


        return $recipients;
    }

    public function cleanCustomProspectsQuery(array $queryComponents): array
    {
        return array_merge(
            [
                'can_contact_by' =>
                    [
                        'fields' => ['email']
                    ],
            ],
            $queryComponents
        );
    }

}
