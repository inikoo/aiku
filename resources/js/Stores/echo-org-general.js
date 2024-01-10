/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 02:34:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import {defineStore} from 'pinia';
import {notify} from '@kyvg/vue3-notification';

export const useEchoGrpGeneral = defineStore(
    'echo-grp-general',
    {

        state  : () => ({
            prospectsDashboard: {},
        }),
        actions: {

            subscribe() {
                console.log('subscribe');
                window.Echo.private('grp.general').
                    listen('.notification', (e) => {
                        notify({
                                   title: e.data.title,
                                   text : e.data.text,
                                   type : 'info',
                               });

                    });

            },
        },

    });
