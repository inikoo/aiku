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

    public function handle(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork, ?int $version = null): string
    {
        abort_unless($artwork->product_development_id === $productDevelopment->id, 404);

        if ($version !== null && $version !== $artwork->version) {
            $artwork = self::asVersion($artwork, $version);
        }

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

    /**
     * An earlier uploaded version, as an unsaved copy of the artwork carrying that version's file. Versions
     * made in Aiku keep no file of their own, only the latest layout.
     */
    private static function asVersion(ProductDevelopmentArtwork $artwork, int $version): ProductDevelopmentArtwork
    {
        $earlier = $artwork->versions()->where('version', $version)->whereNotNull('media_id')->with('media')->first();
        abort_unless($earlier !== null, 404);

        return (clone $artwork)->forceFill(['version' => $earlier->version, 'source' => ProductDevelopmentArtwork::SOURCE_UPLOAD, 'media_id' => $earlier->media_id])->setRelation('media', $earlier->media);
    }

    public static function fileName(ProductDevelopmentArtwork $artwork): string
    {
        return preg_replace('/[^A-Za-z0-9._-]+/', '-', ProductDevelopmentDesignPdf::title($artwork)).'.pdf';
    }

    public function asController(ProductDevelopment $productDevelopment, ProductDevelopmentArtwork $artwork, ActionRequest $request): Response
    {
        $this->initialisationFromGroup(group(), $request);

        $version  = $request->integer('version') ?: null;
        $fileName = self::fileName($version ? (clone $artwork)->forceFill(['version' => $version]) : $artwork);

        return response($this->handle($productDevelopment, $artwork, $version), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($request->boolean('inline') ? 'inline' : 'attachment').'; filename="'.$fileName.'"',
        ]);
    }
}
