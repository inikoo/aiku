/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 08 Dec 2023 01:34:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { useMilisecondToTime } from "@/Composables/useFormatTime"
import { differenceInMilliseconds } from 'date-fns/esm'
import { defineStore } from "pinia";

interface ProgressBar {
    [key: string]: {
        [key: string]: {
            action_id: number
            action_type: string
            data: {
                number_fails: number
                number_success: number
            },
            done: number
            total: number
        }
    }
}

export const useEchoGrpPersonal = defineStore("echo-grp-personal", {
    state: () => ({
        progressBars: {} as ProgressBar,
        isShowProgress: false,
        recentlyUploaded: []
    }),
    actions: {
        subscribe(userID: number) {
            window.Echo.private("grp.personal." + userID).listen(
                ".action-progress",
                (eventData: {}) => {
                    console.log('From echo-grp-personal')
                    console.log(eventData)
                }
            );
        },
    },
});
