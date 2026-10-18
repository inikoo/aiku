/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 00:11:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

export interface PurchaseOrder {
    id: number,
    slug: string,
    provider_id: string,
    provider_type: string,
    number: string
    state: string
    status: string
    created_at: string
    updated_at: string
    parent_type: string
    parent_name: string
    parent_slug: string
    agent_name: string | null
    agent_slug: string | null
    org_agent_slug: string | null
    agent_order_reference?: string | null
    supplier_slug: string | null
    supplier_purchase_orders?: number | null
    organisation_slug?: string | null
    other_clients_orders?: {
        slug: string
        organisation_code: string
        agent_order_reference: string
        state: string
        state_label: string
        cost_total: number
        currency_code: string
    }[] | null
}
