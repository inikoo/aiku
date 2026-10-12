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
import { faCheck } from "@fal"

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
            v-tooltip="part.ready_at ? `${part.ready_by ?? ''} ${useFormatTime(part.ready_at, { formatTime: 'd MMM HH:mm' })}` : undefined"
            class="whitespace-nowrap text-xs font-medium text-green-700"
        >
            <FontAwesomeIcon :icon="faCheck" fixed-width aria-hidden="true" />
            {{ ctrans("ready") }}
        </span>
        <span v-else-if="!part.is_ours" class="text-xs text-amber-600">{{ ctrans("waiting") }}</span>
        <Checkbox
            v-if="part.is_ours && canSend"
            :modelValue="part.ready"
            binary
            :aria-label="ctrans('Our part is OK to send')"
            @update:modelValue="(value: boolean) => $emit('setReady', value)"
        />
    </div>
</template>
