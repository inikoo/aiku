<!--
  - Author Louis Perez
  - Created on 17-10-2026-13h-40m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue"
import axios from "axios"
import Slider from "primevue/slider"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import Select from "primevue/select"
import DesignColorField from "@/Components/Goods/ProductDevelopment/Design/DesignColorField.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import DesignFaceRender from "@/Components/Goods/ProductDevelopment/Design/DesignFaceRender.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { coverBox, ensureImageRatio, imageRatios, newElementId, type DesignElement, type DesignFace, type DesignImage } from "@/Components/Goods/ProductDevelopment/Design/boxNet"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faFont, faImage, faBarcode, faShieldCheck, faSquare, faTrashAlt, faClone, faArrowUp, faArrowDown, faUndo, faRedo, faBold, faItalic, faAlignLeft, faAlignCenter, faAlignRight, faFillDrip, faTimes, faArrows } from "@fal"
library.add(faFont, faImage, faBarcode, faShieldCheck, faSquare, faTrashAlt, faClone, faArrowUp, faArrowDown, faUndo, faRedo, faBold, faItalic, faAlignLeft, faAlignCenter, faAlignRight, faFillDrip, faTimes, faArrows)

const props = defineProps<{
    size: { w: number, h: number }
    label: string
    images: Record<number, string | null>
    imageRoute: routeType
}>()

const face = defineModel<DesignFace>({ required: true })

const emit = defineEmits<{
    imageUploaded: [image: DesignImage]
}>()

const fonts = [
    { value: "", label: "Default" },
    { value: "Arial, Helvetica, sans-serif", label: "Arial" },
    { value: "Georgia, serif", label: "Georgia" },
    { value: "'Times New Roman', Times, serif", label: "Times New Roman" },
    { value: "'Trebuchet MS', sans-serif", label: "Trebuchet" },
    { value: "Verdana, sans-serif", label: "Verdana" },
    { value: "'Courier New', monospace", label: "Courier New" },
]

const alignOptions = [
    { value: "left", label: "Left", icon: "fal fa-align-left" },
    { value: "center", label: "Centre", icon: "fal fa-align-center" },
    { value: "right", label: "Right", icon: "fal fa-align-right" },
]

const stage = ref<HTMLElement | null>(null)
const stageSize = ref({ w: 0, h: 0 })
let observer: ResizeObserver | null = null

const scale = computed(() => {
    if (!stageSize.value.w || !props.size.w || !props.size.h) {
        return 1
    }

    return Math.min((stageSize.value.w - 48) / props.size.w, (stageSize.value.h - 48) / props.size.h)
})

const selectedId = ref<string | null>(null)
const selected = computed(() => face.value.elements.find((element) => element.id === selectedId.value) ?? null)
const uploading = ref(false)
const uploadError = ref<string | null>(null)
const imageInput = ref<HTMLInputElement | null>(null)
const backgroundInput = ref<HTMLInputElement | null>(null)
const textInput = ref<{ $el?: HTMLElement } | null>(null)

function focusText(selectAll = false) {
    const element = textInput.value?.$el as HTMLTextAreaElement | undefined
    element?.focus()
    if (selectAll) {
        element?.select()
    }
}

const undoStack = ref<string[]>([])
const redoStack = ref<string[]>([])

function remember() {
    undoStack.value.push(JSON.stringify(face.value))
    if (undoStack.value.length > 100) {
        undoStack.value.shift()
    }
    redoStack.value = []
}

function undo() {
    const previous = undoStack.value.pop()
    if (!previous) {
        return
    }
    redoStack.value.push(JSON.stringify(face.value))
    face.value = JSON.parse(previous)
}

function redo() {
    const next = redoStack.value.pop()
    if (!next) {
        return
    }
    undoStack.value.push(JSON.stringify(face.value))
    face.value = JSON.parse(next)
}

const round1 = (value: number) => Math.round(value * 10) / 10

function patchElement(id: string, changes: Partial<DesignElement>) {
    face.value = {
        ...face.value,
        elements: face.value.elements.map((element) => element.id === id ? { ...element, ...changes } : element),
    }
}

