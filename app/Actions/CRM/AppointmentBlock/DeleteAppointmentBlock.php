<?php

namespace App\Actions\CRM\AppointmentBlock;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\CRM\AppointmentBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DeleteAppointmentBlock extends OrgAction
{
    public function handle(AppointmentBlock $block): void
    {
        $shop    = $block->shop;
        $blockId = $block->id;

        $block->delete();

        SyncAppointmentBlockGoogleCalendarEvent::dispatchFor($shop, $blockId);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("crm.{$this->shop->id}.edit");
    }

    public function asController(Shop $shop, AppointmentBlock $appointmentBlock, ActionRequest $request): void
    {
        $this->initialisationFromShop($shop, $request);

        $this->handle($appointmentBlock);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back()->with('notification', [
            'status'      => 'success',
            'title'       => __('Block removed'),
            'description' => __('This time can be booked again on the website.'),
        ]);
    }

    public function action(AppointmentBlock $block): void
    {
        $this->asAction = true;
        $this->initialisationFromShop($block->shop, []);

        $this->handle($block);
    }
}
