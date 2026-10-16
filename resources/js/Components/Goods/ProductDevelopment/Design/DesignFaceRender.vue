<!--
  - Author Louis Perez
  - Created on 17-10-2026-13h-10m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed } from "vue"
import { ctrans } from "@/Composables/useTrans"
import DesignBarcode from "@/Components/Goods/ProductDevelopment/Design/DesignBarcode.vue"
import { PT_TO_MM, complianceText, coverBox, ensureImageRatio, imageRatios, type DesignData, type DesignElement, type DesignFace } from "@/Components/Goods/ProductDevelopment/Design/boxNet"

const props = defineProps<{
    face: DesignFace
    scale: number
    images: Record<number, string | null>
    size: { w: number, h: number }
    data?: DesignData | null
}>()

const textLabel = (key: string | null | undefined) => props.data?.sources.text.find((source) => source.key === key)?.label ?? key ?? ""
const textValue = (element: DesignElement) => props.data?.values ? (props.data.values.text[element.source ?? ""] ?? null) : null
const pictogram = (key: string | null | undefined) => props.data?.sources.pictograms.find((source) => source.key === key) ?? null
const barcodeValue = (key: string | null | undefined) => props.data?.values?.barcodes[key ?? "unit"] ?? null
const compliance = (element: DesignElement) => complianceText(props.data?.values ?? null, element.source)

const backgroundUrl = computed(() => props.face.background.image_file_id ? props.images[props.face.background.image_file_id] ?? null : null)

const backgroundImageStyle = computed(() => {
    const url = backgroundUrl.value
    if (!url) {
        return null
    }
    ensureImageRatio(url)
    const box = coverBox(props.size.w, props.size.h, imageRatios[url] ?? 0, props.face.background.zoom ?? 1, props.face.background.x ?? 0.5, props.face.background.y ?? 0.5)

    return {
        left: `${box.left * props.scale}px`,
        top: `${box.top * props.scale}px`,
        width: `${box.width * props.scale}px`,
        height: `${box.height * props.scale}px`,
    }
})

function boxStyle(element: DesignElement): Record<string, string> {
    return {
        left: `${element.x * props.scale}px`,
        top: `${element.y * props.scale}px`,
        width: `${element.w * props.scale}px`,
        height: `${element.h * props.scale}px`,
        transform: element.rotate ? `rotate(${element.rotate}deg)` : "",
    }
}

function textStyle(element: DesignElement): Record<string, string> {
    return {
        fontSize: `${(element.font_size ?? 10) * PT_TO_MM * props.scale}px`,
        fontWeight: element.font_weight === "bold" ? "700" : "400",
        fontStyle: element.italic ? "italic" : "normal",
        textAlign: element.align ?? "center",
        color: element.color ?? "#111827",
        fontFamily: element.font_family || "inherit",
        lineHeight: "1.2",
    }
}

const zoneLabel = (element: DesignElement) => element.label || (element.zone === "barcode" ? "EAN-13" : element.zone === "compliance" ? ctrans("Reserved: compliance text") : ctrans("Reserved"))
</script>

<template>
    <div class="absolute inset-0 overflow-hidden" :style="{ backgroundColor: face.background.color ?? '#ffffff' }">
        <img v-if="backgroundUrl && backgroundImageStyle" :src="backgroundUrl" alt="" class="pointer-events-none absolute max-w-none select-none" :style="backgroundImageStyle" draggable="false" />
        <div v-for="element in face.elements" :key="element.id" class="absolute" :style="boxStyle(element)" :data-element="element.id">
            <div v-if="element.type === 'text'" class="h-full w-full overflow-hidden whitespace-pre-wrap break-words" :style="textStyle(element)">{{ element.text }}</div>
            <img v-else-if="element.type === 'image' && element.image_file_id && images[element.image_file_id]" :src="images[element.image_file_id]!" alt="" class="pointer-events-none h-full w-full select-none object-contain" draggable="false" />
            <div v-else-if="element.type === 'image'" class="flex h-full w-full items-center justify-center border border-dashed border-gray-300 bg-gray-50 text-[10px] text-gray-400">{{ ctrans("Image") }}</div>
            <template v-else-if="element.type === 'data'">
                <div v-if="textValue(element)" class="h-full w-full overflow-hidden whitespace-pre-wrap break-words" :style="textStyle(element)">{{ textValue(element) }}</div>
                <div v-else class="flex h-full w-full items-center overflow-hidden border border-dashed border-sky-300 bg-sky-50/60 px-1 italic text-sky-700" :style="{ ...textStyle(element), color: undefined }">[{{ textLabel(element.source) }}]</div>
            </template>
            <img
                v-else-if="element.type === 'pictogram' && pictogram(element.source)"
                :src="pictogram(element.source)!.icon"
                :alt="pictogram(element.source)!.label"
                class="pointer-events-none h-full w-full select-none object-contain"
                :class="data?.values && !data.values.pictograms[element.source ?? ''] ? 'opacity-30' : ''"
                draggable="false"
            />
            <template v-else-if="element.type === 'barcode'">
                <DesignBarcode v-if="barcodeValue(element.source)" :value="barcodeValue(element.source)!" />
                <div v-else class="flex h-full w-full flex-col items-center justify-center gap-0.5 border border-dashed border-gray-400 bg-white/70 py-0.5 text-gray-600" :style="{ fontSize: `${Math.max(2.2 * scale, 7)}px` }">
                    <span class="w-4/5 flex-1 bg-[repeating-linear-gradient(90deg,#374151_0,#374151_1px,transparent_1px,transparent_3px)]" />
                    <span>{{ data?.sources.barcodes.find((source) => source.key === (element.source ?? 'unit'))?.label ?? "EAN-13" }}</span>
                </div>
            </template>
            <DesignBarcode v-else-if="element.type === 'zone' && element.zone === 'barcode' && barcodeValue(element.source ?? 'unit')" :value="barcodeValue(element.source ?? 'unit')!" />
            <div v-else-if="element.type === 'zone' && element.zone === 'compliance' && compliance(element)" class="h-full w-full overflow-hidden whitespace-pre-wrap break-words" :style="textStyle({ ...element, font_size: element.font_size ?? 6, align: element.align ?? 'left' })">{{ compliance(element) }}</div>
            <div
                v-else
                class="flex h-full w-full items-center justify-center overflow-hidden border border-dashed px-1 text-center leading-tight"
                :class="element.zone === 'compliance' ? 'border-orange-400 bg-orange-50/70 text-orange-700' : 'border-gray-400 bg-white/70 text-gray-600'"
                :style="{ fontSize: `${Math.max(2.2 * scale, 7)}px` }"
            >
                <span v-if="element.zone === 'barcode'" class="flex h-full w-full flex-col items-center justify-center gap-0.5 py-0.5">
                    <span class="w-4/5 flex-1 bg-[repeating-linear-gradient(90deg,#374151_0,#374151_1px,transparent_1px,transparent_3px)]" />
                    <span>{{ zoneLabel(element) }}</span>
                </span>
                <span v-else>{{ zoneLabel(element) }}</span>
            </div>
        </div>
    </div>
</template>
