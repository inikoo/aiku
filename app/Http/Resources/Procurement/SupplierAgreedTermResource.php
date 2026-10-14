<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 03:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use App\Enums\Procurement\SupplierAgreedTerm\SupplierAgreedTermStateEnum;
use App\Models\Procurement\SupplierAgreedTerm;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupplierAgreedTerm
 *
 * @property string|null $level
 * @property string|null $product_code
 * @property bool|null $in_force
 */
class SupplierAgreedTermResource extends JsonResource
{
    public function toArray($request): array
    {
        $level = $this->level ?? match ($this->subject_type) {
            'SupplierProduct' => 'supplier_product',
            'Agent'           => 'agent',
            default           => 'supplier',
        };

        return [
            'id'                => $this->id,
            'term_type'         => $this->term_type->value,
            'term'              => $this->term_type->label(),
            'value_type'        => $this->value_type->value,
            'amount'            => $this->amount,
            'currency_id'       => $this->currency_id,
            'value_text'        => $this->value_text,
            'tolerance'         => $this->tolerance,
            'value'             => $this->valueLabel(),
            'level'             => $level,
            'level_label'       => match ($level) {
                'supplier_product' => __('This product'),
                'agent'            => __('Agent'),
                default            => __('Supplier'),
            },
            'product_code'      => $this->product_code ?? null,
            'organisation_id'   => $this->organisation_id,
            'organisation_code' => $this->organisation?->code,
            'agreed_on'         => $this->agreed_on->toDateString(),
            'agreed_by'         => $this->agreedBy?->contact_name,
            'agreed_by_user_id' => $this->agreed_by_user_id,
            'agreed_with'       => $this->agreed_with,
            'has_evidence'      => $this->evidence_message_id !== null || $this->attachments->isNotEmpty(),
            'evidence'          => $this->attachments->map(fn ($media) => [
                'name'  => $media->pivot->caption ?: $media->name,
                'route' => ['name' => 'grp.media.download', 'parameters' => ['media' => $media->ulid]],
            ])->all(),
            'valid_to'          => $this->valid_to?->toDateString(),
            'state'             => $this->state->value,
            'state_label'       => $this->state->label(),
            'in_force'          => (bool) ($this->in_force ?? $this->state === SupplierAgreedTermStateEnum::ACTIVE),
            'visibility'        => $this->visibility->value,
            'visibility_label'  => $this->visibility->label(),
            'show_on_po'        => $this->show_on_po,
        ];
    }
}
