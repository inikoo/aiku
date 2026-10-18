<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Production;

use App\Models\Inventory\Location;
use App\Models\Production\Production;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class SetProductionFactoryLocation
{
    use AsAction;

    /**
     * The location materials are walked to for the day's production. It changes where
     * production uses materials up from and where customer orders pick last, so it is set
     * from the command line only.
     */
    public function handle(Production $production, ?Location $location): Production
    {
        if ($location) {
            if ($location->warehouse->organisation_id !== $production->organisation_id) {
                throw ValidationException::withMessages(['location' => __('Location belongs to another organisation')]);
            }
            if ($location->is_goods_out) {
                throw ValidationException::withMessages(['location' => __('A goods out location cannot be the factory location')]);
            }
            if (Production::where('factory_location_id', $location->id)->where('id', '!=', $production->id)->exists()) {
                throw ValidationException::withMessages(['location' => __('Location is already the factory location of another production')]);
            }
        }

        $production->update(['factory_location_id' => $location?->id]);

        return $production->refresh();
    }

    public string $commandSignature = 'production:set_factory_location {production : Production slug} {location? : Location code, omit to clear}';

    public function asCommand(Command $command): int
    {
        $production = Production::where('slug', $command->argument('production'))->first();

        if (!$production) {
            $command->error('Production not found');

            return 1;
        }

        $location = null;
        if ($code = $command->argument('location')) {
            $location = Location::whereRelation('warehouse', 'organisation_id', $production->organisation_id)
                ->where('code', $code)
                ->first();

            if (!$location) {
                $command->error("Location $code not found");

                return 1;
            }
        }

        try {
            $this->handle($production, $location);
        } catch (ValidationException $exception) {
            $command->error(collect($exception->errors())->flatten()->implode(', '));

            return 1;
        }

        $command->info($location
            ? "{$production->code} materials are brought to {$location->code}"
            : "{$production->code} has no factory location any more");

        return 0;
    }
}
