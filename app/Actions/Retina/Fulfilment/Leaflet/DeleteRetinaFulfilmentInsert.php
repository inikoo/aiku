<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\Leaflet;

use App\Actions\Retina\SysAdmin\WithDisposableLeafletMedia;
use App\Actions\RetinaAction;
use App\Models\Billables\ModelHasLeaflet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DeleteRetinaFulfilmentInsert extends RetinaAction
{
    use WithRetinaFulfilmentInserts;
    use WithDisposableLeafletMedia;

    public function handle(ModelHasLeaflet $customerLeaflet): void
    {
        DB::transaction(function () use ($customerLeaflet) {
            $mediaId = $customerLeaflet->media_id;

            $customerLeaflet->update(['media_id' => null]);
            $customerLeaflet->delete();

            $this->deleteLeafletMediaIfUnused($mediaId);
        });
    }

    public function asController(ModelHasLeaflet $customerLeaflet, ActionRequest $request): void
    {
        $this->initialisation($request);

        $this->handle($customerLeaflet);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Success!'),
            'description' => __('Insert deleted.'),
        ]);
    }
}
