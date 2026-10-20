<?php

/*
 * Author Louis Perez
 * Created on 20-10-2026-21h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentDesignRound;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * A design version as a vector PDF at its real size, drawn in PHP (no browser on the server). The JPG
 * download is made in the browser from this PDF.
 */
class DownloadProductDevelopmentDesign extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public function handle(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRound $designRound): string
    {
        abort_unless($designRound->product_development_id === $productDevelopment->id, 404);

        $pdf = ProductDevelopmentDesignPdf::render($designRound);
        abort_unless($pdf !== null, 404);

        return $pdf;
    }

    public function asController(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRound $designRound, ActionRequest $request): Response
    {
        $this->initialisationFromGroup(group(), $request);

        $fileName = Str::slug($productDevelopment->code.' '.$designRound->round->value.' v'.$designRound->version).'.pdf';

        return response($this->handle($productDevelopment, $designRound), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
