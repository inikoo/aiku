<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 12 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Http\Resources\Catalogue;

use App\Enums\Discounts\OfferCampaign\OfferCampaignTypeEnum;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property OfferCampaignTypeEnum $type
 * @property int $active_offers
 * @property int $redeemed_offers
 * @property int $redemptions
 * @property int $redemption_customers
 * @property string $discounted_amount
 * @property string $revenue_net_amount
 * @property mixed $last_used_at
 */
class OfferCampaignsInsightsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'                 => $this->slug,
            'code'                 => $this->code,
            'name'                 => $this->name,
            'type'                 => $this->type->icons()[$this->type->value],
            'active_offers'        => (int) $this->active_offers,
            'redeemed_offers'      => (int) $this->redeemed_offers,
            'redemptions'          => (int) $this->redemptions,
            'redemption_customers' => (int) $this->redemption_customers,
            'discounted_amount'    => $this->discounted_amount,
            'revenue_net_amount'   => $this->revenue_net_amount,
            'last_used_at'         => $this->last_used_at,
        ];
    }
}
