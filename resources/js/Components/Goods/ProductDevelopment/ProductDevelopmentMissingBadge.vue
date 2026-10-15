<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ctrans } from "@/Composables/useTrans"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheckCircle, faExclamationTriangle } from "@fal"
library.add(faCheckCircle, faExclamationTriangle)

defineProps<{
    count: number
}>()
</script>

<template>
    <span v-if="count > 0" class="inline-flex items-center gap-1 rounded bg-amber-50 px-1.5 py-0.5 text-xs font-medium tabular-nums text-amber-700">
        <FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
        {{ ctrans(":count missing", { count: String(count) }) }}
    </span>
    <span v-else class="inline-flex items-center gap-1 text-xs text-green-600" v-tooltip="ctrans('Nothing missing')">
        <FontAwesomeIcon icon="fal fa-check-circle" fixed-width aria-hidden="true" />
    </span>
</template>
