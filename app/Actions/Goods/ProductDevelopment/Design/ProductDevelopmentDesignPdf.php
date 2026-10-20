<?php

/*
 * Author Louis Perez
 * Created on 20-10-2026-22h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\UI\GetProductDevelopmentLabelContent;
use App\Actions\Goods\ProductDevelopment\UI\ShowProductDevelopmentDesign;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Models\Goods\ProductDevelopmentDesignRound;
use Illuminate\Support\Arr;
use Mpdf\Mpdf;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Throwable;

/**
 * Draws a design version straight into a vector PDF at its real size with mPDF: the same box net, sides,
 * backgrounds and items as the editor (resources/js/Components/Goods/ProductDevelopment/Design/
 * boxNet.ts, DesignBoxNet.vue and DesignFaceRender.vue), with text kept as text and shapes, dieline and
 * barcodes as vectors. Every item type the editor can place must be drawn here too: ELEMENT_TYPES.
 */
class ProductDevelopmentDesignPdf
{
    public const array ELEMENT_TYPES = ['text', 'image', 'shape', 'data', 'pictogram', 'barcode', 'zone'];

    private const float PX_TO_MM = 0.2646;
    private const float PT_TO_MM = 0.3528;
    private const float LINE_MM = 0.25;
    private const string COMPLIANCE_SEPARATOR = "\n\n";
    private const array COMPLIANCE_PARTS = ['warnings', 'manufacturer', 'eu_responsible', 'ufi', 'un_number'];

    private const array FONTS = [
        'Arial, Helvetica, sans-serif'     => 'dejavusans',
        'Georgia, serif'                   => 'dejavuserif',
        "'Times New Roman', Times, serif"  => 'dejavuserif',
        "'Trebuchet MS', sans-serif"       => 'dejavusans',
        'Verdana, sans-serif'              => 'dejavusans',
        "'Courier New', monospace"         => 'dejavusansmono',
    ];

    private Mpdf $mpdf;
    private int $imageCounter = 0;

    /** @var array<int, string|null> */
    private array $imageBytes = [];

    /** @var array<string, mixed> */
    private array $layout;

    /** @var array{text: array<string, string|null>, pictograms: array<string, bool>, barcodes: array<string, string|null>}|null */
    private ?array $values;

    /** @var array<string, mixed> */
    private array $sources;

    public function __construct(private readonly ProductDevelopmentDesignRound $designRound)
    {
    }

    public static function render(ProductDevelopmentDesignRound $designRound): ?string
    {
        return (new self($designRound))->draw();
    }

