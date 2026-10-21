<!--
  - Author Louis Perez
  - Created on 18-10-2026-05h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { onMounted, ref, watch } from "vue"
import JsBarcode from "jsbarcode"

const props = defineProps<{
    value: string
    showValue?: boolean
}>()

const svg = ref<SVGSVGElement | null>(null)
const valid = ref(true)

function render() {
    if (!svg.value) {
        return
    }
    valid.value = true
    try {
        JsBarcode(svg.value, props.value, {
            format: /^\d{13}$/.test(props.value) ? "EAN13" : "CODE128",
            displayValue: props.showValue ?? true,
            margin: 0,
            background: "transparent",
            valid: (isValid: boolean) => valid.value = isValid,
        })
        const width = svg.value.getAttribute("width")?.replace("px", "")
        const height = svg.value.getAttribute("height")?.replace("px", "")
        if (width && height) {
            svg.value.setAttribute("viewBox", `0 0 ${width} ${height}`)
        }
        svg.value.setAttribute("preserveAspectRatio", "none")
        svg.value.removeAttribute("width")
        svg.value.removeAttribute("height")
    } catch {
        valid.value = false
    }
}

onMounted(render)
watch(() => [props.value, props.showValue], render)
</script>

<template>
    <div class="h-full w-full">
        <svg v-show="valid" ref="svg" class="h-full w-full" />
        <div v-if="!valid" class="flex h-full w-full items-center justify-center text-[8px] text-red-600">{{ value }}</div>
    </div>
</template>
