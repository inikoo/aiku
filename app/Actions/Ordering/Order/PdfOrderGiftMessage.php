<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\OrgAction;
use App\Models\Ordering\Order;
use Lorisleiva\Actions\ActionRequest;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response;

class PdfOrderGiftMessage extends OrgAction
{
    /**
     * @throws \Mpdf\MpdfException
     */
    public function handle(Order $order): Response
    {
        $attachment = $order->attachments()->wherePivot('scope', 'GiftMessage')->first();

        if ($attachment) {
            return response()->file($attachment->getPath(), [
                'Content-Type' => 'application/pdf',
            ]);
        }

        $filename = 'gift-message-'.$order->slug;

        $pdf = PDF::loadView('order.templates.pdf.gift-message', [
            'message' => $order->gift_message,
        ], [], [
            'format' => 'A6',
        ]);

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'.pdf"');
    }

    /**
     * @throws \Mpdf\MpdfException
     */
    public function asController(Order $order, ActionRequest $request): Response
    {
        $user = $request->user();
        abort_unless(
            $user->authTo(["orders.$order->shop_id.view", "orders.$order->shop_id.edit", "crm.$order->shop_id.view", "org-admin.$order->organisation_id"])
            || $order->deliveryNotes->contains(fn ($deliveryNote) => $deliveryNote->canBeViewedBy($user)),
            403
        );

        return $this->handle($order);
    }
}
