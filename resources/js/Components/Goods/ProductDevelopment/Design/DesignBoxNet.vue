<!--
  - Author Louis Perez
  - Created on 17-10-2026-13h-20m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue"
import { ctrans } from "@/Composables/useTrans"
import DesignFaceRender from "@/Components/Goods/ProductDevelopment/Design/DesignFaceRender.vue"
import { buildBoxNet, hasSize, type DesignLayout, type FaceKey, type NetPanel } from "@/Components/Goods/ProductDevelopment/Design/boxNet"

export interface NetPin {
    id: number
    number: number
    x: number
    y: number
    resolved: boolean
    active?: boolean
}

const props = defineProps<{
    layout: DesignLayout
    images: Record<number, string | null>
    selectable?: boolean
    pinnable?: boolean
    pins?: NetPin[]
    selectedFace?: FaceKey | null
    maxHeight?: number
}>()

const emit = defineEmits<{
    select: [face: FaceKey]
    pin: [point: { x: number, y: number }]
    focusPin: [id: number]
}>()

const faceLabels = computed<Record<FaceKey, string>>(() => ({
    front: ctrans("Front"),
    back: ctrans("Back"),
    left: ctrans("Side"),
    right: ctrans("Side"),
    top: ctrans("Top"),
    bottom: ctrans("Bottom"),
}))

const container = ref<HTMLElement | null>(null)
const containerWidth = ref(0)
let observer: ResizeObserver | null = null

onMounted(() => {
    observer = new ResizeObserver(([entry]) => containerWidth.value = entry.contentRect.width)
    if (container.value) {
        observer.observe(container.value)
        containerWidth.value = container.value.clientWidth
    }
})
onBeforeUnmount(() => observer?.disconnect())

const net = computed(() => hasSize(props.layout) ? buildBoxNet(props.layout.width!, props.layout.length!, props.layout.height!) : null)

const scale = computed(() => {
    if (!net.value || !containerWidth.value) {
        return 0
    }
    const byWidth = containerWidth.value / net.value.width
    const byHeight = props.maxHeight ? props.maxHeight / net.value.height : byWidth

    return Math.min(byWidth, byHeight)
})

const panelStyle = (panel: NetPanel) => ({
    left: `${panel.x * scale.value}px`,
    top: `${panel.y * scale.value}px`,
    width: `${panel.w * scale.value}px`,
    height: `${panel.h * scale.value}px`,
})

function onNetClick(event: MouseEvent) {
    if (!props.pinnable || !net.value) {
        return
    }
    const bounds = (event.currentTarget as HTMLElement).getBoundingClientRect()
    emit("pin", {
        x: Math.min(Math.max((event.clientX - bounds.left) / bounds.width, 0), 1),
        y: Math.min(Math.max((event.clientY - bounds.top) / bounds.height, 0), 1),
    })
}

function onPanelClick(panel: NetPanel) {
    if (props.selectable && panel.face) {
        emit("select", panel.face)
    }
}
</script>

<template>
    <div ref="container" class="w-full">
        <div v-if="!net" class="flex h-48 items-center justify-center rounded-md border border-dashed border-gray-300 text-sm text-gray-400">
            {{ ctrans("Set the width, length and height to draw the box") }}
        </div>
        <div
            v-else-if="scale"
            class="relative mx-auto"
            :class="pinnable ? 'cursor-crosshair' : ''"
            :style="{ width: `${net.width * scale}px`, height: `${net.height * scale}px` }"
            @click="onNetClick"
        >
            <div
                v-for="panel in net.panels"
                :key="panel.key"
                class="absolute"
                :class="[
                    panel.face ? 'border border-gray-400 bg-white' : 'border border-dashed border-gray-300 bg-gray-50/60',
                    panel.face && selectable ? 'cursor-pointer transition-shadow hover:z-10 hover:ring-2 hover:ring-[--app-accent]' : '',
                    panel.face && panel.face === selectedFace ? 'z-10 ring-2 ring-[--app-accent]' : '',
                ]"
                :style="panelStyle(panel)"
                @click="onPanelClick(panel)"
            >
                <template v-if="panel.face">
                    <DesignFaceRender :face="layout.faces[panel.face]" :scale="scale" :images="images" />
                    <span
                        v-if="!layout.faces[panel.face].elements.length"
                        class="pointer-events-none absolute inset-0 flex items-center justify-center text-xs text-gray-400"
                    >{{ faceLabels[panel.face] }}</span>
                </template>
            </div>

            <button
                v-for="pin in pins ?? []"
                :key="pin.id"
                type="button"
                class="absolute z-20 flex h-6 w-6 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-2 border-white text-xs font-semibold shadow"
                :class="[pin.resolved ? 'bg-green-600 text-white' : 'bg-[--app-accent] text-[--app-accent-text]', pin.active ? 'scale-125 ring-2 ring-[--app-accent-muted]' : '']"
                :style="{ left: `${pin.x * 100}%`, top: `${pin.y * 100}%` }"
                :aria-label="ctrans('Comment :number', { number: String(pin.number) })"
                @click.stop="emit('focusPin', pin.id)"
            >
                {{ pin.number }}
            </button>
        </div>
    </div>
</template>
