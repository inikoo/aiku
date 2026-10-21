<?php

namespace App\Actions\CRM\Appointment\GoogleCalendar;

use App\Actions\CRM\AppointmentBlock\SyncAppointmentBlockGoogleCalendarEvent;
use App\Actions\OrgAction;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentBlock;
use App\Models\SysAdmin\Organisation;
use App\Services\GoogleCalendar\GoogleCalendarClient;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Throwable;

/**
 * Aiku is the record: the shop calendar is brought back to the appointments calendar, so an event
 * that never arrived, was edited or deleted in Google, or outlived its appointment or block is put
 * right. Events of another shop sharing the same Google calendar are left alone.
 */
class ReconcileShopGoogleCalendar extends OrgAction
{
    public const int PAST_DAYS = 30;

    public const int FUTURE_DAYS = 365;

    public string $commandSignature = 'appointments:reconcile-google-calendar';

    public string $jobQueue = 'low-priority';

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo(['org-admin.'.$this->organisation->id, 'shop-admin.'.$this->shop->id]);
    }

    /**
     * @return array{written: int, removed: int}
     *
     * @throws RequestException
     */
    public function handle(Shop $shop): array
    {
        $client = GoogleCalendarClient::forShop($shop);

        if (!$client) {
            return ['written' => 0, 'removed' => 0];
        }

        $from   = now()->subDays(self::PAST_DAYS);
        $to     = now()->addDays(self::FUTURE_DAYS);
        $events = $client->appointmentEvents($from, $to);

        $appointments = $shop->appointments()
            ->whereNotIn('state', AppointmentStateEnum::closed())
            ->whereBetween('starts_at', [$from, $to])
            ->with(['shop', 'organisation', 'appointmentType', 'user'])
            ->get()
            ->keyBy('id');

        $outsideWindow = Appointment::withTrashed()
            ->whereIn('id', $events->keys()->diff($appointments->keys()))
            ->with(['shop', 'organisation', 'appointmentType', 'user'])
            ->get()
            ->keyBy('id');

        $sync    = SyncAppointmentGoogleCalendarEvent::make();
        $written = 0;
        $removed = 0;

        foreach ($appointments->union($outsideWindow) as $appointment) {
            if ($appointment->shop_id !== $shop->id) {
                continue;
            }

            $event = $events->get($appointment->id);

            if (!$sync->belongsInCalendar($appointment)) {
                if ($event) {
                    $client->deleteEvent($appointment->id);
                    $removed++;
                }

                continue;
            }

            $expected = $sync->event($appointment);

            if (!$event || !$this->sameEvent($expected, $event)) {
                $client->saveEvent($appointment->id, $expected);
                $written++;
            }
        }

        foreach ($events->keys()->diff($appointments->keys())->diff($outsideWindow->keys()) as $appointmentId) {
            $client->deleteEvent($appointmentId);
            $removed++;
        }

        $blocks = $this->reconcileBlocks($shop, $client, $from, $to);

        return ['written' => $written + $blocks['written'], 'removed' => $removed + $blocks['removed']];
    }

    /**
     * @return array{written: int, removed: int}
     *
     * @throws RequestException
     */
    private function reconcileBlocks(Shop $shop, GoogleCalendarClient $client, Carbon $from, Carbon $to): array
    {
        $events = $client->blockEvents($from, $to);
        $blocks = AppointmentBlock::whereIn('id', $events->keys())
            ->orWhere(fn ($query) => $query->where('shop_id', $shop->id)->overlapping($from, $to))
            ->with(['shop', 'organisation', 'appointmentType'])
            ->get()
            ->keyBy('id');

        $sync    = SyncAppointmentBlockGoogleCalendarEvent::make();
        $written = 0;
        $removed = 0;

        foreach ($blocks->where('shop_id', $shop->id) as $block) {
            $expected = $sync->event($block);
            $event    = $events->get($block->id);

            if (!$event || !$this->sameEvent($expected, $event)) {
                $client->putEvent(GoogleCalendarClient::blockEventId($block->id), $expected);
                $written++;
            }
        }

        foreach ($events->keys()->diff($blocks->keys()) as $blockId) {
            $client->removeEvent(GoogleCalendarClient::blockEventId($blockId));
            $removed++;
        }

        return ['written' => $written, 'removed' => $removed];
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $event
     */
    public function sameEvent(array $expected, array $event): bool
    {
        foreach (['summary', 'description', 'location', 'status'] as $field) {
            if ($this->comparable(Arr::get($expected, $field)) !== $this->comparable(Arr::get($event, $field))) {
                return false;
            }
        }

        foreach (['start', 'end'] as $field) {
            $eventTime = Arr::get($event, $field.'.dateTime');

            if (!$eventTime || !Carbon::parse(Arr::get($expected, $field.'.dateTime'))->equalTo(Carbon::parse($eventTime))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Links in an event are written with http from the scheduled command and https from a web
     * request, so the scheme is left out of the comparison.
     */
    private function comparable(mixed $value): string
    {
        return preg_replace('#https?://#', '//', (string) $value);
    }

    public function asCommand(Command $command): int
    {
        Shop::whereNotNull('settings->'.GoogleCalendarClient::SETTINGS_KEY.'->refresh_token')
            ->get()
            ->filter(fn (Shop $shop) => GoogleCalendarClient::isConnected($shop))
            ->each(function (Shop $shop) use ($command) {
                try {
                    $result = $this->handle($shop);
                    $command->info($shop->slug.': '.$result['written'].' events written, '.$result['removed'].' removed');
                } catch (Throwable $exception) {
                    report($exception);
                    $command->error($shop->slug.': '.$exception->getMessage());
                }
            });

        return 0;
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromShop($shop, $request);

        self::dispatch($shop);

        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Google Calendar sync started'),
            'description' => __('The calendar is being matched to the appointments in Aiku in the background. It takes a minute or two.'),
        ]);
    }
}
