/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 00:33:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

import {defineStore} from 'pinia';

export const useLayoutStore = defineStore('layout', {
    state: () => (
        {
            navigation   : [],
            currentModels: [],
            tenant : {},
        }
    ),

});

