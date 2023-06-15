/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 00:11:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

export interface Employee {
    slug: string
    name: string,
    email: string,
    phone: string,
    created_at: string
    updated_at: string
    identity_document_number: number
    gender: string
    worker_number: string
    job_title: string
    emergency_contact: string;
    type: string
    state: string
}
