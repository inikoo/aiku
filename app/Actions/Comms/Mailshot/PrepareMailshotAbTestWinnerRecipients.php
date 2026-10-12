<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 12 Oct 2026 12:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

namespace App\Actions\Comms\Mailshot;

use App\Models\Comms\Mailshot;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class PrepareMailshotAbTestWinnerRecipients
{
    use AsAction;

    public string $jobQueue = 'urgent';

    public function tags(): array
    {
        return ['send_mailshot_ab_test_winner'];
    }

    public function handle(Mailshot $mailshot): void
    {
        $winner = $mailshot->abTestWinner;
        if (!$winner) {
            return;
        }

        $testMailshotIds = [$mailshot->id, $mailshot->abTestVariant->id];

        $prepareAbTest = PrepareMailshotAbTestRecipients::make();
        $audienceQuery = $prepareAbTest->audienceQuery($mailshot);
        $audienceQuery?->whereNotExists(function ($query) use ($testMailshotIds) {
            $query->select(DB::raw(1))
                ->from('mailshot_recipients')
                ->whereColumn('mailshot_recipients.recipient_id', 'customers.id')
                ->where('mailshot_recipients.recipient_type', 'Customer')
                ->whereIn('mailshot_recipients.mailshot_id', $testMailshotIds);
        });

        $customerIds = $prepareAbTest->validCustomerIds($audienceQuery)->values()->all();

        $prepareAbTest->sendToCustomers($winner, $customerIds, $winner->recipients()->count());

        if (!$customerIds) {
            UpdateMailshotSentState::run($winner->refresh());
        }
    }
}
