<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 20 Sep 2023 13:50:48 Malaysia Time, Pantai Lembeng, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->
<script setup lang="ts">

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faEmptySet } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { inject } from "vue"
library.add(faEmptySet)


const props = defineProps<{
    item: {
        label?: string
        number?: number | string
    }
}>()

const locale = inject('locale', {})

</script>

<template>
    <div class="xl:whitespace-nowrap">
        <span class="leading-none">{{ item.label }}</span>

        <span>
            <template v-if="typeof item.number == 'number'">
                <template v-if="item.number > 0">
                    ({{ locale.number(item.number) }})
                </template>
                <template v-else>a
                    (<FontAwesomeIcon icon='fal fa-empty-set' class='' fixed-width aria-hidden='true' />)
                </template>
            </template>
            <template v-else>
                ({{ item.number }})d
            </template>
        </span>
    </div>
</template>
