<?php

use App\Actions\Dispatching\DeliveryNoteLeaflet\WithPrintDeliveryNoteLeaflet;
use App\Actions\Traits\WithPrintNode;
use Illuminate\Validation\ValidationException;

function leafletImagePdf(string $bytes): string
{
    return (new class () {
        use WithPrintNode;
        use WithPrintDeliveryNoteLeaflet;

        public function build(string $bytes): string
        {
            return $this->imageLeafletPdf($bytes);
        }
    })->build($bytes);
}

function noisyLeafletPng(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            imagesetpixel($image, $x, $y, random_int(0, 0xFFFFFF));
        }
    }
    ob_start();
    imagepng($image);

    return ob_get_clean();
}

test('an image leaflet becomes one A6 page in its own orientation', function (int $width, int $height, string $mediaBox) {
    $png = noisyLeafletPng($width, $height);
    $pdf = leafletImagePdf($png);

    expect($pdf)->toStartWith('%PDF')
        ->and(preg_match_all('/\/Type\s*\/Page\b/', $pdf))->toBe(1)
        ->and($pdf)->toMatch($mediaBox);
})->with([
    'portrait'  => [700, 987, '/MediaBox\s*\[0 0 297\.6\d* 419\.5\d*\]/'],
    'landscape' => [987, 700, '/MediaBox\s*\[0 0 419\.5\d* 297\.6\d*\]/'],
]);

test('an unreadable leaflet image is refused with a message', function () {
    leafletImagePdf('not an image');
})->throws(ValidationException::class);
