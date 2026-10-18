<?php

namespace App\Actions\Comms\Outbox\ReviewReminder;

use App\Actions\Comms\DispatchedEmail\StoreDispatchedEmail;
use App\Actions\Comms\EmailBulkRun\StoreEmailBulkRunRecipient;
use App\Actions\Comms\EmailBulkRun\UpdateEmailBulkRunRecipientStoredAt;
use App\Actions\Comms\EmailDeliveryChannel\SendEmailDeliveryChannel;
use App\Actions\Comms\EmailDeliveryChannel\StoreEmailDeliveryChannel;
use App\Actions\Comms\EmailDeliveryChannel\UpdateEmailDeliveryChannel;
use App\Enums\Comms\EmailDeliveryChannel\EmailDeliveryChannelStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Comms\EmailBulkRun;
use App\Models\Comms\Outbox;
use App\Models\CRM\Customer;
use App\Models\Ordering\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class ProcessReviewReminderRecipients implements ShouldQueue
{
    use AsAction;

    private const int PRODUCTS_IN_EMAIL = 5;

    public string $jobQueue = 'ses';

    public function handle(int $emailBulkRunId, array $customers): void
    {
        $emailBulkRun = EmailBulkRun::find($emailBulkRunId);

        if (!$emailBulkRun) {
            return;
        }

        $outbox = Outbox::find($emailBulkRun->outbox_id);

        if (!$outbox) {
            return;
        }

        $previousLocale = app()->getLocale();
        app()->setLocale($outbox->shop->language->code);

        $emailDeliveryChannel = StoreEmailDeliveryChannel::run($emailBulkRun, [
            'state' => EmailDeliveryChannelStateEnum::IN_PROCESS->value,
        ]);

        foreach ($customers as $customer) {
            $customerModel = Customer::find($customer['id']);
            if (!$customerModel) {
                continue;
            }

            $dispatchedEmail = StoreDispatchedEmail::run(
                $emailBulkRun,
                $customerModel,
                [
                    'outbox_id'     => $emailBulkRun->outbox_id,
                    'email_address' => $customerModel->email,
                    'data->additional_data' => [
                        'review_reminder_links' => $this->generateReviewLinks($customer['order_ids'])
                    ]
                ]
            );

            StoreEmailBulkRunRecipient::run(
                $emailBulkRun,
                [
                    'dispatched_email_id' => $dispatchedEmail->id,
                    'recipient_type'      => class_basename($customerModel),
                    'recipient_id'        => $customerModel->id,
                    'channel'             => $emailDeliveryChannel->id,
                    'recipient_name'      => $customerModel->name,
                ]
            );
        }

        app()->setLocale($previousLocale);

        UpdateEmailDeliveryChannel::run(
            $emailDeliveryChannel,
            [
                'number_emails' => $emailBulkRun->recipients()->where('channel', $emailDeliveryChannel->id)->count(),
                'state'         => EmailDeliveryChannelStateEnum::READY->value
            ]
        );
        UpdateEmailBulkRunRecipientStoredAt::run($emailBulkRun);

        SendEmailDeliveryChannel::dispatch($emailDeliveryChannel->id)->delay(2);
    }

    public function generateReviewLinks(string $orderIds): string
    {
        $html = '';
        foreach (explode(',', $orderIds) as $orderId) {
            $order = Order::find((int) $orderId);
            if (!$order || !$order->reviewUrl()) {
                continue;
            }

            $html .= $this->orderReviewBlock($order);
        }

        return $html;
    }

    /**
     * Every star is a link that opens the order's review page with that rating already chosen,
     * so a customer rates the company and each product from the email itself (HELP-4098).
     */
    private function orderReviewBlock(Order $order): string
    {
        $products = Product::query()
            ->whereIn('id', $order->transactions()->where('model_type', 'Product')->select('model_id'))
            ->orderBy('code')
            ->get();

        $rows = $this->reviewRow(
            __('How would you rate :shop?', ['shop' => e($order->shop->name)]),
            null,
            fn (int $rating) => $order->reviewUrl(['rating' => $rating])
        );

        foreach ($products->take(self::PRODUCTS_IN_EMAIL) as $product) {
            $rows .= $this->reviewRow(
                e($product->name),
                Arr::get($product->imageSources(112, 112), 'png'),
                fn (int $rating) => $order->reviewUrl(['product' => $product->id, 'rating' => $rating])
            );
        }

        if ($products->count() > self::PRODUCTS_IN_EMAIL) {
            $rows .= '<tr><td colspan="3" style="padding:12px 0 0;font-size:14px;"><a href="'.e($order->reviewUrl()).'" style="color:#111827;font-weight:600;">'
                .__('Review all :count products', ['count' => $products->count()]).'</a></td></tr>';
        }

        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:Arial,Helvetica,sans-serif;margin:0 0 24px;">'
            .'<tr><td colspan="3" style="padding:0 0 4px;font-size:18px;font-weight:700;color:#111827;">'.__('How was your order :reference?', ['reference' => e($order->reference)]).'</td></tr>'
            .'<tr><td colspan="3" style="padding:0 0 8px;font-size:14px;color:#6b7280;">'.__('Tap a star to rate, it only takes a few seconds.').'</td></tr>'
            .$rows
            .'</table>';
    }

    private function reviewRow(string $label, ?string $imageUrl, callable $starUrl): string
    {
        $stars = '';
        foreach (range(1, 5) as $rating) {
            $stars .= '<a href="'.e($starUrl($rating)).'" title="'.$rating.'/5" style="color:#f5b301;text-decoration:none;font-size:30px;line-height:30px;padding:0 1px;">&#9733;</a>';
        }

        $image = $imageUrl
            ? '<td width="64" style="padding:12px 8px 12px 0;border-top:1px solid #e5e7eb;vertical-align:middle;"><img src="'.e($imageUrl).'" width="56" height="56" alt="" style="display:block;border-radius:6px;object-fit:cover;"></td>'
            : '<td width="0" style="padding:12px 0;border-top:1px solid #e5e7eb;"></td>';

        return '<tr>'.$image
            .'<td style="padding:12px 8px 12px 0;border-top:1px solid #e5e7eb;vertical-align:middle;font-size:14px;color:#111827;">'.$label.'</td>'
            .'<td align="right" style="padding:12px 0;border-top:1px solid #e5e7eb;vertical-align:middle;white-space:nowrap;">'.$stars.'</td>'
            .'</tr>';
    }
}
