<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from "vue"
import Popover from "primevue/popover"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faTimes } from "@fal"
import { ctrans } from "@/Composables/useTrans"

library.add(faCheck, faTimes)

defineProps<{
    value: number | null
    checks?: Record<string, boolean> | null
}>()

const checkLabels: Record<string, string> = {
    cbm: ctrans("Carton CBM"),
    carton_dimensions: ctrans("Carton dimensions"),
    carton_weight: ctrans("Carton gross weight"),
    carton_net_weight: ctrans("Carton net weight"),
    moq: ctrans("Minimum order (MOQ)"),
    lead_time: ctrans("Lead time"),
    lead_time_confirmed: ctrans("Lead time confirmed"),
}

const popover = ref()
</script>

<template>
    <span v-if="value !== null" class="inline-flex items-center justify-end gap-2" :aria-label="`${value}%`"
        @mouseenter="checks && popover?.show($event)" @mouseleave="popover?.hide()">
        <span class="h-1.5 w-16 overflow-hidden rounded-full bg-gray-100">
            <span class="block h-full rounded-full" :class="value >= 100 ? 'bg-green-500' : value >= 50 ? 'bg-amber-400' : 'bg-red-400'" :style="{ width: `${value}%` }" />
        </span>
        <span class="w-9 text-right tabular-nums">{{ value }}%</span>
        <Popover v-if="checks" ref="popover">
            <div class="text-sm">
                <div class="mb-1 font-medium">{{ ctrans("Product data complete") }}</div>
                <ul class="space-y-0.5">
                    <li v-for="(label, key) in checkLabels" :key="key" class="flex items-center gap-2">
                        <FontAwesomeIcon :icon="checks[key] ? 'fal fa-check' : 'fal fa-times'" fixed-width :class="checks[key] ? 'text-green-600' : 'text-red-500'" aria-hidden="true" />
                        <span :class="checks[key] ? 'text-gray-700' : 'text-gray-500'">{{ label }}</span>
                    </li>
                </ul>
            </div>
        </Popover>
    </span>
    <span v-else class="text-gray-400">-</span>
</template>
