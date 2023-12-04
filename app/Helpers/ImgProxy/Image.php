<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 06 Aug 2023 12:39:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Helpers\ImgProxy;

use App\Helpers\ImgProxy\Exceptions\InvalidFormat;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Image
{
    public const DEFAULT_RESIZE = 'fit';
    /**
     * north (top edge)
     */
    public const DEFAULT_GRAVITY = 'no';
    public const MAX_ENLARGE     = 5;
    public const MIN_ENLARGE     = 0;


    protected ?string $sizeProcessOption = null;

    protected ?array $resize = null;

    protected int $width = 0;

    protected int $height = 0;
    protected string $gravity;
    protected int $enlarge;

    protected ?string $extension = null;

    protected mixed $url;

    protected ?string $preset = null;

    public bool $is_animated = false;


    public function make(string $path, $is_animated = false): static
    {
        $this->setOriginalPictureUrl($path);

        $this->is_animated = $is_animated;

        return $this;
    }


    public function getSizeProcessOption(): ?string
    {
        return $this->sizeProcessOption;
    }

    public function makePreset(string $path, string $preset, $extension = null): static
    {
        $this->setOriginalPictureUrl($path)
            ->setPreset($preset)
            ->extension($extension);

        return $this;
    }

    public function getPreset(): ?string
    {
        return $this->preset;
    }

    public function setPreset($preset): static
    {
        $this->preset = $preset;

        return $this;
    }

    public function resize($width = null, $height = null, $type = null, $enlarge = null, $extend = null): static
    {
        $this->sizeProcessOption = 'resize';
        $this->resize            = [
            'type'    => null,
            'width'   => null,
            'height'  => null,
            'enlarge' => null,
            'extend'  => null,


        ];

        if (!is_null($type) && Arr::get(['fit', 'fill', 'fill-down', 'force', 'auto'], $type)) {
            $this->resize['type'] = $type;
        }

        if (!is_null($width)) {
            $this->width           = $this->parseDimension($width);
            $this->resize['width'] = $this->width;
        }
        if (!is_null($height)) {
            $this->height           = $this->parseDimension($height);
            $this->resize['height'] = $this->height;
        }

        if ($enlarge) {
            $this->resize['enlarge'] = 1;
        }
        if ($extend) {
            $this->resize['extend'] = 1;
        }


        return $this;
    }


    public function getResize(): ?array
    {
        return $this->resize;
    }

    public function parseDimension(int $width)
    {
        $width = abs($width);
        if ($width > config('img-proxy.max_dim_px')) {
            $width = config('img-proxy.max_dim_px');
        }

        return $width;
    }

    public function setWidth(?int $width = 1): static
    {
        if (is_null($width)) {
            return $this;
        }

        $width = abs($width) ?: 1;
        if ($width > config('img-proxy.max_dim_px')) {
            $width = config('img-proxy.max_dim_px');
        }
        $this->width = $width;

        return $this;
    }


    public function getWidth(): int
    {
        return $this->width;
    }


    public function getHeight(): int
    {
        return $this->height;
    }


    public function setGravity(string $argument1 = null): static
    {
        $argument1     = Str::lower($argument1);
        $this->gravity = (!in_array($argument1, config('img-proxy.gravity_values')))
            ? self::DEFAULT_GRAVITY
            : $argument1;

        return $this;
    }


    public function getGravity(): string
    {
        return $this->gravity;
    }


    public function extension($extension): static
    {
        if ($extension) {
            $extension = Str::lower($extension);

            if (!in_array($extension, config('img-proxy.formats'))) {
                throw new InvalidFormat($extension);
            }
        }
        if (!$extension) {
            $extension = '';
        }
        $this->extension = $extension;

        return $this;
    }


    public function getExtension(): ?string
    {
        return $this->extension;
    }


    public function setOriginalPictureUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }


    public function getOriginalPictureUrl(): string
    {
        return $this->url;
    }
}
