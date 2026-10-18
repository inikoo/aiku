<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 24 Sep 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrder;

use App\Enums\EnumHelperTrait;

/**
 * The PO journey board's columns: the seven stages of a purchase order, worded as on the order, and the day its
 * products went online, which is not a stage.
 */
enum PurchaseOrderJourneyStageEnum: string
{
    use EnumHelperTrait;

    case SUBMITTED = 'submitted';
    case DEPOSIT_PAID = 'deposit_paid';
    case IN_PRODUCTION = 'in_production';
    case READY = 'ready';
    case SHIPPED = 'shipped';
    case ARRIVED = 'arrived';
    case RECEIVED = 'received';
    case PRODUCTS_ONLINE = 'products_online';

    public static function labels(): array
    {
        return [
            ...PurchaseOrderStageEnum::labels(),
            'products_online' => __('Products online'),
        ];
    }

    public static function descriptions(): array
    {
        return [
            ...PurchaseOrderStageEnum::descriptions(),
            'shipped'         => __('Goods handed over complete and checked, and left the supplier'),
            'products_online' => __('Every product from the PO created and on sale on the website'),
        ];
    }

    public static function waitingLabels(): array
    {
        return [
            'submitted'       => __('Not sent yet'),
            'deposit_paid'    => __('Waiting for deposit'),
            'in_production'   => __('Waiting for production'),
            'ready'           => __('Waiting for QC'),
            'shipped'         => __('Waiting for dispatch'),
            'arrived'         => __('In transit'),
            'received'        => __('Arrived, on its way to the warehouse'),
            'products_online' => __('Waiting to go online'),
        ];
    }

    /**
     * Stages a person records, so the board can not see them by itself: the deposit (as a payment), production
     * start and QC.
     */
    public function isRecordedByPeople(): bool
    {
        return in_array($this, [self::DEPOSIT_PAID, self::IN_PRODUCTION, self::READY]);
    }

    public function markColumn(): ?string
    {
        return match ($this) {
            self::IN_PRODUCTION => 'production_started_at',
            self::READY         => 'qc_passed_at',
            default             => null,
        };
    }

    /**
     * Days a stage gets after the previous one is done, per journey (agent, supplier or partner).
     */
    public function defaultDays(string $journey): int
    {
        return match ($this) {
            self::SUBMITTED       => $journey === 'agent' ? 7 : 3,
            self::DEPOSIT_PAID    => 7,
            self::IN_PRODUCTION   => $journey === 'agent' ? 21 : 7,
            self::READY           => $journey === 'agent' ? 40 : 19,
            self::SHIPPED         => match ($journey) {
                'agent'   => 10,
                'partner' => 21,
                default   => 14,
            },
            self::ARRIVED         => match ($journey) {
                'agent'   => 31,
                'partner' => 3,
                default   => 6,
            },
            self::RECEIVED        => match ($journey) {
                'agent'   => 4,
                default   => 1,
            },
            self::PRODUCTS_ONLINE => 7,
        };
    }
}
