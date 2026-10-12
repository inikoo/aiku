<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 11 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Checkbox from "primevue/checkbox"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faHourglassHalf } from "@fal"
import { faCheckCircle } from "@fas"

defineProps<{
    part: { is_ours: boolean, ready: boolean, ready_by: string | null, ready_at: string | null }
    canSend: boolean
}>()

defineEmits<{ (event: "setReady", ready: boolean): void }>()
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <span
            v-if="part.ready"
            v-tooltip="[ctrans('OK to send'), part.ready_by, part.ready_at ? useFormatTime(part.ready_at, { formatTime: 'd MMM HH:mm' }) : null].filter(Boolean).join(' · ')"
            class="text-green-600"
        >
            <FontAwesomeIcon :icon="faCheckCircle" fixed-width aria-hidden="true" />
            <span class="sr-only">{{ ctrans("OK to send") }}</span>
        </span>
        <span v-else-if="!part.is_ours" v-tooltip="ctrans('Has not said it is OK to send yet')" class="text-amber-500">
            <FontAwesomeIcon :icon="faHourglassHalf" fixed-width aria-hidden="true" />
            <span class="sr-only">{{ ctrans("waiting") }}</span>
        </span>
        <Checkbox
            v-if="part.is_ours && canSend"
            :modelValue="part.ready"
            binary
            :aria-label="ctrans('Our part is OK to send')"
            v-tooltip="ctrans('Tick when your part is OK to send')"
            @update:modelValue="(value: boolean) => $emit('setReady', value)"
        />
    </div>
</template>
