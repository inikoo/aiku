<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 12 Oct 2026 16:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

namespace App\Actions\Comms\Mailshot;

use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Models\Comms\Mailshot;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class RunMailshotAbTestVariants
{
    use AsAction;

    public string $jobQueue = 'ses';
    public string $commandSignature = 'run-mailshot-ab-test-variants';

    public function handle(): void
    {
        $dueOriginals = Mailshot::query()
            ->where('state', MailshotStateEnum::SENT)
            ->where('ab_test_variant_delay_hours', '>', 0)
            ->whereRaw("start_sending_at + (ab_test_variant_delay_hours || ' hours')::interval <= ?", [Carbon::now()->utc()])
            ->whereHas('abTestVariant', fn ($query) => $query->where('state', MailshotStateEnum::READY)->whereNull('start_sending_at'));

        foreach ($dueOriginals->cursor() as $mailshot) {
            $claimed = Mailshot::whereKey($mailshot->abTestVariant->id)
                ->where('state', MailshotStateEnum::READY)
                ->whereNull('start_sending_at')
                ->update([
                    'state'            => MailshotStateEnum::SENDING,
                    'start_sending_at' => Carbon::now()->utc(),
                ]);

            if ($claimed !== 1) {
                continue;
            }

            try {
                PrepareMailshotAbTestRecipients::make()->sendDelayedVariant($mailshot);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    public function asCommand(): void
    {
        $this->run();
    }
}