function addElement(element: Omit<DesignElement, "id" | "x" | "y"> & { x?: number, y?: number }) {
    remember()
    const id = newElementId()
    const added: DesignElement = {
        ...element,
        id,
        w: Math.min(element.w, props.size.w),
        h: Math.min(element.h, props.size.h),
        x: element.x ?? round1((props.size.w - Math.min(element.w, props.size.w)) / 2),
        y: element.y ?? round1((props.size.h - Math.min(element.h, props.size.h)) / 2),
    }
    face.value = { ...face.value, elements: [...face.value.elements, added] }
    selectedId.value = id
}

function addText() {
    addElement({ type: "text", w: round1(props.size.w * 0.8), h: round1(Math.max(props.size.h * 0.12, 8)), text: ctrans("Your text"), font_size: 12, font_weight: "normal", align: "center", color: "#111827", font_family: "" })
    nextTick(() => focusText(true))
}

function addZone(zone: "compliance" | "barcode" | "other") {
    const sizes = {
        compliance: { w: props.size.w * 0.85, h: props.size.h * 0.3 },
        barcode: { w: Math.min(37.3, props.size.w * 0.8), h: Math.min(25.9, props.size.h * 0.3) },
        other: { w: props.size.w * 0.5, h: props.size.h * 0.2 },
    }[zone]
    addElement({ type: "zone", zone, w: round1(sizes.w), h: round1(sizes.h), label: null })
}

async function upload(file: File): Promise<DesignImage | null> {
    uploading.value = true
    uploadError.value = null
    try {
        const form = new FormData()
        form.append("image", file)
        const { data } = await axios.post(route(props.imageRoute.name, props.imageRoute.parameters), form)
        emit("imageUploaded", data)
        return data
    } catch (error: any) {
        uploadError.value = error?.response?.data?.errors?.image?.[0] ?? error?.response?.data?.message ?? ctrans("The image could not be uploaded")
        return null
    } finally {
        uploading.value = false
    }
}

function naturalRatio(url: string): Promise<number> {
    return new Promise((resolve) => {
        const image = new Image()
        image.onload = () => resolve(image.naturalWidth ? image.naturalHeight / image.naturalWidth : 1)
        image.onerror = () => resolve(1)
        image.src = url
    })
}

async function onImagePicked(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    input.value = ""
    if (!file) {
        return
    }
    const image = await upload(file)
    if (!image) {
        return
    }
    const ratio = await naturalRatio(URL.createObjectURL(file))
    let w = props.size.w * 0.6
    let h = w * ratio
    if (h > props.size.h * 0.8) {
        h = props.size.h * 0.8
        w = h / ratio
    }
    addElement({ type: "image", image_file_id: image.id, w: round1(w), h: round1(h) })
}

async function onBackgroundPicked(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    input.value = ""
    if (!file) {
        return
    }
    const image = await upload(file)
    if (image) {
        remember()
        face.value = { ...face.value, background: { ...face.value.background, image_file_id: image.id } }
    }
}

function clearBackgroundImage() {
    remember()
    face.value = { ...face.value, background: { ...face.value.background, image_file_id: null } }
}

const backgroundColor = computed({
    get: () => face.value.background.color ?? "#ffffff",
    set: (value: string) => {
        face.value = { ...face.value, background: { ...face.value.background, color: value.toLowerCase() } }
    },
})

const textColor = computed({
    get: () => selected.value?.color ?? "#111827",
    set: (value: string) => {
        if (selected.value) {
            patchElement(selected.value.id, { color: value.toLowerCase() })
        }
    },
})

function setField<K extends keyof DesignElement>(key: K, value: DesignElement[K]) {
    if (selected.value) {
        patchElement(selected.value.id, { [key]: value } as Partial<DesignElement>)
    }
}

function removeSelected() {
    if (!selected.value) {
        return
    }
    remember()
    face.value = { ...face.value, elements: face.value.elements.filter((element) => element.id !== selectedId.value) }
    selectedId.value = null
}

function duplicateSelected() {
    if (!selected.value) {
        return
    }
    const { id: _id, ...copy } = selected.value
    addElement({ ...copy, x: round1(selected.value.x + 3), y: round1(selected.value.y + 3) })
}

function moveLayer(direction: 1 | -1) {
    if (!selected.value) {
        return
    }
    const elements = [...face.value.elements]
    const index = elements.findIndex((element) => element.id === selectedId.value)
    const target = index + direction
    if (target < 0 || target >= elements.length) {
        return
    }
    remember()
    ;[elements[index], elements[target]] = [elements[target], elements[index]]
    face.value = { ...face.value, elements }
}

