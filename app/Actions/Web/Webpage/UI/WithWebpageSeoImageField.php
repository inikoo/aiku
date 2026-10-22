<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Models\Web\Webpage;
use Illuminate\Support\Arr;

trait WithWebpageSeoImageField
{
    public function webpageSeoImageField(Webpage $webpage): array
    {
        return [
            'type'        => 'image_crop_square',
            'label'       => __('Share image'),
            'value'       => $webpage->seo_image_url
                ? ['original' => $webpage->seo_image_url]
                : $webpage->imageSources(1200, 1200, 'seoImage'),
            'information' => __('The preview image (og:image) shown when the page is shared on social media (i.e Whatsapp, Facebook). It is not shown on the page itself. Crop ratio from 1:1 to 3:1, served scaled down to fit 1200x1200 pixels.'),
            'hasOther'    => [
                [
                    'name'        => 'seo_image_alt',
                    'type'        => 'alt',
                    'value'       => Arr::get($webpage->seo_data, 'image_alt'),
                    'label'       => __('Share image alt text'),
                    'placeholder' => __('Describe the image'),
                    'information' => __('Alternative text of the share image, used by screen readers and shown when the image cannot be loaded. Will use the Meta Title if missing.'),
                ],
                [
                    'name'        => 'seo_image_url',
                    'type'        => 'url',
                    'value'       => $webpage->seo_image_url,
                    'label'       => __('Or paste an image link'),
                    'placeholder' => 'https://',
                    'information' => __('Use an externally hosted image as the share image instead of uploading one. It takes precedence over the uploaded image; uploading a new image clears it.'),
                ],
            ],
            'options'     => [
                'minAspectRatio' => 1,
                'maxAspectRatio' => 12 / 4,
            ],
        ];
    }
}
