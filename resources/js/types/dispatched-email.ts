/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 00:11:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

export interface DispatchedEmail {
    id:string,
    mailshot_id: string,
    outbox_id: string,
    recipient_type: string
    recipient_id: string
    state: string
    created_at: string
    updated_at: string
    number_reads: string
    number_clicks: string


}
