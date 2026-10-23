<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Media;

use App\Actions\Production\Artefact\Label\MakeArtworkPdfReadable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;
use Mpdf\Mpdf;

class MergePdfs
{
    use AsObject;

    /**
     * Every page keeps its own size, so an A4 invoice and a 6x4 label can share one print job.
     * A document that cannot be read even after rewriting is reported by name rather than left
     * out, so nothing is missing from the parcel without staff knowing.
     *
     * @param  array<int, array{name: string, pdf: string}>  $documents
     */
    public function handle(array $documents): string
    {
        $mpdf           = new Mpdf(['tempDir' => sys_get_temp_dir()]);
        $temporaryFiles = [];
        $unreadable     = [];

        try {
            foreach ($documents as $document) {
                $path             = sys_get_temp_dir().'/'.Str::uuid().'.pdf';
                $temporaryFiles[] = $path;
                file_put_contents($path, $document['pdf']);

                $readable = MakeArtworkPdfReadable::run($path);
                if (!$readable) {
                    $unreadable[] = $document['name'];
                    continue;
                }

                if ($readable['is_temporary']) {
                    $temporaryFiles[] = $readable['path'];
                }

                $pageCount = $mpdf->setSourceFile($readable['path']);
                for ($page = 1; $page <= $pageCount; $page++) {
                    $templateId = $mpdf->importPage($page);
                    $size       = $mpdf->getTemplateSize($templateId);
                    $mpdf->AddPageByArray([
                        'orientation' => $size['height'] > $size['width'] ? 'P' : 'L',
                        'newformat'   => [$size['width'], $size['height']],
                        'mgl'         => 0,
                        'mgr'         => 0,
                        'mgt'         => 0,
                        'mgb'         => 0,
                    ]);
                    $mpdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
                }
            }
        } finally {
            foreach ($temporaryFiles as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    unlink($temporaryFile);
                }
            }
        }

        if ($unreadable) {
            throw ValidationException::withMessages([
                'messages' => __('These documents could not be read, download them from the Attachments tab and print them on their own: :documents', ['documents' => implode(', ', $unreadable)]),
            ]);
        }

        return $mpdf->Output('', 'S');
    }
}
