<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;

class SendMailshotScheduled
{
    use AsCommand;
    use AsAction;
    use WithMailshotStateOps;

    public string $commandSignature = 'mailshot:send-scheduled';

    public function handle(): void
    {
        $mailshots = Mailshot::query()
            ->where('state', MailshotStateEnum::SCHEDULED)
            ->whereNotNull('ready_at')
            ->get();


        foreach ($mailshots as $mailshot) {
            if($mailshot->ready_at->format('Y-m-d H:i') >= now()->format('Y-m-d H:i')) {
                ProcessSendMailshot::dispatch($mailshot);
            }
        }
    }

    public function asCommand(): void
    {
        $this->handle();
    }
}
