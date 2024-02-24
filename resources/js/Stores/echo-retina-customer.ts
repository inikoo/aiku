/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Feb 2024 10:38:34 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

import { defineStore } from 'pinia'
import { notify } from '@kyvg/vue3-notification'

interface NotificationData {
    title: string
    text: string
    type: string
}

export const useEchoRetinaCustomer = defineStore(
    'echo-retina-customer', {
        state: () => ({

        }),
        actions: {
            subscribe(customerId: string) {

                window.Echo.private(`retina.${customerId}.customer`).
                    listen('.notification', (e: NotificationData) => {

                        notify({
                            title: e.title || 'Retina Customer',
                            text: e.text || 'From echo-retina-customer',
                            type: e.type || 'success',
                        })
                    })

            },
        },
    }
)
