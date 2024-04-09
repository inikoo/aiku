<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:33:35 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentGateway\Xendit\Webhook;

use App\Actions\Accounting\Payment\UpdatePayment;
use App\Enums\Accounting\Payment\PaymentStateEnum;
use App\Enums\Accounting\Payment\PaymentStatusEnum;
use App\Models\Accounting\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class HandleWebhookNotification
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    /**
     * @throws \Throwable
     */
    public function handle(ActionRequest $request): Payment|Builder
    {
        return DB::transaction(function () use ($request) {
            $callbackToken = $request->header('x-callback-token');
            $webhookId     = $request->header('webhook-id');
            $status        = $request->input('status');

            if ($callbackToken === env('XENDIT_CALLBACK_TOKEN')) {
                $payment = Payment::where('reference', $request->input('external_id'))->first();

                if(!$payment) {
                    abort(404);
                }

                if (blank($payment->webhook_id)) {
                    $data = [
                        'webhook_id' => $webhookId,
                        'status'     => $this->checkStatus($status),
                        'state'      => $this->checkState($status),
                        'data'       => $request->all()
                    ];

                    if($status === 'PAID') {
                        array_merge($data, ['completed_at' => now()]);
                    } else {
                        array_merge($data, ['cancelled_at' => now()]);
                    }

                    UpdatePayment::run($payment, $data);
                }

                return $payment;
            }

            abort(403);
        });
    }

    /**
     * @throws \Throwable
     */
    public function asController(ActionRequest $request): Payment|Builder
    {
        return $this->handle($request);
    }

    public function checkStatus(string $status): string
    {
        match ($status) {
            'PAID'  => $status  = PaymentStatusEnum::SUCCESS->value,
            default => $status  = PaymentStatusEnum::FAIL->value
        };

        return $status;
    }

    public function checkState(string $status): string
    {
        match ($status) {
            'PAID'  => $status  = PaymentStateEnum::COMPLETED->value,
            default => $status  = PaymentStateEnum::CANCELLED->value
        };

        return $status;
    }
}
