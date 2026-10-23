<?php

namespace App\Actions\CRM\AppointmentBlock;

use App\Actions\OrgAction;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * A whole-day block runs from midnight of its first day to midnight after its last, in the shop's
 * timezone; a block of hours stays inside one day. Appointments already booked in it are kept:
 * the calendar marks them so staff decide what to tell each visitor.
 */
class StoreAppointmentBlock extends OrgAction
{
    public function handle(Shop $shop, array $modelData, ?int $userId = null): AppointmentBlock
    {
        $timezone = $shop->timezone?->name ?? 'UTC';
        $isAllDay = (bool) Arr::get($modelData, 'is_all_day');
        $dateFrom = $modelData['date_from'];

        if ($isAllDay) {
            $startsAt = Carbon::parse($dateFrom, $timezone)->startOfDay();
            $endsAt   = Carbon::parse(Arr::get($modelData, 'date_to') ?: $dateFrom, $timezone)->startOfDay()->addDay();
        } else {
            $startsAt = Carbon::parse($dateFrom.' '.$modelData['time_from'], $timezone);
            $endsAt   = Carbon::parse($dateFrom.' '.$modelData['time_to'], $timezone);
        }

        /** @var AppointmentBlock $block */
        $block = $shop->appointmentBlocks()->create([
            'group_id'            => $shop->group_id,
            'organisation_id'     => $shop->organisation_id,
            'appointment_type_id' => Arr::get($modelData, 'appointment_type_id'),
            'starts_at'           => $startsAt->utc(),
            'ends_at'             => $endsAt->utc(),
            'is_all_day'          => $isAllDay,
            'reason'              => Arr::get($modelData, 'reason'),
            'created_by_user_id'  => $userId,
        ]);

        SyncAppointmentBlockGoogleCalendarEvent::dispatchFor($shop, $block->id);

        return $block;
    }

    public static function clashingAppointments(AppointmentBlock $block): int
    {
        return Appointment::where('shop_id', $block->shop_id)
            ->whereIn('state', AppointmentStateEnum::holdingSlot())
            ->where('starts_at', '<', $block->ends_at)
            ->where('ends_at', '>', $block->starts_at)
            ->when($block->appointment_type_id, fn ($query) => $query->where('appointment_type_id', $block->appointment_type_id))
            ->count();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'is_all_day'          => ['required', 'boolean'],
            'date_from'           => ['required', 'date_format:Y-m-d'],
            'date_to'             => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'time_from'           => ['exclude_if:is_all_day,true', 'required', 'date_format:H:i'],
            'time_to'             => ['exclude_if:is_all_day,true', 'required', 'date_format:H:i', 'after:time_from'],
            'appointment_type_id' => ['nullable', 'integer', Rule::exists('appointment_types', 'id')->where('shop_id', $this->shop->id)->whereNull('deleted_at')],
            'reason'              => ['nullable', 'string', 'max:255'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'time_to.after'          => __('The block must end after it starts.'),
            'date_to.after_or_equal' => __('The last day cannot be before the first day.'),
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): AppointmentBlock
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData, $request->user()->id);
    }

    public function htmlResponse(AppointmentBlock $block): RedirectResponse
    {
        $clashes = self::clashingAppointments($block);

        return Redirect::back()->with('notification', [
            'status'      => $clashes ? 'warning' : 'success',
            'title'       => __('Time blocked'),
            'description' => $clashes
                ? trans_choice(':count booked appointment falls in this time and is marked on the calendar: open it to reschedule or decline it.|:count booked appointments fall in this time and are marked on the calendar: open each to reschedule or decline it.', $clashes, ['count' => $clashes])
                : __('Visitors can no longer book this time on the website.'),
        ]);
    }

    public function action(Shop $shop, array $modelData): AppointmentBlock
    {
        $this->asAction = true;
        $this->initialisationFromShop($shop, $modelData);

        return $this->handle($shop, $this->validatedData);
    }
}
