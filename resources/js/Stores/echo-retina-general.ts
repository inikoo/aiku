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

export const useEchoRetinaGeneral = defineStore(
    'echo-retina-general', {
        state: () => ({
            prospectsDashboard: {},
        }),
        actions: {
            subscribe(groupId: string) {
                if (!groupId) {
                    console.log("%cWebsocket General Failed (Group id isn't provided)","background:#000;color:#0f0;font-family:Lucida console;font-size:20px;letter-spacing:-1px;display:block;padding:5px;box-shadow: 0 1px 0 rgba(255, 255, 255, 0.4) inset, 0 5px 3px -5px rgba(0, 0, 0, 0.5), 0 -13px 5px -10px rgba(255,255,255,0.4) inset")
                    
                    return 
                }
                let abcdef = window.Echo.private(`retina.${groupId}.general`).
                    listen('.notification', (e: NotificationData) => {
                        // console.log('From echo-org-general', e)
                        notify({
                            title: e.title || 'Retina General',
                            text: e.text || 'From echo-retina-general',
                            type: e.type || 'success',
                        })
                    })
                // console.log('Subscribed to General: :', abcdef)
            },
        },
    }
)