type Gesture =
    | { kind: "move" | "resize" | "rotate", start: DesignElement, pointer: { x: number, y: number }, handle?: [number, number] }
    | { kind: "background", start: { x: number, y: number }, pointer: { x: number, y: number } }

const faceEl = ref<HTMLElement | null>(null)
const gesture = ref<Gesture | null>(null)
const guides = ref<{ v: number[], h: number[] }>({ v: [], h: [] })
const editingBackground = ref(false)

const SNAP_PX = 6
const ROTATE_STEP = 15
const handles: [number, number][] = [[-1, -1], [0, -1], [1, -1], [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0]]

const backgroundUrl = computed(() => face.value.background.image_file_id ? props.images[face.value.background.image_file_id] ?? null : null)

function backgroundBox() {
    const url = backgroundUrl.value
    if (!url) {
        return null
    }
    ensureImageRatio(url)
    return coverBox(props.size.w, props.size.h, imageRatios[url] ?? 0, face.value.background.zoom ?? 1, face.value.background.x ?? 0.5, face.value.background.y ?? 0.5)
}

const backgroundGhostStyle = computed(() => {
    const box = backgroundBox()
    return box ? { left: `${box.left * scale.value}px`, top: `${box.top * scale.value}px`, width: `${box.width * scale.value}px`, height: `${box.height * scale.value}px` } : null
})

function pointerMm(event: PointerEvent): { x: number, y: number } {
    const bounds = faceEl.value!.getBoundingClientRect()
    return { x: (event.clientX - bounds.left) / scale.value, y: (event.clientY - bounds.top) / scale.value }
}

function startGesture(event: PointerEvent, next: Gesture) {
    event.preventDefault()
    event.stopPropagation()
    remember()
    gesture.value = next
    window.addEventListener("pointermove", onPointerMove)
    window.addEventListener("pointerup", endGesture, { once: true })
}

function startMove(event: PointerEvent, element: DesignElement) {
    selectedId.value = element.id
    editingBackground.value = false
    startGesture(event, { kind: "move", start: { ...element }, pointer: pointerMm(event) })
}

function startResize(event: PointerEvent, handle: [number, number]) {
    if (selected.value) {
        startGesture(event, { kind: "resize", start: { ...selected.value }, pointer: pointerMm(event), handle })
    }
}

function startRotate(event: PointerEvent) {
    if (selected.value) {
        startGesture(event, { kind: "rotate", start: { ...selected.value }, pointer: pointerMm(event) })
    }
}

function startBackgroundPan(event: PointerEvent) {
    startGesture(event, { kind: "background", start: { x: face.value.background.x ?? 0.5, y: face.value.background.y ?? 0.5 }, pointer: pointerMm(event) })
}

/**
 * Moves the box so the nearest of its edges or centre lines meets a face edge, the face centre or
 * another unrotated item, when it is within a few pixels.
 */
function snap(start: number, length: number, targets: number[]): { value: number, line: number | null } {
    const threshold = SNAP_PX / scale.value
    let best: { delta: number, line: number } | null = null
    for (const edge of [start, start + length / 2, start + length]) {
        for (const target of targets) {
            const delta = target - edge
            if (Math.abs(delta) <= threshold && (!best || Math.abs(delta) < Math.abs(best.delta))) {
                best = { delta, line: target }
            }
        }
    }
    return best ? { value: start + best.delta, line: best.line } : { value: start, line: null }
}

function snapTargets(axis: "x" | "y", excludeId: string): number[] {
    const length = axis === "x" ? props.size.w : props.size.h
    const others = face.value.elements.filter((element) => element.id !== excludeId && !element.rotate)
        .flatMap((element) => axis === "x" ? [element.x, element.x + element.w / 2, element.x + element.w] : [element.y, element.y + element.h / 2, element.y + element.h])
    return [0, length / 2, length, ...others]
}

