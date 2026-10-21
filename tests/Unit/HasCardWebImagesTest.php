<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Jul 2026 00:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Http\Resources\Traits\HasCardWebImages;

$subject = new class () {
    use HasCardWebImages;

    public function cardWebImages(mixed $webImages): array
    {
        return $this->getCardWebImages($webImages);
    }
};

$gallery = fn (string $src) => ['original' => $src, 'webp' => "$src.webp"];

test('secondary falls back to first non-main image in all', function () use ($subject, $gallery) {
    $webImages = [
        'main' => ['gallery' => $gallery('main.jpg')],
        'all'  => [
            ['gallery' => $gallery('main.jpg')],
            ['gallery' => $gallery('second.jpg')],
        ],
    ];

    $result = $subject->cardWebImages($webImages);

    expect($result['secondary']['gallery']['original'])->toBe('second.jpg');
});

test('secondary omitted when all only contains the main image', function () use ($subject, $gallery) {
    $result = $subject->cardWebImages([
        'main' => ['gallery' => $gallery('main.jpg')],
        'all'  => [['gallery' => $gallery('main.jpg')]],
    ]);

    expect($result)->not->toHaveKey('secondary');
});

test('explicit secondary wins over fallback', function () use ($subject, $gallery) {
    $result = $subject->cardWebImages([
        'main'      => ['gallery' => $gallery('main.jpg')],
        'secondary' => ['gallery' => $gallery('front.jpg')],
        'all'       => [['gallery' => $gallery('other.jpg')]],
    ]);

    expect($result['secondary']['gallery']['original'])->toBe('front.jpg');
});

test('card galleries offer avif and the original, never webp', function () use ($subject, $gallery) {
    $result = $subject->cardWebImages([
        'main' => ['gallery' => $gallery('main.jpg') + ['webp_2x' => 'main.jpg.2x.webp', 'avif' => 'main.jpg.avif']],
    ]);

    expect($result['main']['gallery'])->toEqual(['avif' => 'main.jpg.avif', 'original' => 'main.jpg']);
});

test('storefront webpage data drops every webp image url', function () {
    $webpageData = [
        'title'  => 'webp',
        'blocks' => [
            ['image' => ['original' => 'a.jpg', 'avif' => 'a.avif', 'webp' => 'a.webp', 'webp_2x' => 'a2.webp']],
            ['srcset' => ['original' => 'a.jpg 320w', 'webp' => 'a.webp 320w']],
            ['settings' => ['webp' => ['kept' => true]]],
        ],
    ];

    expect(App\Actions\Web\Webpage\Iris\ShowIrisWebpage::make()->withoutWebpImages($webpageData))->toBe([
        'title'  => 'webp',
        'blocks' => [
            ['image' => ['original' => 'a.jpg', 'avif' => 'a.avif']],
            ['srcset' => ['original' => 'a.jpg 320w']],
            ['settings' => ['webp' => ['kept' => true]]],
        ],
    ]);
});
