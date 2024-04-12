<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Ses;

use App\Actions\CRM\Prospect\UpdateProspectEmailSent;
use App\Actions\Mail\DispatchedEmail\UpdateDispatchedEmail;
use App\Actions\Mail\EmailAddress\Traits\AwsClient;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\Mail\DispatchedEmail;
use Aws\Exception\AwsException;
use Aws\Result;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class SendSesEmail
{
    use AsAction;
    use AwsClient;

    public mixed $message;

    public function handle(string $subject, string $emailHtmlBody, DispatchedEmail $dispatchedEmail, string $sender, string $unsubscribeUrl = null): DispatchedEmail
    {

        if ($dispatchedEmail->state != DispatchedEmailStateEnum::READY) {
            return $dispatchedEmail;
        }

        if (!app()->isProduction() and (!config('mail.devel.send_ses_emails') and !$dispatchedEmail->is_test)) {
            UpdateDispatchedEmail::run(
                $dispatchedEmail,
                [
                    'state'               => DispatchedEmailStateEnum::SENT,
                    'is_sent'             => true,
                    'sent_at'             => now(),
                    'date'                => now(),
                    'provider_message_id' => 'devel-'.Str::uuid()
                ]
            );

            if ($dispatchedEmail->mailshotRecipient->recipient_type == 'Prospect') {
                UpdateProspectEmailSent::run($dispatchedEmail->recipient);
            }



            UpdateDispatchedEmail::run(
                $dispatchedEmail,
                [
                    'state'        => DispatchedEmailStateEnum::DELIVERED,
                    'date'         => now(),
                    'delivered_at' => now(),
                    'is_delivered' => true
                ]
            );



            return $dispatchedEmail;
        }

        $emailData = $this->getEmailData(
            $subject,
            $sender,
            $dispatchedEmail->email->address,
            $emailHtmlBody,
            $unsubscribeUrl
        );




        $numberAttempts = 12;
        $attempt        = 0;

        do {
            try {
                $result = $this->sendEmail($emailData);

                UpdateDispatchedEmail::run(
                    $dispatchedEmail,
                    [
                        'state'               => DispatchedEmailStateEnum::SENT,
                        'is_sent'             => true,
                        'sent_at'             => now(),
                        'date'                => now(),
                        'provider_message_id' => Arr::get($result, 'MessageId')
                    ]
                );

                if($dispatchedEmail->recipient) {
                    if ($dispatchedEmail->mailshotRecipient->recipient_type == 'Prospect') {
                        UpdateProspectEmailSent::run($dispatchedEmail->recipient);
                    }
                }


            } catch (AwsException $e) {
                if ($e->getAwsErrorCode() == 'Throttling' and $attempt < $numberAttempts - 1) {
                    $attempt++;
                    usleep(rand(200, 300) + pow(2, $attempt));
                    continue;
                } else {
                    UpdateDispatchedEmail::run(
                        $dispatchedEmail,
                        [
                            'state'       => DispatchedEmailStateEnum::ERROR,
                            'is_error'    => true,
                            'date'        => now(),
                            'data->error' =>
                                [
                                    'code'    => $e->getAwsErrorCode(),
                                    'msg'     => $e->getAwsErrorMessage(),
                                    'attempt' => $attempt

                                ],
                        ]
                    );

                    break;
                }
            } catch (Exception $e) {
                UpdateDispatchedEmail::run(
                    $dispatchedEmail,
                    [
                        'state'       => DispatchedEmailStateEnum::ERROR,
                        'is_error'    => true,
                        'date'        => now(),
                        'data->error' =>
                            [
                                'msg'     => $e->getMessage(),
                            ],
                    ]
                );

                break;
            }

            break;
        } while ($attempt < $numberAttempts);


        return $dispatchedEmail;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendEmail($emailData): Result
    {
        return $this->getSesClient()->sendRawEmail($this->getRawEmail($emailData));
    }

    public function getEmailData($subject, $sender, $to, $emailHtmlBody, $unsubscribeUrl = null): array
    {
        $message = [
            'Message' => [
                'Subject' => [
                    'Data' => $subject,
                ]
            ]
        ];

        $message['Message']['Body']['Html'] = [
            'Data' => $emailHtmlBody
        ];


        $headers = [];
        if ($unsubscribeUrl) {
            data_set($headers, 'List-Unsubscribe', '<'.$unsubscribeUrl.'>');
            data_set($headers, 'List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        }


        return [
            'Source'      => $sender,
            'Destination' => [
                'ToAddresses' => [
                    $to
                ]
            ],
            'Message'     => $message['Message'],
            'Headers'     => $headers
        ];

    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function getRawEmail(array $emailData): array
    {

        $mail = new PHPMailer();

        $mail->addAddress($emailData['Destination']['ToAddresses'][0]);
        $mail->setFrom($emailData['Source']);

        foreach (Arr::get($emailData, 'Headers', []) as $key => $header) {
            $mail->addCustomHeader($key, $header);
        }
        $mail->isHTML();
        $mail->Subject = $emailData['Message']['Subject']['Data'];
        $mail->CharSet = 'UTF-8';

        $mail->Body    = $emailData['Message']['Body']['Html']['Data'];
        $mail->XMailer = null;




        $mail->preSend();

        $rawData=[
            'Source'       => $emailData['Source'],
            'Destinations' => $emailData['Destination']['ToAddresses'],
            'RawMessage'   => [
                'Data' => $mail->getSentMIMEMessage(),
            ]
        ];

        if (config('services.ses.configuration_set')) {
            data_set($rawData, 'ConfigurationSetName', config('services.ses.configuration_set'));
        }


        return $rawData;
    }


}
