<?php

namespace App\Actions\CRM\Appointment\GoogleCalendar;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Services\GoogleCalendar\GoogleCalendarClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DisconnectShopGoogleCalendar extends OrgAction
{
    use WithActionUpdate;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo(['org-admin.'.$this->organisation->id, 'shop-admin.'.$this->shop->id]);
    }

    public function handle(Shop $shop): Shop
    {
        $settings = $shop->settings ?? [];
        Arr::forget($settings, GoogleCalendarClient::SETTINGS_KEY);

        Cache::forget(GoogleCalendarClient::accessTokenCacheKey($shop));

        return $this->update($shop, ['settings' => $settings]);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): RedirectResponse
    {
        $this->initialisationFromShop($shop, $request);

        $this->handle($shop);

        return Redirect::route('grp.org.shops.show.settings.edit', [$shop->organisation->slug, $shop->slug])
            ->with('notification', [
                'status'      => 'success',
                'title'       => __('Google Calendar disconnected'),
                'description' => __('Appointments are no longer written into the calendar, and its busy times no longer block bookings.'),
            ]);
    }
}
