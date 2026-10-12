<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 12 Oct 2026 12:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

namespace App\Actions\Comms\Mailshot;

use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Enums\Comms\Mailshot\MailshotTypeEnum;
use App\Models\Comms\Mailshot;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class PrepareMailshotAbTestRecipients
{
    use AsAction;

    public function handle(Mailshot $mailshot, ?Builder $audienceQuery): void
    {
        $variant = $mailshot->abTestVariant;

        $variant->update([
            'state'            => MailshotStateEnum::SENDING,
            'start_sending_at' => now(),
        ]);

        $customerIds = $this->validCustomerIds($audienceQuery)->shuffle()->values()->all();

        $testSize          = (int)round(count($customerIds) * $mailshot->ab_test_size_percentage / 100);
        $variantCustomers  = array_slice($customerIds, 0, intdiv($testSize, 2));
        $originalCustomers = array_slice($customerIds, count($variantCustomers), $testSize - count($variantCustomers));

        $this->sendToCustomers($mailshot, $originalCustomers);
        $this->sendToCustomers($variant, $variantCustomers);
    }

    public function audienceQuery(Mailshot $mailshot): ?Builder
    {
        if ($mailshot->type === MailshotTypeEnum::NEWSLETTER) {
            return PrepareNewsletterRecipients::make()->audienceQuery($mailshot);
        }

        return GetMailshotRecipientsQueryBuilder::make()->handle($mailshot);
    }

    /**
     * @return Collection<int, int>
     */
    public function validCustomerIds(?Builder $audienceQuery): Collection
    {
        if (!$audienceQuery) {
            return collect();
        }

        return $audienceQuery->select('customers.id', 'customers.email')
            ->orderBy('customers.id')
            ->get()
            ->filter(fn ($customer) => filter_var($customer->email, FILTER_VALIDATE_EMAIL))
            ->pluck('id')
            ->unique();
    }

    /**
     * @param  array<int, int>  $customerIds
     */
    public function sendToCustomers(Mailshot $mailshot, array $customerIds, int $alreadyStoredRecipients = 0): void
    {
        foreach (array_chunk($customerIds, 50) as $customerIdsChunk) {
            ProcessSendMailshot::dispatch($mailshot->id, $customerIdsChunk);
        }

        $mailshot->update([
            'recipients_prepared_at' => now(),
            'recipients_count'       => $alreadyStoredRecipients + count($customerIds),
        ]);
        UpdateMailshotRecipientsStoredAt::run($mailshot);
    }
}
