<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\GoodsIn\StockDelivery;

use App\Models\GoodsIn\StockDelivery;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use OwenIt\Auditing\Events\AuditCustom;

/**
 * Closes today's costing reminder. Each delivery waiting more than the reminder's days gets the reason the user
 * typed written in its history under their name, and may be left out of the reminder until a date at most
 * a month away (data.costing_reminder_snoozed_until).
 */
class AcknowledgeStockDeliveriesCostingReminder
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return (bool) $request->user();
    }

    public function rules(): array
    {
        return [
            'reasons'   => ['sometimes', 'array'],
            'reasons.*' => ['nullable', 'string', 'max:500'],
            'remind_on'   => ['sometimes', 'array'],
            'remind_on.*' => ['nullable', 'date', 'after:today', 'before_or_equal:'.now()->addMonth()->toDateString()],
        ];
    }

    /**
     * @param array<int|string, string|null> $reasons
     * @param array<int|string, string|null> $remindOn
     */
    public function handle(User $user, array $reasons, array $remindOn = []): void
    {
        $late = array_filter(GetStockDeliveriesCostingReminder::make()->deliveries($user), fn (array $delivery) => $delivery['needs_reason']);

        $missing = array_filter($late, fn (array $delivery) => mb_strlen(trim((string) Arr::get($reasons, $delivery['id']))) < 5);
        if ($missing) {
            throw ValidationException::withMessages(
                collect($missing)->mapWithKeys(fn (array $delivery) => ["reasons.{$delivery['id']}" => __('Say why :reference is still not costed', ['reference' => $delivery['reference']])])->all()
            );
        }

        foreach ($late as $delivery) {
            $stockDelivery                 = StockDelivery::find($delivery['id']);
            $stockDelivery->auditEvent     = 'costing_late_reason';
            $stockDelivery->isCustomEvent  = true;
            $stockDelivery->auditCustomOld = [];
            $snoozedUntil                  = Arr::get($remindOn, $delivery['id']) ? Carbon::parse($remindOn[$delivery['id']])->toDateString() : null;
            $stockDelivery->auditCustomNew = array_filter(['working_days_since_booking_in' => $delivery['days'], 'reason' => trim($reasons[$delivery['id']]), 'remind_on' => $snoozedUntil]);
            Event::dispatch(new AuditCustom($stockDelivery));
            $stockDelivery->isCustomEvent  = false;
            $stockDelivery->auditCustomNew = [];

            if ($snoozedUntil) {
                $stockDelivery->updateQuietly(['data' => array_merge($stockDelivery->data ?? [], ['costing_reminder_snoozed_until' => $snoozedUntil])]);
            }
        }

        Cache::put(GetStockDeliveriesCostingReminder::cacheKey($user), true, now()->endOfDay());
    }

    public function asController(ActionRequest $request): void
    {
        $this->handle($request->user(), $request->validated('reasons', []), $request->validated('remind_on', []));
    }

    public function htmlResponse(): RedirectResponse
    {
        return redirect()->back();
    }
}