    public function draw(): ?string
    {
        $layout = ShowProductDevelopmentDesign::content($this->designRound)['layout'];
        $size   = $layout ? ProductDevelopmentDesignLayout::netSize($layout) : null;
        if (!$size) {
            return null;
        }

        $this->layout  = $layout;
        $this->sources = ProductDevelopmentDesignLayout::dataSources();
        $this->values  = $this->labelValues();

        $this->mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => [$size['width'], $size['height']],
            'orientation'   => 'P',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'default_font'  => 'dejavusans',
            'tempDir'       => storage_path('app/mpdf'),
        ]);
        $this->mpdf->SetDisplayMode('fullpage');
        $this->mpdf->SetTitle($this->designRound->productDevelopment->code.' '.$this->designRound->round->value.' v'.$this->designRound->version);
        $this->mpdf->AddPage();

        foreach (self::panels($layout) as $panel) {
            $this->drawPanel($panel);
        }

        return $this->mpdf->Output('', 'S');
    }

    /**
     * The flat net in millimetres, as buildLayoutNet() in boxNet.ts: one page for a label, else the sides
     * of a reverse tuck end carton with its tuck, dust and glue flaps.
     *
     * @return array<int, array{key: string, face: string|null, x: float, y: float, w: float, h: float}>
     */
    public static function panels(array $layout): array
    {
        $width  = (float) $layout['width'];
        $height = (float) $layout['height'];
        if (Arr::get($layout, 'kind') === ProductDevelopmentDesignLayout::KIND_SHEET) {
            return [['key' => 'sheet', 'face' => 'sheet', 'x' => 0, 'y' => 0, 'w' => $width, 'h' => $height]];
        }

        $length = (float) $layout['length'];
        $tuck   = min(max($length * 0.6, 8), 25);
        $dust   = min($length * 0.5, 30);
        $glue   = min(max($length * 0.35, 8), 15);
        $rowY   = $tuck + $length;
        $xs     = ['left' => 0, 'front' => $length, 'right' => $length + $width, 'back' => 2 * $length + $width];

        return [
            ['key' => 'left', 'face' => 'left', 'x' => $xs['left'], 'y' => $rowY, 'w' => $length, 'h' => $height],
            ['key' => 'front', 'face' => 'front', 'x' => $xs['front'], 'y' => $rowY, 'w' => $width, 'h' => $height],
            ['key' => 'right', 'face' => 'right', 'x' => $xs['right'], 'y' => $rowY, 'w' => $length, 'h' => $height],
            ['key' => 'back', 'face' => 'back', 'x' => $xs['back'], 'y' => $rowY, 'w' => $width, 'h' => $height],
            ['key' => 'glue', 'face' => null, 'x' => $xs['back'] + $width, 'y' => $rowY + $height * 0.05, 'w' => $glue, 'h' => $height * 0.9],
            ['key' => 'top', 'face' => 'top', 'x' => $xs['front'], 'y' => $tuck, 'w' => $width, 'h' => $length],
            ['key' => 'top_tuck', 'face' => null, 'x' => $xs['front'], 'y' => 0, 'w' => $width, 'h' => $tuck],
            ['key' => 'bottom', 'face' => 'bottom', 'x' => $xs['front'], 'y' => $rowY + $height, 'w' => $width, 'h' => $length],
            ['key' => 'bottom_tuck', 'face' => null, 'x' => $xs['front'], 'y' => $rowY + $height + $length, 'w' => $width, 'h' => $tuck],
            ['key' => 'left_dust_top', 'face' => null, 'x' => $xs['left'], 'y' => $rowY - $dust, 'w' => $length, 'h' => $dust],
            ['key' => 'left_dust_bottom', 'face' => null, 'x' => $xs['left'], 'y' => $rowY + $height, 'w' => $length, 'h' => $dust],
            ['key' => 'right_dust_top', 'face' => null, 'x' => $xs['right'], 'y' => $rowY - $dust, 'w' => $length, 'h' => $dust],
            ['key' => 'right_dust_bottom', 'face' => null, 'x' => $xs['right'], 'y' => $rowY + $height, 'w' => $length, 'h' => $dust],
        ];
    }

    /**
     * @param array{key: string, face: string|null, x: float, y: float, w: float, h: float} $panel
     */
    private function drawPanel(array $panel): void
    {
        if (!$panel['face']) {
            $this->svg($panel['x'], $panel['y'], $panel['w'], $panel['h'], $this->rectSvg($panel['w'], $panel['h'], '#f9fafb', '#d1d5db', self::LINE_MM, true));

            return;
        }

        $face = $this->layout['faces'][$panel['face']] ?? ['background' => [], 'elements' => []];

        $this->clip($panel['x'], $panel['y'], $panel['w'], $panel['h'], function () use ($panel, $face) {
            $this->svg($panel['x'], $panel['y'], $panel['w'], $panel['h'], $this->rectSvg($panel['w'], $panel['h'], '#ffffff'));
            $this->drawBackground($panel, $face['background'] ?? []);
            foreach ($face['elements'] ?? [] as $element) {
                $this->drawElement($panel['x'], $panel['y'], $element);
            }
        });

        $this->svg($panel['x'], $panel['y'], $panel['w'], $panel['h'], $this->rectSvg($panel['w'], $panel['h'], 'none', '#9ca3af', self::LINE_MM));
    }

    /**
     * @param array{x: float, y: float, w: float, h: float} $panel
     * @param array<string, mixed> $background
     */
    private function drawBackground(array $panel, array $background): void
    {
        $paint = $background['color'] ?? null;
        if ($paint && $paint !== 'none') {
            $this->svg($panel['x'], $panel['y'], $panel['w'], $panel['h'], $this->rectSvg($panel['w'], $panel['h'], $paint));
        }

        $bytes = $this->imageFor($background['image_file_id'] ?? null);
        $ratio = $bytes ? self::ratio($bytes) : null;
        if (!$bytes || !$ratio) {
            return;
        }

        $box = self::coverBox($panel['w'], $panel['h'], $ratio, (float) ($background['zoom'] ?? 1), (float) ($background['x'] ?? 0.5), (float) ($background['y'] ?? 0.5));
        $this->image($bytes, $panel['x'] + $box['left'], $panel['y'] + $box['top'], $box['width'], $box['height']);
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawElement(float $originX, float $originY, array $element): void
    {
        $x = $originX + (float) ($element['x'] ?? 0);
        $y = $originY + (float) ($element['y'] ?? 0);
        $w = (float) ($element['w'] ?? 0);
        $h = (float) ($element['h'] ?? 0);
        if ($w <= 0 || $h <= 0) {
            return;
        }

        $rotate = (float) ($element['rotate'] ?? 0);
        if ($rotate) {
            $this->mpdf->StartTransform();
            $this->mpdf->transformRotate($rotate, $x + $w / 2, $y + $h / 2);
        }

        match ($element['type'] ?? null) {
            'text'      => $this->text($x, $y, $w, $h, (string) ($element['text'] ?? ''), $element),
            'image'     => $this->drawImageElement($x, $y, $w, $h, $element),
            'shape'     => $this->svg($x, $y, $w, $h, $this->shapeSvg($w, $h, $element)),
            'data'      => $this->drawData($x, $y, $w, $h, $element),
            'pictogram' => $this->drawPictogram($x, $y, $w, $h, $element),
            'barcode'   => $this->drawBarcode($x, $y, $w, $h, $element['source'] ?? 'unit', $element),
            'zone'      => $this->drawZone($x, $y, $w, $h, $element),
            default     => null,
        };

        if ($rotate) {
            $this->mpdf->StopTransform();
        }
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawImageElement(float $x, float $y, float $w, float $h, array $element): void
    {
        $bytes = $this->imageFor($element['image_file_id'] ?? null);
        if (!$bytes) {
            $this->placeholder($x, $y, $w, $h, __('Image'), '#f9fafb', '#d1d5db', '#9ca3af');

            return;
        }
        $this->contain($bytes, $x, $y, $w, $h);
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawData(float $x, float $y, float $w, float $h, array $element): void
    {
        $source = (string) ($element['source'] ?? '');
        $value  = $this->values ? ($this->values['text'][$source] ?? null) : null;
        if ($value) {
            $this->text($x, $y, $w, $h, (string) $value, $element);

            return;
        }

        $label = collect($this->sources['text'])->firstWhere('key', $source)['label'] ?? $source;
        $this->placeholder($x, $y, $w, $h, '['.$label.']', '#f0f9ff', '#7dd3fc', '#0369a1', (float) ($element['font_size'] ?? 10), true, $element['font_family'] ?? null, 'left');
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawPictogram(float $x, float $y, float $w, float $h, array $element): void
    {
        $source    = (string) ($element['source'] ?? '');
        $pictogram = collect($this->sources['pictograms'])->firstWhere('key', $source);
        $path      = $pictogram ? public_path(ltrim($pictogram['icon'], '/')) : null;
        if (!$path || !is_file($path)) {
            return;
        }

        $faded = $this->values && !($this->values['pictograms'][$source] ?? false);
        if ($faded) {
            $this->mpdf->SetAlpha(0.3);
        }
        $this->contain((string) file_get_contents($path), $x, $y, $w, $h);
        if ($faded) {
            $this->mpdf->SetAlpha(1);
        }
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawZone(float $x, float $y, float $w, float $h, array $element): void
    {
        $zone = $element['zone'] ?? 'other';

        if ($zone === 'barcode' && $this->barcodeValue($element['source'] ?? 'unit')) {
            $this->drawBarcode($x, $y, $w, $h, $element['source'] ?? 'unit', $element);

            return;
        }

        if ($zone === 'compliance' && ($text = $this->complianceText($element['source'] ?? null))) {
            $this->text($x, $y, $w, $h, $text, [...$element, 'font_size' => $element['font_size'] ?? 6, 'align' => $element['align'] ?? 'left']);

            return;
        }

        $label = ($element['label'] ?? null) ?: match ($zone) {
            'barcode'    => 'EAN-13',
            'compliance' => __('Reserved: compliance text'),
            default      => __('Reserved'),
        };

        if ($zone === 'barcode') {
            $this->barcodePlaceholder($x, $y, $w, $h, $label);

            return;
        }

        $zone === 'compliance'
            ? $this->placeholder($x, $y, $w, $h, $label, '#fff7ed', '#fb923c', '#c2410c')
            : $this->placeholder($x, $y, $w, $h, $label, '#ffffff', '#9ca3af', '#4b5563');
    }

    /**
     * @param array<string, mixed> $element
     */
    private function drawBarcode(float $x, float $y, float $w, float $h, string $source, array $element): void
    {
        $value = $this->barcodeValue($source);
        if (!$value) {
            $label = collect($this->sources['barcodes'])->firstWhere('key', $source)['label'] ?? 'EAN-13';
            $this->barcodePlaceholder($x, $y, $w, $h, $label);

            return;
        }

        $type = preg_match('/^\d{13}$/', $value) ? BarcodeGenerator::TYPE_EAN_13 : BarcodeGenerator::TYPE_CODE_128;
        try {
            $bars = (new BarcodeGeneratorSVG())->getBarcode($value, $type, 2, 60);
        } catch (Throwable) {
            $this->barcodePlaceholder($x, $y, $w, $h, $value);

            return;
        }

        $textHeight = min($h * 0.22, 4);
        $this->svg($x, $y, $w, $h - $textHeight, self::stretchSvg($bars, $w, $h - $textHeight));
        $this->text($x, $y + $h - $textHeight, $w, $textHeight, $value, ['font_size' => max(min($textHeight / 0.3528 * 0.85, 9), 4), 'align' => 'center', 'color' => $element['color'] ?? '#111827']);
    }

    private function barcodePlaceholder(float $x, float $y, float $w, float $h, string $label): void
    {
        $this->svg($x, $y, $w, $h, $this->rectSvg($w, $h, '#ffffff', '#9ca3af', self::LINE_MM, true));
        $labelHeight = min($h * 0.3, 3.5);
        $barsHeight  = max($h - $labelHeight - 1.2, 1);
        $bars        = '';
        for ($barX = $w * 0.1; $barX < $w * 0.9; $barX += 0.8) {
            $bars .= sprintf('<rect x="%.3F" y="0" width="0.27" height="%.3F" fill="#374151"/>', $barX, $barsHeight);
        }
        $this->svg($x, $y + 0.6, $w, $barsHeight, sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%1$.3Fmm" height="%2$.3Fmm" viewBox="0 0 %1$.3F %2$.3F">%3$s</svg>', $w, $barsHeight, $bars));
        $this->text($x, $y + $h - $labelHeight - 0.3, $w, $labelHeight, $label, ['font_size' => 6, 'align' => 'center', 'color' => '#4b5563']);
    }

    /**
     * A dashed box with its label in the middle, as the editor draws an empty data field or reserved area.
     */
    private function placeholder(float $x, float $y, float $w, float $h, string $label, string $fill, string $stroke, string $color, float $fontSize = 6, bool $italic = false, ?string $fontFamily = null, string $align = 'center'): void
    {
        $this->svg($x, $y, $w, $h, $this->rectSvg($w, $h, $fill, $stroke, self::LINE_MM, true));
        $lineHeight = $fontSize * self::PT_TO_MM * 1.2;
        $this->mpdf->WriteFixedPosHTML(
            sprintf(
                '<div style="padding:%.3Fmm 1mm 0 1mm;line-height:1.2;text-align:%s;font-family:%s;font-size:%.2Fpt;color:%s;%s">%s</div>',
                max(($h - $lineHeight) / 2, 0),
                $align,
                $this->font($fontFamily),
                $fontSize,
                $color,
                $italic ? 'font-style:italic;' : '',
                e($label)
            ),
            $x,
            $y,
            $w,
            $h,
            'hidden'
        );
    }

    /**
     * Text as the editor shows it: top-aligned, wrapping, lines 1.2 high, cut at the edge of its box.
     *
     * @param array<string, mixed> $style
     */
    private function text(float $x, float $y, float $w, float $h, string $text, array $style): void
    {
        $paint = self::parsePaint($style['color'] ?? null, '#111827');
        if ($paint['mode'] === 'none' || trim($text) === '') {
            return;
        }

        $html = sprintf(
            '<div style="font-family:%s;font-size:%.2Fpt;line-height:1.2;color:%s;text-align:%s;%s%s">%s</div>',
            $this->font($style['font_family'] ?? null),
            (float) ($style['font_size'] ?? 10),
            $paint['from'],
            in_array($style['align'] ?? 'center', ['left', 'center', 'right'], true) ? $style['align'] ?? 'center' : 'center',
            ($style['font_weight'] ?? 'normal') === 'bold' ? 'font-weight:bold;' : '',
            !empty($style['italic']) ? 'font-style:italic;' : '',
            nl2br(e($text), false)
        );
        $this->mpdf->WriteFixedPosHTML($html, $x, $y, $w, $h, 'hidden');
    }

    private function font(?string $family): string
    {
        return self::FONTS[$family ?? ''] ?? 'dejavusans';
    }

    /**
     * @param array<string, mixed> $element
     */
    private function shapeSvg(float $w, float $h, array $element): string
    {
        $stroke    = ($element['stroke'] ?? null) && $element['stroke'] !== 'none' ? $element['stroke'] : null;
        $strokeMm  = $stroke ? (float) ($element['stroke_px'] ?? 1) * self::PX_TO_MM : 0;
        $shape     = $element['shape'] ?? 'rect';
        $paint     = self::parsePaint($element['fill'] ?? null, '#d1d5db');
        [$defs, $fill] = $this->fillFor($paint);
        $strokeAttributes = $stroke ? sprintf(' stroke="%s" stroke-width="%.3F"', e($stroke), $strokeMm) : ' stroke="none"';
        $half      = $strokeMm / 2;

        $body = match ($shape) {
            'line'     => sprintf('<line x1="0" y1="%1$.3F" x2="%2$.3F" y2="%1$.3F" stroke="%3$s" stroke-width="%4$.3F"/>', $h / 2, $w, e($stroke ?? '#111827'), max($strokeMm, 0.2)),
            'ellipse'  => sprintf('<ellipse cx="%.3F" cy="%.3F" rx="%.3F" ry="%.3F" fill="%s"%s/>', $w / 2, $h / 2, max($w / 2 - $half, 0), max($h / 2 - $half, 0), $fill, $strokeAttributes),
            'triangle' => sprintf('<polygon points="%.3F,%.3F %.3F,%.3F %.3F,%.3F" stroke-linejoin="round" fill="%s"%s/>', $w / 2, $half, $w - $half, $h - $half, $half, $h - $half, $fill, $strokeAttributes),
            default    => sprintf(
                '<rect x="%.3F" y="%.3F" width="%.3F" height="%.3F" rx="%.3F" fill="%s"%s/>',
                $half,
                $half,
                max($w - $strokeMm, 0),
                max($h - $strokeMm, 0),
                $shape === 'rounded' ? min((float) ($element['radius'] ?? 3), $w / 2, $h / 2) : 0,
                $fill,
                $strokeAttributes
            ),
        };

        return sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%1$.3Fmm" height="%2$.3Fmm" viewBox="0 0 %1$.3F %2$.3F" preserveAspectRatio="none">%3$s%4$s</svg>', $w, $h, $defs, $body);
    }

    private function rectSvg(float $w, float $h, string $paintValue, ?string $stroke = null, float $strokeMm = 0, bool $dashed = false): string
    {
        [$defs, $fill] = $this->fillFor(self::parsePaint($paintValue, '#ffffff'));
        $inset         = $strokeMm / 2;
        $strokeAttributes = $stroke ? sprintf(' stroke="%s" stroke-width="%.3F"%s', $stroke, $strokeMm, $dashed ? ' stroke-dasharray="1 0.7"' : '') : '';

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$.3Fmm" height="%2$.3Fmm" viewBox="0 0 %1$.3F %2$.3F" preserveAspectRatio="none">%3$s<rect x="%4$.3F" y="%4$.3F" width="%5$.3F" height="%6$.3F" fill="%7$s"%8$s/></svg>',
            $w,
            $h,
            $defs,
            $inset,
            max($w - $strokeMm, 0),
            max($h - $strokeMm, 0),
            $fill,
            $strokeAttributes
        );
    }

    /**
     * @param array{mode: string, from: string, to: string, angle: float} $paint
     * @return array{0: string, 1: string} the gradient definition, and the fill to use
     */
    private function fillFor(array $paint): array
    {
        if ($paint['mode'] === 'none') {
            return ['', 'none'];
        }
        if ($paint['mode'] === 'solid') {
            return ['', e($paint['from'])];
        }

        $id    = 'g'.(++$this->imageCounter);
        $stops = sprintf('<stop offset="0" stop-color="%s"/><stop offset="1" stop-color="%s"/>', e($paint['from']), e($paint['to']));
        if ($paint['mode'] === 'radial') {
            return [sprintf('<defs><radialGradient id="%s" cx="0.5" cy="0.5" r="0.5">%s</radialGradient></defs>', $id, $stops), 'url(#'.$id.')'];
        }

        $radians = deg2rad($paint['angle']);
        $dx      = sin($radians) / 2;
        $dy      = -cos($radians) / 2;

        return [
            sprintf('<defs><linearGradient id="%s" x1="%.4F" y1="%.4F" x2="%.4F" y2="%.4F">%s</linearGradient></defs>', $id, 0.5 - $dx, 0.5 - $dy, 0.5 + $dx, 0.5 + $dy, $stops),
            'url(#'.$id.')',
        ];
    }

    /**
     * A paint is a hex colour, "none", "linear:<angle>:#from:#to" or "radial:#from:#to", as parsePaint() in boxNet.ts.
     *
     * @return array{mode: string, from: string, to: string, angle: float}
     */
    public static function parsePaint(?string $value, string $fallback): array
    {
        if (!$value) {
            return ['mode' => 'solid', 'from' => $fallback, 'to' => $fallback, 'angle' => 90];
        }
        if ($value === 'none') {
            return ['mode' => 'none', 'from' => $fallback, 'to' => $fallback, 'angle' => 90];
        }
        if (preg_match('/^linear:(\d{1,3}):(#[0-9a-fA-F]{6}):(#[0-9a-fA-F]{6})$/', $value, $match)) {
            return ['mode' => 'linear', 'angle' => (float) $match[1], 'from' => $match[2], 'to' => $match[3]];
        }
        if (preg_match('/^radial:(#[0-9a-fA-F]{6}):(#[0-9a-fA-F]{6})$/', $value, $match)) {
            return ['mode' => 'radial', 'angle' => 90, 'from' => $match[1], 'to' => $match[2]];
        }

        return ['mode' => 'solid', 'from' => $value, 'to' => $value, 'angle' => 90];
    }

    /**
     * Where a background image sits on a side: it always covers the side, as coverBox() in boxNet.ts.
     *
     * @return array{left: float, top: float, width: float, height: float}
     */
    public static function coverBox(float $faceWidth, float $faceHeight, float $ratio, float $zoom = 1, float $x = 0.5, float $y = 0.5): array
    {
        $safeRatio = $ratio > 0 ? $ratio : $faceHeight / max($faceWidth, 1);
        $width     = $faceWidth;
        $height    = $faceWidth * $safeRatio;
        if ($height < $faceHeight) {
            $height = $faceHeight;
            $width  = $faceHeight / $safeRatio;
        }
        $width  *= $zoom;
        $height *= $zoom;

        return ['width' => $width, 'height' => $height, 'left' => -($width - $faceWidth) * $x, 'top' => -($height - $faceHeight) * $y];
    }

    private function contain(string $bytes, float $x, float $y, float $w, float $h): void
    {
        $ratio = self::ratio($bytes);
        if (!$ratio) {
            return;
        }
        $drawWidth  = min($w, $h / $ratio);
        $drawHeight = $drawWidth * $ratio;
        $this->image($bytes, $x + ($w - $drawWidth) / 2, $y + ($h - $drawHeight) / 2, $drawWidth, $drawHeight);
    }

    /** height ÷ width of an image; SVGs from their width and height or viewBox */
    private static function ratio(string $bytes): ?float
    {
        $info = @getimagesizefromstring($bytes);
        if ($info && $info[0] > 0) {
            return $info[1] / $info[0];
        }
        if (preg_match('/viewBox="[\d.\-]+[\s,]+[\d.\-]+[\s,]+([\d.]+)[\s,]+([\d.]+)"/', $bytes, $match) && (float) $match[1] > 0) {
            return (float) $match[2] / (float) $match[1];
        }

        return null;
    }

    private function image(string $bytes, float $x, float $y, float $w, float $h): void
    {
        $key                         = 'image'.(++$this->imageCounter);
        $this->mpdf->imageVars[$key] = $bytes;
        $this->mpdf->Image('var:'.$key, $x, $y, $w, $h, '', '', true, false);
    }

    private function svg(float $x, float $y, float $w, float $h, string $svg): void
    {
        if ($w > 0 && $h > 0) {
            $this->image($svg, $x, $y, $w, $h);
        }
    }

    /** A generated SVG stretched to fill a box, as the editor stretches the barcode to its area */
    private static function stretchSvg(string $svg, float $w, float $h): string
    {
        if (!preg_match('/<svg\b[^>]*\bwidth="([\d.]+)"[^>]*\bheight="([\d.]+)"[^>]*>(.*)<\/svg>/s', $svg, $match)) {
            return $svg;
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%.3Fmm" height="%.3Fmm" viewBox="0 0 %s %s" preserveAspectRatio="none">%s</svg>',
            $w,
            $h,
            $match[1],
            $match[2],
            preg_replace('/<desc>.*?<\/desc>/s', '', $match[3])
        );
    }

    /**
     * Draws inside a rectangle only, as the sides cut off anything that runs over their edge.
     */
    private function clip(float $x, float $y, float $w, float $h, callable $draw): void
    {
        $scale = Mpdf::SCALE;
        $this->mpdf->_out(sprintf('q %.3F %.3F %.3F %.3F re W n', $x * $scale, ($this->mpdf->h - $y) * $scale, $w * $scale, -$h * $scale));
        $draw();
        $this->mpdf->_out('Q');
    }

    private function imageFor(mixed $fileId): ?string
    {
        if (!$fileId) {
            return null;
        }
        if (!array_key_exists((int) $fileId, $this->imageBytes)) {
            $file = $this->designRound->productDevelopment->files()
                ->where('scope', ProductDevelopmentFileScopeEnum::DESIGN_IMAGE->value)
                ->with('media')
                ->find((int) $fileId);
            try {
                $stream                             = $file?->media?->stream();
                $this->imageBytes[(int) $fileId] = $stream ? stream_get_contents($stream) : null;
            } catch (Throwable) {
                $this->imageBytes[(int) $fileId] = null;
            }
        }

        return $this->imageBytes[(int) $fileId] ?: null;
    }

    private function barcodeValue(?string $source): ?string
    {
        return $this->values ? ($this->values['barcodes'][$source ?: 'unit'] ?? null) : null;
    }

    private function complianceText(?string $source): ?string
    {
        if (!$this->values) {
            return null;
        }
        $parts = $source ? array_values(array_intersect(explode(',', $source), self::COMPLIANCE_PARTS)) : self::COMPLIANCE_PARTS;
        $lines = array_filter(array_map(fn (string $part) => $this->values['text'][$part] ?? null, $parts));

        return $lines ? implode(self::COMPLIANCE_SEPARATOR, $lines) : null;
    }

    /**
     * @return array{text: array<string, string|null>, pictograms: array<string, bool>, barcodes: array<string, string|null>}|null
     */
    private function labelValues(): ?array
    {
        $itemId = $this->designRound->product_development_item_id;
        if (!$itemId) {
            return null;
        }
        $productDevelopment = $this->designRound->productDevelopment;
        $item               = $productDevelopment->items()->with('tradeUnit')->find($itemId);

        return $item ? GetProductDevelopmentLabelContent::run($productDevelopment, $item)['values'] : null;
    }
}