function onPointerMove(event: PointerEvent) {
    const current = gesture.value
    if (!current || !faceEl.value) {
        return
    }
    const pointer = pointerMm(event)
    const dx = pointer.x - current.pointer.x
    const dy = pointer.y - current.pointer.y

    if (current.kind === "background") {
        const box = backgroundBox()
        if (!box) {
            return
        }
        const overflowX = box.width - props.size.w
        const overflowY = box.height - props.size.h
        const clamp = (value: number) => Math.min(Math.max(value, 0), 1)
        face.value = {
            ...face.value,
            background: {
                ...face.value.background,
                x: overflowX > 0 ? Math.round(clamp(current.start.x - dx / overflowX) * 10000) / 10000 : 0.5,
                y: overflowY > 0 ? Math.round(clamp(current.start.y - dy / overflowY) * 10000) / 10000 : 0.5,
            },
        }
        return
    }

    const start = current.start
    if (current.kind === "move") {
        let x = start.x + dx
        let y = start.y + dy
        const lines = { v: [] as number[], h: [] as number[] }
        if (!start.rotate && !event.altKey) {
            const snappedX = snap(x, start.w, snapTargets("x", start.id))
            const snappedY = snap(y, start.h, snapTargets("y", start.id))
            x = snappedX.value
            y = snappedY.value
            if (snappedX.line !== null) {
                lines.v.push(snappedX.line)
            }
            if (snappedY.line !== null) {
                lines.h.push(snappedY.line)
            }
        }
        guides.value = lines
        patchElement(start.id, { x: round1(x), y: round1(y) })
        return
    }

    if (current.kind === "rotate") {
        const centreX = start.x + start.w / 2
        const centreY = start.y + start.h / 2
        let angle = Math.atan2(pointer.y - centreY, pointer.x - centreX) * 180 / Math.PI + 90
        if (!event.altKey) {
            const nearest = Math.round(angle / ROTATE_STEP) * ROTATE_STEP
            if (Math.abs(nearest - angle) <= 4 || event.shiftKey) {
                angle = nearest
            }
        }
        patchElement(start.id, { rotate: Math.round(((angle % 360) + 360) % 360) })
        return
    }

    const [handleX, handleY] = current.handle ?? [1, 1]
    const radians = ((start.rotate ?? 0) * Math.PI) / 180
    const cos = Math.cos(radians)
    const sin = Math.sin(radians)
    const localX = dx * cos + dy * sin
    const localY = -dx * sin + dy * cos

    let width = Math.max(start.w + handleX * localX, 1)
    let height = Math.max(start.h + handleY * localY, 1)
    if ((start.type === "image" || event.shiftKey) && handleX && handleY) {
        const ratio = start.h / start.w
        if (width * ratio > height) {
            height = width * ratio
        } else {
            width = height / ratio
        }
    }

    const shiftX = handleX * (width - start.w) / 2
    const shiftY = handleY * (height - start.h) / 2
    const centreX = start.x + start.w / 2 + shiftX * cos - shiftY * sin
    const centreY = start.y + start.h / 2 + shiftX * sin + shiftY * cos

    patchElement(start.id, { w: round1(width), h: round1(height), x: round1(centreX - width / 2), y: round1(centreY - height / 2) })
}

function endGesture() {
    window.removeEventListener("pointermove", onPointerMove)
    gesture.value = null
    guides.value = { v: [], h: [] }
}

function toggleBackgroundEditing() {
    editingBackground.value = !editingBackground.value && !!backgroundUrl.value
    if (editingBackground.value) {
        selectedId.value = null
    }
}

const backgroundZoom = computed({
    get: () => face.value.background.zoom ?? 1,
    set: (zoom: number) => {
        face.value = { ...face.value, background: { ...face.value.background, zoom: Math.round(zoom * 100) / 100 } }
    },
})

const boxStyle = (element: DesignElement) => ({
    left: `${element.x * scale.value}px`,
    top: `${element.y * scale.value}px`,
    width: `${element.w * scale.value}px`,
    height: `${element.h * scale.value}px`,
    transform: element.rotate ? `rotate(${element.rotate}deg)` : "",
})

const handleStyle = ([handleX, handleY]: [number, number]) => ({
    left: `${(handleX + 1) * 50}%`,
    top: `${(handleY + 1) * 50}%`,
    cursor: handleX === 0 ? "ns-resize" : handleY === 0 ? "ew-resize" : handleX === handleY ? "nwse-resize" : "nesw-resize",
})

