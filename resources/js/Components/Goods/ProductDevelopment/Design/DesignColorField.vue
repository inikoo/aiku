<!--
  - Author Louis Perez
  - Created on 18-10-2026-06h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"
import InputNumber from "primevue/inputnumber"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import DesignHexInput from "@/Components/Goods/ProductDevelopment/Design/DesignHexInput.vue"
import { ctrans } from "@/Composables/useTrans"
import { formatPaint, paintCss, parsePaint, type ParsedPaint } from "@/Components/Goods/ProductDevelopment/Design/boxNet"

const props = defineProps<{
    allowGradient?: boolean
    allowNone?: boolean
    fallback?: string
}>()

const model = defineModel<string>({ required: true })

const emit = defineEmits<{
    open: []
}>()

const paint = computed(() => parsePaint(model.value, props.fallback ?? "#111827"))

const modes = computed(() => [
    { value: "solid", label: ctrans("Solid") },
    ...(props.allowGradient ? [{ value: "linear", label: ctrans("Linear") }, { value: "radial", label: ctrans("Radial") }] : []),
    ...(props.allowNone ? [{ value: "none", label: ctrans("None") }] : []),
])

function update(changes: Partial<ParsedPaint>) {
    const next = { ...paint.value, ...changes }
    if (changes.mode && changes.mode !== "solid" && changes.mode !== "none" && paint.value.from === paint.value.to) {
        next.to = paint.value.from.toLowerCase() === "#ffffff" ? "#111827" : "#ffffff"
    }
    model.value = formatPaint(next)
}
</script>

<template>
    <div class="space-y-2">
        <SegmentedToggle v-if="modes.length > 1" :modelValue="paint.mode" :options="modes as any" :ariaLabel="ctrans('Colour type')" @update:modelValue="(mode) => { emit('open'); update({ mode: mode as ParsedPaint['mode'] }) }" />

        <DesignHexInput v-if="paint.mode === 'solid'" :modelValue="paint.from" @update:modelValue="(from) => update({ from, to: from })" @open="emit('open')" />

        <template v-else-if="paint.mode !== 'none'">
            <div class="h-4 rounded border border-gray-200" :style="{ background: paintCss(model) }" aria-hidden="true" />
            <div class="grid grid-cols-[2.5rem_minmax(0,1fr)] items-center gap-x-2 gap-y-1.5 text-xs text-gray-500">
                <span>{{ ctrans("From") }}</span>
                <DesignHexInput :modelValue="paint.from" @update:modelValue="(from) => update({ from })" @open="emit('open')" />
                <span>{{ ctrans("To") }}</span>
                <DesignHexInput :modelValue="paint.to" @update:modelValue="(to) => update({ to })" @open="emit('open')" />
                <template v-if="paint.mode === 'linear'">
                    <span>{{ ctrans("Angle") }}</span>
                    <InputNumber :modelValue="paint.angle" :min="0" :max="359" suffix="°" size="small" inputClass="w-full" class="w-full" @focus="emit('open')" @update:modelValue="(angle) => update({ angle: angle ?? 0 })" />
                </template>
            </div>
        </template>
    </div>
</template>
