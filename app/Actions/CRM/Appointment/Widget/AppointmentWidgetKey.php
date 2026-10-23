<?php

namespace App\Actions\CRM\Appointment\Widget;

use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

class AppointmentWidgetKey
{
    use AsObject;

    public function ensure(Shop $shop): string
    {
        $key = Arr::get($shop->settings, 'appointments.widget_key');

        if (is_string($key) && $key !== '') {
            return $key;
        }

        $key      = Str::lower(Str::random(32));
        $settings = $shop->settings ?? [];

        data_set($settings, 'appointments.widget_key', $key);
        $shop->update(['settings' => $settings]);

        return $key;
    }

    public function resolveShop(?string $key): ?Shop
    {
        if (!is_string($key) || $key === '') {
            return null;
        }

        return Shop::whereRaw("settings #>> '{appointments,widget_key}' = ?", [$key])
            ->whereRaw("settings #>> '{appointments,widget_enabled}' = 'true'")
            ->first();
    }

    public function embedSnippet(string $key): string
    {
        $src = url('/appointment-widget/v1.js').'?k='.$key;

        return <<<HTML
        <div data-aiku-appointments></div>
        <script src="$src" async></script>
        HTML;
    }
}
