/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 02:34:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { defineStore } from 'pinia'
import { notify } from '@kyvg/vue3-notification'

interface NotificationData {
    title: string
    text: string
    type: string
}

export const useEchoRetinaWebsite = defineStore(
    'echo-retina-website', {
        state: () => ({
            prospectsDashboard: {},
        }),
        actions: {
            subscribe(websiteId: string) {

                window.Echo.private(`retina.${websiteId}.website`).
                    listen('.notification', (e: NotificationData) => {
                        // console.log('From echo-org-general', e)
                        notify({
                            title: e.title || 'Retina General',
                            text: e.text || 'From echo-retina-website',
                            type: e.type || 'success',
                        })
                    })

            },
        },
    }
)
