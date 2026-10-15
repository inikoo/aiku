<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 09:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrder;

use App\Enums\EnumHelperTrait;

/**
 * The seven stages a supplier purchase order goes through. The first four are recorded on the purchase order,
 * the last three on each of its stock deliveries, because one order can ship in several deliveries.
 */
enum PurchaseOrderStageEnum: string
{
    use EnumHelperTrait;

    case SUBMITTED = 'submitted';
    case DEPOSIT_PAID = 'deposit_paid';
    case IN_PRODUCTION = 'in_production';
    case READY = 'ready';
    case SHIPPED = 'shipped';
    case ARRIVED = 'arrived';
    case RECEIVED = 'received';

    /**
     * Dates a person records on the purchase order, with the stage each belongs to.
     */
    public const array MARKABLE_FIELDS = [
        'deposit_paid_at'       => self::DEPOSIT_PAID,
        'sample_approved_at'    => self::IN_PRODUCTION,
        'production_started_at' => self::IN_PRODUCTION,
        'qc_passed_at'          => self::READY,
    ];

    public const array AGENT_MARKABLE_FIELDS = [
        'deposit_paid_at',
        'production_started_at',
    ];

    public static function labels(): array
    {
        return [
            'submitted'     => __('PO submitted'),
            'deposit_paid'  => __('Deposit paid'),
            'in_production' => __('In production'),
            'ready'         => __('Ready & QC passed'),
            'shipped'       => __('Shipped'),
            'arrived'       => __('Arrived & cleared'),
            'received'      => __('Received at warehouse'),
        ];
    }

    public static function descriptions(): array
    {
        return [
            'submitted'     => __('The order was sent to the supplier or agent'),
            'deposit_paid'  => __('The supplier has the deposit'),
            'in_production' => __('Sample approved and the goods are being made'),
            'ready'         => __('Goods made and their quality check approved'),
            'shipped'       => __('Goods left the supplier'),
            'arrived'       => __('Goods arrived at the port and cleared customs'),
            'received'      => __('Goods received at our warehouse'),
        ];
    }

    public static function fieldLabels(): array
    {
        return [
            'submitted_at'           => __('Submitted'),
            'confirmed_at'           => __('Accepted'),
            'deposit_paid_at'        => __('Deposit paid'),
            'sample_approved_at'     => __('Sample approved'),
            'production_started_at'  => __('Production started'),
            'proposed_ready_at'      => __('Proposed ready date'),
            'approved_ready_at'      => __('Approved ready date'),
            'produced_at'            => __('Production done'),
            'qc_passed_at'           => __('QC approved'),
            'balance_paid_at'        => __('Balance paid'),
            'handed_over_at'         => __('Handed over'),
            'dispatched_at'          => __('Dispatched'),
            'arrived_at'             => __('Port arrival'),
            'customs_released_at'    => __('Customs released'),
            'compliance_complete_at' => __('Compliance complete'),
            'received_at'            => __('Received'),
            'checked_at'             => __('Checked'),
            'placed_at'              => __('Placed'),
        ];
    }

    /**
     * The date that says the order reached this stage.
     */
    public function column(): string
    {
        return match ($this) {
            self::SUBMITTED     => 'submitted_at',
            self::DEPOSIT_PAID  => 'deposit_paid_at',
            self::IN_PRODUCTION => 'production_started_at',
            self::READY         => 'qc_passed_at',
            self::SHIPPED       => 'dispatched_at',
            self::ARRIVED       => 'arrived_at',
            self::RECEIVED      => 'received_at',
        };
    }

    /**
     * Other dates recorded along the way, shown under the stage.
     */
    public function steps(): array
    {
        return match ($this) {
            self::SUBMITTED     => ['confirmed_at'],
            self::DEPOSIT_PAID  => [],
            self::IN_PRODUCTION => ['sample_approved_at', 'proposed_ready_at', 'approved_ready_at'],
            self::READY         => ['produced_at', 'balance_paid_at'],
            self::SHIPPED       => ['handed_over_at'],
            self::ARRIVED       => ['customs_released_at', 'compliance_complete_at'],
            self::RECEIVED      => ['checked_at', 'placed_at'],
        };
    }

    /**
     * A date the stage cannot be completed without; completing it anyway needs a reason.
     */
    public function gate(): ?string
    {
        return match ($this) {
            self::READY => 'sample_approved_at',
            default     => null,
        };
    }

    public function isDeliveryStage(): bool
    {
        return in_array($this, [self::SHIPPED, self::ARRIVED, self::RECEIVED]);
    }

    public function position(): int
    {
        return array_search($this, self::cases(), true);
    }
}
