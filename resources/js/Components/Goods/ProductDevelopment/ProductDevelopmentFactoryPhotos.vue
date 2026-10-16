<!--
  - Author Louis Perez
  - Created on 18-10-2026-01h-20m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCamera, faCheck, faSpinner } from "@fal"
library.add(faCamera, faCheck, faSpinner)

interface Angle {
    key: string
    label: string
    url: string | null
    preview_url: string | null
}

const props = defineProps<{
    value: { angles: Angle[], upload_route: routeType }
    editable: boolean
}>()

const emit = defineEmits<{
    error: [message: string | null]
}>()

const input = ref<HTMLInputElement | null>(null)
const pendingAngle = ref<string | null>(null)
const uploading = ref<string | null>(null)

const nextMissing = computed(() => props.value.angles.find((angle) => !angle.preview_url) ?? null)

function pick(angle: Angle) {
    if (!props.editable || uploading.value) {
        return
    }
    pendingAngle.value = angle.key
    input.value?.click()
}

function onPicked(event: Event) {
    const target = event.target as HTMLInputElement
    const photo = target.files?.[0]
    target.value = ""
    if (!photo || !pendingAngle.value) {
        return
    }
    const angle = pendingAngle.value
    emit("error", null)
    router.post(route(props.value.upload_route.name, props.value.upload_route.parameters), { angle, photo }, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onStart: () => uploading.value = angle,
        onFinish: () => uploading.value = null,
        onError: (errors) => emit("error", Object.values(errors)[0] ?? null),
    })
}
</script>

<template>
    <div class="space-y-3">
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
            <button
                v-for="angle in value.angles"
                :key="angle.key"
                type="button"
                class="relative flex aspect-[4/3] flex-col items-center justify-center overflow-hidden rounded-md text-xs transition-colors"
                :class="[
                    angle.preview_url ? 'border border-gray-200 bg-gray-100 text-green-800' : 'border border-dashed border-orange-300 bg-orange-50/40 text-orange-700',
                    editable ? 'cursor-pointer hover:border-[--app-accent]' : 'cursor-default',
                ]"
                :disabled="!editable"
                :aria-label="angle.preview_url ? ctrans('Replace the :angle photo', { angle: angle.label }) : ctrans('Take the :angle photo', { angle: angle.label })"
                @click="pick(angle)"
            >
                <img v-if="angle.preview_url" :src="angle.preview_url" :alt="angle.label" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
                <span class="relative z-10 flex items-center gap-1 rounded px-1.5 py-0.5" :class="angle.preview_url ? 'bg-white/85' : ''">
                    <FontAwesomeIcon v-if="uploading === angle.key" icon="fal fa-spinner" spin fixed-width aria-hidden="true" />
                    {{ angle.label }}
                    <FontAwesomeIcon v-if="angle.preview_url && uploading !== angle.key" icon="fal fa-check" fixed-width aria-hidden="true" />
                </span>
            </button>
        </div>

        <Button
            v-if="editable && nextMissing"
            type="primary"
            full
            icon="fal fa-camera"
            :label="ctrans('Take photo of :angle', { angle: nextMissing.label })"
            :loading="!!uploading"
            @click="pick(nextMissing)"
        />

        <input ref="input" type="file" accept="image/*" capture="environment" class="hidden" @change="onPicked" />
    </div>
</template>