const isTyping = (event: KeyboardEvent) => {
    const target = event.target as HTMLElement | null
    return !!target && (target.tagName === "INPUT" || target.tagName === "TEXTAREA" || target.isContentEditable)
}

function onKeydown(event: KeyboardEvent) {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "z") {
        if (isTyping(event)) {
            return
        }
        event.preventDefault()
        event.shiftKey ? redo() : undo()
        return
    }
    if (isTyping(event) || !selected.value) {
        return
    }
    if (event.key === "Delete" || event.key === "Backspace") {
        event.preventDefault()
        removeSelected()
        return
    }
    const step = event.shiftKey ? 5 : 1
    const moves: Record<string, [number, number]> = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] }
    if (moves[event.key]) {
        event.preventDefault()
        remember()
        patchElement(selected.value.id, { x: round1(selected.value.x + moves[event.key][0]), y: round1(selected.value.y + moves[event.key][1]) })
    }
    if (event.key === "Escape") {
        selectedId.value = null
        editingBackground.value = false
    }
}

onMounted(() => {
    observer = new ResizeObserver(([entry]) => stageSize.value = { w: entry.contentRect.width, h: entry.contentRect.height })
    if (stage.value) {
        observer.observe(stage.value)
    }
    window.addEventListener("keydown", onKeydown)
})

onBeforeUnmount(() => {
    observer?.disconnect()
    window.removeEventListener("keydown", onKeydown)
})

onBeforeUnmount(endGesture)
</script>

