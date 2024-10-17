<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Apr 2024 14:30:26 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $number_current_clients
 * @property mixed $slug
 * @property mixed $reference
 * @property mixed $name
 * @property mixed $contact_name
 * @property mixed $company_name
 * @property mixed $location
 * @property mixed $email
 * @property mixed $created_at
 * @property mixed $last_invoiced_at
 * @property mixed $invoiced_net_amount
 * @property mixed $invoiced_org_net_amount
 * @property mixed $invoiced_grp_net_amount
 * @property mixed $number_invoices_type_invoice
 */
class CustomersResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [
            'slug'                         => $this->slug,
            'reference'                    => $this->reference,
            'name'                         => $this->name,
            'contact_name'                 => $this->contact_name,
            'company_name'                 => $this->company_name,
            'location'                     => $this->location,
            'created_at'                   => $this->created_at,
            'number_current_clients'       => $this->number_current_clients,
            'number_current_portfolios'    => $this->number_current_portfolios,
            'platforms'                    => $this->platform_name ?? 'none',
            'last_invoiced_at'             => $this->last_invoiced_at,
            'number_invoices_type_invoice' => $this->number_invoices_type_invoice,
            'invoiced_net_amount'          => $this->invoiced_net_amount,
            'invoiced_org_net_amount'      => $this->invoiced_org_net_amount,
            'invoiced_grp_net_amount'      => $this->invoiced_grp_net_amount,
        ];
    }
}
