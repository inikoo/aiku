<?php

namespace App\Actions\Comms\Outbox;

use App\Actions\Comms\Outbox\ReviewReminder\ProcessReviewReminderRecipients;
use App\Actions\Iris\Reviews\ShowIrisOrderReviewPreview;
use App\Enums\Catalogue\Product\ProductStatusEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Catalogue\Product;
use App\Models\Comms\Outbox;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Made-up merge tag values for an outbox's test email, built from the shop's real products so the
 * test looks like the real email, without reading or writing any customer or order.
 */
class GetOutboxTestEmailData
{
    use AsAction;

    private const int SAMPLE_PRODUCTS = 6;

    /**
     * @return array<string, string>
     */
    public function handle(Outbox $outbox): array
    {
        if (!$outbox->shop) {
            return [];
        }

        $previousLocale = app()->getLocale();
        app()->setLocale($outbox->shop->language->code);

        $data = match ($outbox->code) {
            OutboxCodeEnum::REVIEW_REMINDER => ['review_reminder_links' => $this->reviewReminderLinks($outbox)],
            default                         => [],
        };

        app()->setLocale($previousLocale);

        return $data;
    }

    private function reviewReminderLinks(Outbox $outbox): string
    {
        $shop      = $outbox->shop;
        $reference = 'TEST-'.now()->format('ymdHi');

        $products = Product::query()
            ->where('shop_id', $shop->id)
            ->where('status', ProductStatusEnum::FOR_SALE)
            ->orderByRaw('image_id IS NULL')
            ->latest('id')
            ->limit(self::SAMPLE_PRODUCTS)
            ->get()
            ->sortBy('code')
            ->values();

        return (new ProcessReviewReminderRecipients())->reviewBlock(
            $shop->name,
            $reference,
            $products,
            fn (array $preselect) => ShowIrisOrderReviewPreview::url($shop, $reference, $products->pluck('id')->all(), $preselect)
        );
    }
}
