<?php

namespace App\Actions\CRM\Appointment\Widget;

use App\Actions\Iris\Appointment\GetIrisAppointmentTypes;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAppointmentWidgetConfig
{
    use AsAction;

    /**
     * @return array{shop_name: string, timezone: string, country_code: string|null, appointment_types: array<int, array<string, mixed>>, theme: array<int, string>|null}
     */
    public function handle(Shop $shop): array
    {
        return [
            ...GetIrisAppointmentTypes::make()->handle($shop),
            'theme' => Arr::get($shop->website?->published_layout, 'theme.color'),
        ];
    }

    public function asController(ActionRequest $request): array
    {
        $key  = $request->query('key');
        $shop = AppointmentWidgetKey::make()->resolveShop(is_string($key) ? $key : null);

        if (!$shop) {
            abort(404, 'Unknown appointment widget');
        }

        return $this->handle($shop);
    }

    public function jsonResponse(array $config): array
    {
        return [
            'data' => $config,
        ];
    }
}
