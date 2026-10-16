<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-10h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Models\Goods\ProductDevelopment;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * The box a design version is drawn on: its size in millimetres and what sits on each of its six faces.
 * Element positions are millimetres from the top-left corner of their face, so a layout keeps its scale
 * when the box is resized.
 */
class ProductDevelopmentDesignLayout
{
    public const array FACES = ['front', 'back', 'left', 'right', 'top', 'bottom'];

    public const array ELEMENT_TYPES = ['text', 'image', 'zone'];

    public const array ZONES = ['compliance', 'barcode', 'other'];

    public const int MAX_ELEMENTS_PER_FACE = 60;

    public const int MAX_SIDE_MM = 2000;

    /**
     * @return array<string, mixed>
     */
    public static function blank(ProductDevelopment $productDevelopment): array
    {
        return [
            'units'  => 'mm',
            'width'  => null,
            'length' => null,
            'height' => null,
            ...self::sizeFromFirstSku($productDevelopment),
            'faces'  => collect(self::FACES)->mapWithKeys(fn (string $face) => [$face => ['background' => ['color' => '#ffffff', 'image_file_id' => null], 'elements' => []]])->all(),
        ];
    }

    /**
     * @return array{width?: float, length?: float, height?: float, units?: string}
     */
    private static function sizeFromFirstSku(ProductDevelopment $productDevelopment): array
    {
        $dimensions = $productDevelopment->items()->with('tradeUnit')->orderBy('id')->first()?->tradeUnit?->marketing_dimensions;
        if (!is_array($dimensions)) {
            return [];
        }

        $toMm  = match (Arr::get($dimensions, 'units', 'cm')) {
            'mm' => 1,
            'm' => 1000,
            'inch' => 25.4,
            default => 10,
        };
        $sizes = collect(['width' => 'w', 'length' => 'l', 'height' => 'h'])
            ->map(fn (string $key) => round((float) Arr::get($dimensions, $key, 0) * $toMm, 1));

        if ($sizes->contains(fn (float $size) => $size <= 0)) {
            return [];
        }

        return [...$sizes->all(), 'units' => Arr::get($dimensions, 'units') === 'mm' ? 'mm' : 'cm'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(string $prefix = 'layout'): array
    {
        $element = $prefix.'.faces.*.elements.*';

        return [
            $prefix                          => ['required', 'array'],
            $prefix.'.units'                 => ['required', Rule::in(['mm', 'cm'])],
            $prefix.'.width'                 => ['nullable', 'numeric', 'gt:0', 'max:'.self::MAX_SIDE_MM],
            $prefix.'.length'                => ['nullable', 'numeric', 'gt:0', 'max:'.self::MAX_SIDE_MM],
            $prefix.'.height'                => ['nullable', 'numeric', 'gt:0', 'max:'.self::MAX_SIDE_MM],
            $prefix.'.faces'                 => ['required', 'array:'.implode(',', self::FACES)],
            $prefix.'.faces.*.background'                 => ['nullable', 'array'],
            $prefix.'.faces.*.background.color'           => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            $prefix.'.faces.*.background.image_file_id'   => ['nullable', 'integer'],
            $prefix.'.faces.*.elements'      => ['present', 'array', 'max:'.self::MAX_ELEMENTS_PER_FACE],
            $element.'.id'                   => ['required', 'string', 'max:40'],
            $element.'.type'                 => ['required', Rule::in(self::ELEMENT_TYPES)],
            $element.'.x'                    => ['required', 'numeric'],
            $element.'.y'                    => ['required', 'numeric'],
            $element.'.w'                    => ['required', 'numeric', 'gt:0'],
            $element.'.h'                    => ['required', 'numeric', 'gt:0'],
            $element.'.rotate'               => ['nullable', 'numeric', 'between:-360,360'],
            $element.'.text'                 => ['nullable', 'string', 'max:2000'],
            $element.'.font_size'            => ['nullable', 'numeric', 'between:2,400'],
            $element.'.font_weight'          => ['nullable', Rule::in(['normal', 'bold'])],
            $element.'.italic'               => ['nullable', 'boolean'],
            $element.'.align'                => ['nullable', Rule::in(['left', 'center', 'right'])],
            $element.'.color'                => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            $element.'.font_family'          => ['nullable', 'string', 'max:60'],
            $element.'.image_file_id'        => ['nullable', 'integer'],
            $element.'.zone'                 => ['nullable', Rule::in(self::ZONES)],
            $element.'.label'                => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * Every face present, unknown keys dropped, so the editor can trust the shape it reads back.
     *
     * @param array<string, mixed> $layout
     *
     * @return array<string, mixed>
     */
    public static function normalise(array $layout): array
    {
        $keys = ['id', 'type', 'x', 'y', 'w', 'h', 'rotate', 'text', 'font_size', 'font_weight', 'italic', 'align', 'color', 'font_family', 'image_file_id', 'zone', 'label'];

        return [
            'units'  => Arr::get($layout, 'units', 'mm'),
            'width'  => self::size(Arr::get($layout, 'width')),
            'length' => self::size(Arr::get($layout, 'length')),
            'height' => self::size(Arr::get($layout, 'height')),
            'faces'  => collect(self::FACES)->mapWithKeys(fn (string $face) => [$face => [
                'background' => [
                    'color'         => Arr::get($layout, "faces.$face.background.color", '#ffffff'),
                    'image_file_id' => Arr::get($layout, "faces.$face.background.image_file_id"),
                ],
                'elements'   => collect(Arr::get($layout, "faces.$face.elements", []))
                    ->map(fn (array $element) => Arr::only($element, $keys))
                    ->values()
                    ->all(),
            ]])->all(),
        ];
    }

    private static function size(mixed $value): ?float
    {
        return is_numeric($value) && $value > 0 ? round((float) $value, 1) : null;
    }

    /**
     * @param array<string, mixed> $layout
     */
    public static function hasSize(array $layout): bool
    {
        return self::size(Arr::get($layout, 'width')) && self::size(Arr::get($layout, 'length')) && self::size(Arr::get($layout, 'height'));
    }

    /**
     * @param array<string, mixed> $layout
     *
     * @return array<int, int>
     */
    public static function imageFileIds(array $layout): array
    {
        return collect(Arr::get($layout, 'faces', []))
            ->flatMap(fn (array $face) => [
                Arr::get($face, 'background.image_file_id'),
                ...collect(Arr::get($face, 'elements', []))->pluck('image_file_id')->all(),
            ])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
