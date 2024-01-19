/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 02:34:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { defineStore } from 'pinia'
import { notify } from '@kyvg/vue3-notification'

export const useEchoGrpGeneral = defineStore(
    'echo-grp-general', {
        state: () => ({
            prospectsDashboard: {},
        }),
        actions: {
            subscribe() {
                // console.log('subscribe general')
                let abcdef = window.Echo.private('grp.general').
                    listen('.notification', (e: {}) => {
                        console.log('From echo-org-general', e)
                        notify({
                            title: 'General',
                            text: 'From echo-org-general',
                            type: 'success',
                        })
                    })
                console.log('Subscribed to General: :', abcdef.subscription.subscribed)
            },
        },
    }
)