<template>
    <div class="flex h-full min-h-0 flex-col">
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 px-3 py-2">
            <Button type="tertiary" size="xs" icon="fal fa-font" :label="ctrans('Text')" @click="addText" />
            <Button type="tertiary" size="xs" icon="fal fa-image" :label="ctrans('Image')" :loading="uploading" @click="imageInput?.click()" />
            <Button type="tertiary" size="xs" icon="fal fa-shield-check" :label="ctrans('Compliance space')" @click="addZone('compliance')" />
            <Button type="tertiary" size="xs" icon="fal fa-barcode" :label="ctrans('Barcode')" @click="addZone('barcode')" />
            <Button type="tertiary" size="xs" icon="fal fa-square" :label="ctrans('Reserved area')" @click="addZone('other')" />
            <span class="mx-1 h-5 w-px bg-gray-200" />
            <Button type="tertiary" size="xs" icon="fal fa-undo" :tooltip="ctrans('Undo')" :disabled="!undoStack.length" class="disabled:cursor-not-allowed disabled:opacity-50" @click="undo" />
            <Button type="tertiary" size="xs" icon="fal fa-redo" :tooltip="ctrans('Redo')" :disabled="!redoStack.length" class="disabled:cursor-not-allowed disabled:opacity-50" @click="redo" />
            <span class="ml-auto text-xs tabular-nums text-gray-500">{{ label }} · {{ size.w }} × {{ size.h }} mm</span>
            <input ref="imageInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onImagePicked" />
            <input ref="backgroundInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onBackgroundPicked" />
        </div>
        <p v-if="uploadError" class="border-b border-red-100 bg-red-50 px-3 py-1.5 text-xs text-red-600">{{ uploadError }}</p>

        <div class="flex min-h-0 flex-1 flex-col md:flex-row">
            <div ref="stage" class="relative flex min-h-[18rem] flex-1 items-center justify-center overflow-hidden bg-gray-100" @pointerdown.self="selectedId = null">
                <div
                    ref="faceEl"
                    class="relative shadow-md"
                    :style="{ width: `${size.w * scale}px`, height: `${size.h * scale}px` }"
                    @pointerdown.self="selectedId = null"
                    @dblclick.self="toggleBackgroundEditing"
                >
                    <img
                        v-if="editingBackground && backgroundUrl && backgroundGhostStyle"
                        :src="backgroundUrl"
                        alt=""
                        class="pointer-events-none absolute max-w-none select-none opacity-30"
                        :style="backgroundGhostStyle"
                        draggable="false"
                    />
                    <DesignFaceRender :face="face" :scale="scale" :images="images" :size="size" />

                    <div
                        v-if="editingBackground"
                        class="absolute inset-0 z-20 cursor-grab ring-2 ring-[--app-accent] active:cursor-grabbing"
                        @pointerdown="startBackgroundPan"
                        @dblclick="editingBackground = false"
                    />

                    <template v-else>
                        <div
                            v-for="element in face.elements"
                            :key="element.id"
                            class="absolute cursor-move touch-none"
                            :class="element.id === selectedId ? '' : 'hover:outline hover:outline-1 hover:outline-[--app-accent]'"
                            :style="boxStyle(element)"
                            @pointerdown="startMove($event, element)"
                            @dblclick="element.type === 'text' && focusText()"
                        />

                        <div v-if="selected" class="pointer-events-none absolute z-10" :style="boxStyle(selected)">
                            <div class="absolute inset-0 outline outline-1 outline-[--app-accent]" />
                            <span class="absolute left-1/2 top-0 h-5 w-px -translate-y-full bg-[--app-accent]" />
                            <button
                                type="button"
                                class="pointer-events-auto absolute left-1/2 top-0 h-3.5 w-3.5 -translate-x-1/2 -translate-y-[calc(100%+1.25rem)] cursor-grab touch-none rounded-full border-2 border-[--app-accent] bg-white"
                                :aria-label="ctrans('Rotate')"
                                @pointerdown="startRotate"
                            />
                            <button
                                v-for="handle in handles"
                                :key="handle.join()"
                                type="button"
                                class="pointer-events-auto absolute h-3 w-3 -translate-x-1/2 -translate-y-1/2 touch-none rounded-sm border border-white bg-[--app-accent] shadow"
                                :style="handleStyle(handle)"
                                :aria-label="ctrans('Resize')"
                                @pointerdown="startResize($event, handle)"
                            />
                        </div>

                        <span v-for="line in guides.v" :key="`v${line}`" class="pointer-events-none absolute inset-y-0 z-30 w-px bg-pink-500" :style="{ left: `${line * scale}px` }" />
                        <span v-for="line in guides.h" :key="`h${line}`" class="pointer-events-none absolute inset-x-0 z-30 h-px bg-pink-500" :style="{ top: `${line * scale}px` }" />
                    </template>
                </div>
            </div>

            <aside class="w-full shrink-0 space-y-4 overflow-y-auto border-t border-gray-200 p-3 text-sm [scrollbar-width:thin] md:w-72 md:border-l md:border-t-0">
                <template v-if="selected">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-800">{{ selected.type === 'text' ? ctrans('Text') : selected.type === 'image' ? ctrans('Image') : ctrans('Reserved area') }}</span>
                        <div class="flex gap-1">
                            <Button type="tertiary" size="xs" icon="fal fa-arrow-up" :tooltip="ctrans('Bring forward')" @click="moveLayer(1)" />
                            <Button type="tertiary" size="xs" icon="fal fa-arrow-down" :tooltip="ctrans('Send backward')" @click="moveLayer(-1)" />
                            <Button type="tertiary" size="xs" icon="fal fa-clone" :tooltip="ctrans('Duplicate')" @click="duplicateSelected" />
                            <Button type="negative" size="xs" icon="fal fa-trash-alt" :tooltip="ctrans('Delete')" @click="removeSelected" />
                        </div>
                    </div>

                    <template v-if="selected.type === 'text'">
                        <Textarea ref="textInput" :modelValue="selected.text ?? ''" rows="3" autoResize class="w-full text-sm" @focus="remember" @update:modelValue="(value) => setField('text', value)" />
                        <div class="grid grid-cols-2 gap-2">
                            <label class="space-y-1">
                                <span class="text-xs text-gray-500">{{ ctrans("Size (pt)") }}</span>
                                <InputNumber :modelValue="selected.font_size ?? 12" :min="2" :max="400" :maxFractionDigits="1" size="small" inputClass="w-full" class="w-full" @update:modelValue="(value) => setField('font_size', value)" />
                            </label>
                            <label class="space-y-1">
                                <span class="text-xs text-gray-500">{{ ctrans("Colour") }}</span>
                                <DesignColorField v-model="textColor" @open="remember" />
                            </label>
                        </div>
                        <Select :modelValue="selected.font_family ?? ''" :options="fonts" optionLabel="label" optionValue="value" size="small" class="w-full" overlayClass="text-sm" @update:modelValue="(value) => setField('font_family', value)">
                            <template #option="{ option }"><span :style="{ fontFamily: option.value || 'inherit' }">{{ option.label }}</span></template>
                        </Select>
                        <div class="flex items-center gap-2">
                            <Button :type="selected.font_weight === 'bold' ? 'primary' : 'tertiary'" size="xs" icon="fal fa-bold" :tooltip="ctrans('Bold')" @click="setField('font_weight', selected.font_weight === 'bold' ? 'normal' : 'bold')" />
                            <Button :type="selected.italic ? 'primary' : 'tertiary'" size="xs" icon="fal fa-italic" :tooltip="ctrans('Italic')" @click="setField('italic', !selected.italic)" />
                            <SegmentedToggle :modelValue="selected.align ?? 'center'" :options="alignOptions" iconOnly :ariaLabel="ctrans('Alignment')" @update:modelValue="(value) => setField('align', value as DesignElement['align'])" />
                        </div>
                    </template>

                    <label v-if="selected.type === 'zone'" class="block space-y-1">
                        <span class="text-xs text-gray-500">{{ ctrans("Label") }}</span>
                        <Textarea :modelValue="selected.label ?? ''" rows="2" autoResize class="w-full text-sm" :placeholder="selected.zone === 'barcode' ? 'EAN-13' : ctrans('What goes here')" @focus="remember" @update:modelValue="(value) => setField('label', value || null)" />
                    </label>

                    <div class="grid grid-cols-2 gap-2">
                        <label v-for="field in (['x', 'y', 'w', 'h'] as const)" :key="field" class="space-y-1">
                            <span class="text-xs uppercase text-gray-500">{{ field }} (mm)</span>
                            <InputNumber :modelValue="selected[field]" :maxFractionDigits="1" :min="field === 'w' || field === 'h' ? 1 : undefined" size="small" inputClass="w-full" class="w-full" @update:modelValue="(value) => value !== null && setField(field, value)" />
                        </label>
                        <label class="col-span-2 space-y-1">
                            <span class="text-xs text-gray-500">{{ ctrans("Rotation (°)") }}</span>
                            <InputNumber :modelValue="selected.rotate ?? 0" :min="-360" :max="360" size="small" inputClass="w-full" class="w-full" @update:modelValue="(value) => setField('rotate', value ?? 0)" />
                        </label>
                    </div>
                </template>

                <template v-else>
                    <div class="font-medium text-gray-800">{{ ctrans("Background") }}</div>
                    <div class="flex items-center gap-2">
                        <DesignColorField v-model="backgroundColor" @open="remember" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button v-if="face.background.image_file_id" :type="editingBackground ? 'primary' : 'tertiary'" size="xs" icon="fal fa-arrows" :label="editingBackground ? ctrans('Done moving') : ctrans('Move background')" @click="toggleBackgroundEditing" />
                        <Button type="tertiary" size="xs" icon="fal fa-fill-drip" :label="face.background.image_file_id ? ctrans('Change image') : ctrans('Background image')" :loading="uploading" @click="backgroundInput?.click()" />
                        <Button v-if="face.background.image_file_id" type="negative" size="xs" icon="fal fa-times" :label="ctrans('Remove')" @click="clearBackgroundImage" />
                    </div>
                    <label v-if="face.background.image_file_id" class="block space-y-2">
                        <span class="flex justify-between text-xs text-gray-500"><span>{{ ctrans("Zoom") }}</span><span class="tabular-nums">{{ Math.round(backgroundZoom * 100) }}%</span></span>
                        <Slider v-model="backgroundZoom" :min="1" :max="3" :step="0.05" @slideend="remember" />
                    </label>
                    <p v-if="editingBackground" class="rounded-md bg-[--app-accent-soft] px-2 py-1.5 text-xs text-[--app-accent-deep]">{{ ctrans("Drag the image to choose which part shows. It always covers the whole side.") }}</p>
                    <p class="text-xs leading-relaxed text-gray-500">
                        {{ ctrans("Drag an item to move it, its corners to resize (Shift keeps the shape) and the round handle to rotate. Hold Alt to stop snapping. Arrow keys nudge by 1 mm (Shift: 5 mm), Delete removes it, Ctrl+Z undoes. Double-click the side to move its background.") }}
                    </p>
                </template>
            </aside>
        </div>
    </div>
</template>
