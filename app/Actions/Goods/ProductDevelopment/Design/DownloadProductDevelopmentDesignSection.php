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
use App\Models\Goods\ProductDevelopmentDesignSection;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

/**
 * Every current artwork PDF of a design section in one ZIP.
 */
class DownloadProductDevelopmentDesignSection extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public function handle(ProductDevelopment $productDevelopment, ProductDevelopmentDesignSection $section): string
    {
        abort_unless($section->product_development_id === $productDevelopment->id, 404);

        $artworks = $section->artworks()->with(['item.tradeUnit', 'media', 'section', 'productDevelopment'])->get()
            ->filter(fn (ProductDevelopmentArtwork $artwork) => $artwork->media_id || $artwork->source === ProductDevelopmentArtwork::SOURCE_AIKU);

        $zipPath = tempnam(sys_get_temp_dir(), 'pd-artwork');
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE);

        $added = 0;
        foreach ($artworks as $artwork) {
            $bytes = DownloadProductDevelopmentArtwork::bytes($artwork);
            if ($bytes !== null) {
                $zip->addFromString(DownloadProductDevelopmentArtwork::fileName($artwork), $bytes);
                $added++;
            }
        }
        $zip->close();

        $content = $added ? file_get_contents($zipPath) : null;
        @unlink($zipPath);
        abort_unless($content !== null, 404);

        return $content;
    }

    public function asController(ProductDevelopment $productDevelopment, ProductDevelopmentDesignSection $section, ActionRequest $request): Response
    {
        $this->initialisationFromGroup(group(), $request);

        $fileName = Str::slug($productDevelopment->code.' '.$section->name).'.zip';

        return response($this->handle($productDevelopment, $section), 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
