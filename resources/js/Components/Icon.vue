<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 01 Feb 2024 09:15:14 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {capitalize} from '@/Composables/capitalize.js';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {
    faSeedling,
    faBroadcastTower,
    faImage,
    faSparkles,
    faRocket,
    faDoNotEnter,
    faClipboardCheck,
    faCheck,
    faSpellCheck,
    faCheckDouble, faTruck
} from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core"
library.add(faSeedling, faBroadcastTower, faImage, faSparkles, faRocket, faDoNotEnter, faClipboardCheck, faCheck, faSpellCheck, faCheckDouble, faTruck)

const props = defineProps<{
    data: {
        tooltip?: string,
        icon: object,
        class?: string
    },
    title?: string
}>()
</script>

<template>

    <FontAwesomeIcon
        v-tooltip="title ? title : capitalize(data.tooltip ?? '')"
        aria-hidden="true"
        :icon="data.icon"
        :class="data.class"
        fixed-width
    />

</template>
