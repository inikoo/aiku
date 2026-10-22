<?php

namespace App\Actions\CRM\Appointment\Widget;

use App\Models\Catalogue\Shop;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class EnableShopAppointmentWidget
{
    use AsAction;

    public string $commandSignature = 'appointments:enable-widget {shop : Shop slug} {--disable}';

    public string $commandDescription = 'Turn the appointment booking widget for other websites (WordPress, Shopify) on or off for a shop';

    /**
     * @return array{key: string|null, enabled: bool}
     */
    public function handle(Shop $shop, bool $enabled = true): array
    {
        $settings = $shop->settings ?? [];
        data_set($settings, 'appointments.widget_enabled', $enabled);
        $shop->update(['settings' => $settings]);

        return [
            'key'     => $enabled ? AppointmentWidgetKey::make()->ensure($shop->refresh()) : null,
            'enabled' => $enabled,
        ];
    }

    public function asCommand(Command $command): int
    {
        $shop = Shop::where('slug', $command->argument('shop'))->first();

        if (!$shop) {
            $command->error('No shop with slug '.$command->argument('shop'));

            return 1;
        }

        $enabled = !$command->option('disable');
        $result  = $this->handle($shop, $enabled);

        if (!$enabled) {
            $command->info('Appointment widget disabled for '.$shop->name);

            return 0;
        }

        $command->info('Appointment widget enabled for '.$shop->name);
        $command->newLine();
        $command->line('Paste this where the booking form should appear (WordPress: Custom HTML block, Shopify: Custom Liquid section):');
        $command->newLine();
        $command->line(AppointmentWidgetKey::make()->embedSnippet($result['key']));
        $command->newLine();
        $command->comment('The host comes from APP_URL. When testing through a tunnel, swap it for the tunnel host.');

        return 0;
    }
}
