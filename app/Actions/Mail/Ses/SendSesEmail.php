<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Ses;

use App\Actions\Mail\EmailAddress\Traits\AwsClient;
use Aws\Result;
use Lorisleiva\Actions\Concerns\AsAction;

class SendSesEmail
{
    use AsAction;
    use AwsClient;

    public mixed $message;

    public function handle(array $content, string $to, $attach = null, $type = 'html'): Result
    {
        $message = [
            'Message' => [
                'Subject' => [
                    'Data' => $content['title']
                ]
            ]
        ];

        if ($type == 'html') {
            $message['Message']['Body']['Html'] = [
                'Data' => $content['body'],
            ];
        } else {
            $message['Message']['Body']['Text'] = [
                'Data' => $content['body'],
            ];
        }

        if (!blank($attach)) {
            $attachments = [];

            foreach ($attach as $attachment) {
                $attachments[] = [
                    'ContentType' => 'image/png',
                    'Filename' => basename($attachment),
                    'Data' => base64_encode(file_get_contents($attachment)),
                ];
            }

            $message['Message']['Attachments'] = $attachments;
        }

        return $this->getSesClient()->sendEmail([
            'Source' => env('MAIL_FROM_ADDRESS'),
            'Destination' => [
                'ToAddresses' => [$to],
            ],
            'Message' => $message['Message']
        ]);
    }
}
