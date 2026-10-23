<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RRPs, the margins and profits worked out from them, and product documents are for customers only.
 */
class HideRetailPricesFromIrisVisitors
{
    public const array HIDDEN_KEYS = [
        'rrp',
        'rrp_per_unit',
        'profit_per_unit',
        'discounted_margin',
        'discounted_profit',
        'discounted_profit_per_unit',
        'attachments',
        'labeling_guide',
    ];

    /**
     * Web blocks use margin as a style property, so these only go where an RRP sits beside them.
     */
    public const array HIDDEN_KEYS_BESIDE_RRP = [
        'margin',
        'profit',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && !$request->user('retina')) {
            $data           = $response->getData(true);
            $dataForVisitor = self::withoutHiddenKeys($data);

            if ($dataForVisitor !== $data) {
                $response->setData($dataForVisitor);
            }
        }

        return $response;
    }

    /**
     * Web blocks carry API resources that Inertia only turns into arrays when it renders, after these keys are stripped.
     */
    public static function resolved(mixed $data): mixed
    {
        if ($data instanceof Responsable) {
            $response = $data->toResponse(request());
            $data     = method_exists($response, 'getData') ? $response->getData(true) : $data;
        } elseif ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return is_array($data) ? array_map(self::resolved(...), $data) : $data;
    }

    public static function withoutHiddenKeys(mixed $data): mixed
    {
        if ($data instanceof Responsable || $data instanceof Arrayable) {
            $data = self::resolved($data);
        }

        if (!is_array($data)) {
            return $data;
        }

        $hiddenKeys = array_key_exists('rrp', $data) || array_key_exists('rrp_per_unit', $data)
            ? [...self::HIDDEN_KEYS, ...self::HIDDEN_KEYS_BESIDE_RRP]
            : self::HIDDEN_KEYS;

        return array_map(self::withoutHiddenKeys(...), array_diff_key($data, array_flip($hiddenKeys)));
    }
}
