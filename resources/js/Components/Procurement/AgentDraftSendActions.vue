<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 11 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"

const props = defineProps<{
    card: { has_ours: boolean, parts: { organisation_code: string, is_ours: boolean, ready: boolean }[] }
    isSending: boolean
}>()

defineEmits<{ (event: "send"): void, (event: "override"): void }>()

const waiting = computed(() => props.card.parts.filter((part) => !part.ready && !part.is_ours))
</script>

<template>
    <div class="flex items-center justify-end gap-2">
        <span v-if="!card.has_ours" class="mr-auto text-xs text-gray-400">{{ ctrans("Your organisation has nothing on this supplier") }}</span>
        <Button v-else-if="!waiting.length" :label="ctrans('Submit to agent')" type="tertiary" icon="fal fa-paper-plane" size="s" :loading="isSending" @click="$emit('send')" />
        <template v-else>
            <span class="mr-auto text-xs text-amber-600">
                {{ ctrans("Waiting for :organisations", { organisations: waiting.map((part) => part.organisation_code).join(", ") }) }}
            </span>
            <Button :label="ctrans('Submit without waiting')" type="negative" icon="fal fa-exclamation-triangle" size="s" @click="$emit('override')" />
        </template>
    </div>
</template>
