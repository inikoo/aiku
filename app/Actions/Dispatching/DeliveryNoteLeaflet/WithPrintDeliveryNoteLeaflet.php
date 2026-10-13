<?php

/*
 * Author: Andi Ferdiawan
 * Created: Mon, 14 Jul 2026 13:30:00 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2026, Inikoo Ltd
 */

namespace App\Actions\Dispatching\DeliveryNoteLeaflet;

use App\Enums\Dispatching\DeliveryNoteLeaflet\DeliveryNoteLeafletStateEnum;
use App\Models\Dispatching\DeliveryNoteLeaflet;
use Exception;
use Illuminate\Support\Arr;
use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Mccarlosen\LaravelMpdf\LaravelMpdf;
use Rawilk\Printing\Api\PrintNode\Enums\PrintJobOption;
use Rawilk\Printing\Api\PrintNode\Resources\Printer;
use Rawilk\Printing\Api\PrintNode\Resources\PrintJob;

trait WithPrintDeliveryNoteLeaflet
{
    protected function leafletPrinterId(User $user): ?int
    {
        $printerId = Arr::get($user->settings, 'preferred_leaflet_printer_id') ?: Arr::get($user->settings, 'preferred_printer_id');

        return $printerId ? (int) $printerId : null;
    }

    /**
     * Sends the leaflet's media file to the given PrintNode printer and marks it printed.
     * Uses WithPrintNode::printPdf() (the consuming action must also use WithPrintNode).
     */
    protected function printDeliveryNoteLeaflet(DeliveryNoteLeaflet $deliveryNoteLeaflet, int $printerId): ?PrintJob
    {
        $media = $deliveryNoteLeaflet->media;

        if (!$media && !filled($deliveryNoteLeaflet->message)) {
            return null;
        }

        if (!$media) {
            $pdfBase64 = base64_encode(
                PDF::loadHTML($this->personalisedMessageHtml($deliveryNoteLeaflet->message))->output()
            );
        } else {
            $bytes = Storage::disk($media->disk)->get($media->getPathRelativeToRoot());

            $pdfBase64 = base64_encode(
                $media->mime_type && str_starts_with($media->mime_type, 'image/') ? $this->imageLeafletPdf($bytes) : $bytes
            );
        }

        $result = $this->printPdf(
            title: $deliveryNoteLeaflet->name,
            printId: $printerId,
            pdfBase64: $pdfBase64,
            options: [PrintJobOption::FitToPage->value => false] + ($media ? $this->leafletPaperOption($printerId) : [])
        );

        $deliveryNoteLeaflet->update([
            'state'              => DeliveryNoteLeafletStateEnum::PRINTED,
            'printed_at'         => now(),
            'printed_by_user_id' => request()->user()?->id,
        ]);

        return $result;
    }

    /**
     * Names the printer's own A6 paper for the job. Without it the printer uses its default paper,
     * usually A4, and lays the A6 page in the middle of that sheet, half off the card.
     *
     * @return array<string, string>
     */
    protected function leafletPaperOption(int $printerId): array
    {
        $this->ensureClientInitialized();

        try {
            $papers = Printer::retrieve($printerId)->capabilities?->papers;
        } catch (Exception) {
            return [];
        }

        $paper = $this->a6PaperName(json_decode(json_encode($papers ?? []), true) ?: []);

        return $paper === null ? [] : [PrintJobOption::Paper->value => $paper];
    }

    /**
     * @param  array<string, array{0: int|null, 1: int|null}>  $papers  paper name => [width, height] in tenths of a millimetre
     */
    protected function a6PaperName(array $papers): ?string
    {
        foreach ($papers as $name => $size) {
            if (abs((int) Arr::get($size, 0) - 1050) <= 20 && abs((int) Arr::get($size, 1) - 1480) <= 20) {
                return (string) $name;
            }
        }

        return Arr::first(array_map('strval', array_keys($papers)), fn (string $name) => preg_match('/\bA6\b/i', $name) === 1);
    }

    /**
     * Lays an image on one A6 page, scaled to fit whole and centred, never cropped.
     * The bytes go in as an mPDF image variable: inlined as base64 they exceed the HTML size mPDF accepts.
     */
    protected function imageLeafletPdf(string $bytes): string
    {
        $size = getimagesizefromstring($bytes);
        if ($size === false) {
            throw ValidationException::withMessages([
                'messages' => __('The insert image could not be read. Ask the customer to upload it again as an A6 PDF.'),
            ]);
        }

        [$width, $height]        = $size;
        [$pageWidth, $pageHeight] = $width > $height ? [148, 105] : [105, 148];

        $pdf = new LaravelMpdf([
            'format'        => [$pageWidth, $pageHeight],
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $scale       = min($pageWidth / $width, $pageHeight / $height);
        $imageWidth  = $width * $scale;
        $imageHeight = $height * $scale;

        $mpdf                       = $pdf->getMpdf();
        $mpdf->imageVars['leaflet'] = $bytes;
        $mpdf->AddPage();
        $mpdf->Image('var:leaflet', ($pageWidth - $imageWidth) / 2, ($pageHeight - $imageHeight) / 2, $imageWidth, $imageHeight, '', '', true, false);

        return $pdf->output();
    }

    protected function personalisedMessageHtml(string $message): string
    {
        return '<div style="font-family: sans-serif; font-size: 14pt; line-height: 1.6; padding: 24pt; white-space: pre-wrap;">'
            .e($message)
            .'</div>';
    }
}
