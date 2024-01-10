/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 01:34:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { useMilisecondToTime } from "@/Composables/useFormatTime"
import { differenceInMilliseconds } from 'date-fns/esm'
import { defineStore } from "pinia";

// interface ProgressBar {
//     [key: string]: {
//         [key: string]: {
//             action_id: number
//             action_type: string
//             data: {
//                 number_fails: number
//                 number_success: number
//             },
//             done: number
//             total: number
//         }
//     }
// }

export const useEchoOrgPersonal = defineStore("echo-org-personal", {
    state: () => ({
        progressBars: {
            // Upload: {
            //     1: {
            //         action_id: 0,
            //         action_type: '',
            //         data: {
            //             number_fails: 0,
            //             number_success: 0
            //         },
            //         done: 0,
            //         total: 0
            //     }
            // }
        },
        isShowProgress: false,
        recentlyUploaded: []
    }),
    actions: {
        subscribe(userID) {
            window.Echo.private("grp.personal." + userID).listen(
                ".action-progress",
                (eventData) => {
                    console.log('From echo-org-personal')
                    console.log(eventData)
                }
            );
        },
    },
});
