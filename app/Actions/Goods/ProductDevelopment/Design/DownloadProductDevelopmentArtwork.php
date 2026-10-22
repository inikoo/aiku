<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-16h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentArtwork;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * The current artwork of one SKU as a PDF: the uploaded file, or the Aiku layout drawn as a vector PDF
 * at its real size (no browser on the server). The JPG download is made in the browser from this PDF.
 */
class DownloadProductDevelopmentArtwork extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public function handle(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork): string
    {
        abort_unless($artwork->product_development_id === $productDevelopment->id, 404);

        $pdf = self::bytes($artwork);
        abort_unless($pdf !== null, 404);

        return $pdf;
    }

    public static function bytes(ProductDevelopmentArtwork $artwork): ?string
    {
        if ($artwork->source === ProductDevelopmentArtwork::SOURCE_AIKU) {
            return ProductDevelopmentDesignPdf::render($artwork);
        }

        try {
            $stream = $artwork->media?->stream();

            $bytes  = $stream ? stream_get_contents($stream) : false;

            return $bytes === false ? null : $bytes;
        } catch (Throwable) {
            return null;
        }
    }

    public static function fileName(ProductDevelopmentArtwork $artwork): string
    {
        return preg_replace('/[^A-Za-z0-9._-]+/', '-', ProductDevelopmentDesignPdf::title($artwork)).'.pdf';
    }

    public function asController(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork, ActionRequest $request): Response
    {
        $this->initialisationFromGroup(group(), $request);

        return response($this->handle($productDevelopment, $artwork), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($request->boolean('inline') ? 'inline' : 'attachment').'; filename="'.self::fileName($artwork).'"',
        ]);
    }
}
