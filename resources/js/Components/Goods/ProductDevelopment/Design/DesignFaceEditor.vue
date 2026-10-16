<!--
  - Author Louis Perez
  - Created on 17-10-2026-13h-40m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue"
import axios from "axios"
import Moveable from "vue3-moveable"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import Select from "primevue/select"
import ColorPicker from "primevue/colorpicker"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import DesignFaceRender from "@/Components/Goods/ProductDevelopment/Design/DesignFaceRender.vue"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import { newElementId, type DesignElement, type DesignFace, type DesignImage } from "@/Components/Goods/ProductDevelopment/Design/boxNet"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faFont, faImage, faBarcode, faShieldCheck, faSquare, faTrashAlt, faClone, faArrowUp, faArrowDown, faUndo, faRedo, faBold, faItalic, faAlignLeft, faAlignCenter, faAlignRight, faFillDrip, faTimes } from "@fal"
library.add(faFont, faImage, faBarcode, faShieldCheck, faSquare, faTrashAlt, faClone, faArrowUp, faArrowDown, faUndo, faRedo, faBold, faItalic, faAlignLeft, faAlignCenter, faAlignRight, faFillDrip, faTimes)

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
const hitBoxes = ref<Record<string, HTMLElement | null>>({})
const moveable = ref<InstanceType<typeof Moveable> | null>(null)
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
    get: () => (face.value.background.color ?? "#ffffff").replace("#", ""),
    set: (value: string) => {
        face.value = { ...face.value, background: { ...face.value.background, color: `#${value}` } }
    },
})

const textColor = computed({
    get: () => (selected.value?.color ?? "#111827").replace("#", ""),
    set: (value: string) => selected.value && patchElement(selected.value.id, { color: `#${value}` }),
})

function setField<K extends keyof DesignElement>(key: K, value: DesignElement[K]) {
    if (selected.value) {
        patchElement(selected.value.id, { [key]: value } as Partial<DesignElement>)
        nextTick(() => moveable.value?.updateRect())
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

function onDragStart() {
    remember()
}

function onDrag(event: { left: number, top: number }) {
    if (selected.value) {
        patchElement(selected.value.id, { x: round1(event.left / scale.value), y: round1(event.top / scale.value) })
    }
}

function onResize(event: { width: number, height: number, drag: { left: number, top: number } }) {
    if (selected.value) {
        patchElement(selected.value.id, {
            w: round1(Math.max(event.width / scale.value, 1)),
            h: round1(Math.max(event.height / scale.value, 1)),
            x: round1(event.drag.left / scale.value),
            y: round1(event.drag.top / scale.value),
        })
    }
}

function onRotate(event: { rotation: number }) {
    if (selected.value) {
        patchElement(selected.value.id, { rotate: Math.round(((event.rotation % 360) + 360) % 360) })
    }
}

const hitStyle = (element: DesignElement) => ({
    left: `${element.x * scale.value}px`,
    top: `${element.y * scale.value}px`,
    width: `${element.w * scale.value}px`,
    height: `${element.h * scale.value}px`,
    transform: element.rotate ? `rotate(${element.rotate}deg)` : "",
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
        nextTick(() => moveable.value?.updateRect())
    }
    if (event.key === "Escape") {
        selectedId.value = null
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

watch(scale, () => nextTick(() => moveable.value?.updateRect()))

const otherHitBoxes = computed(() => face.value.elements.filter((element) => element.id !== selectedId.value).map((element) => hitBoxes.value[element.id]).filter(Boolean) as HTMLElement[])
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
                    class="relative shadow-md"
                    :style="{ width: `${size.w * scale}px`, height: `${size.h * scale}px` }"
                    @pointerdown.self="selectedId = null"
                >
                    <DesignFaceRender :face="face" :scale="scale" :images="images" />
                    <div
                        v-for="element in face.elements"
                        :key="element.id"
                        :ref="(el) => hitBoxes[element.id] = el as HTMLElement | null"
                        class="absolute cursor-move"
                        :class="element.id === selectedId ? '' : 'hover:outline hover:outline-1 hover:outline-[--app-accent]'"
                        :style="hitStyle(element)"
                        @pointerdown="selectedId = element.id"
                        @dblclick="element.type === 'text' && focusText()"
                    />
                    <Moveable
                        v-if="selected && hitBoxes[selected.id]"
                        ref="moveable"
                        :key="selected.id"
                        :target="hitBoxes[selected.id]"
                        :draggable="true"
                        :resizable="true"
                        :rotatable="true"
                        :keepRatio="selected.type === 'image'"
                        :snappable="true"
                        :snapThreshold="6"
                        :verticalGuidelines="[0, (size.w * scale) / 2, size.w * scale]"
                        :horizontalGuidelines="[0, (size.h * scale) / 2, size.h * scale]"
                        :elementGuidelines="otherHitBoxes"
                        :snapDirections="{ top: true, left: true, bottom: true, right: true, center: true, middle: true }"
                        :elementSnapDirections="{ top: true, left: true, bottom: true, right: true, center: true, middle: true }"
                        :origin="false"
                        @dragStart="onDragStart"
                        @drag="onDrag"
                        @resizeStart="onDragStart"
                        @resize="onResize"
                        @rotateStart="onDragStart"
                        @rotate="onRotate"
                    />
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
                                <span class="flex h-[2.15rem] items-center gap-2 rounded-md border border-gray-300 px-2">
                                    <ColorPicker v-model="textColor" format="hex" />
                                    <span class="text-xs uppercase text-gray-600">#{{ textColor }}</span>
                                </span>
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
                        <span class="flex h-[2.15rem] items-center gap-2 rounded-md border border-gray-300 px-2">
                            <ColorPicker v-model="backgroundColor" format="hex" @show="remember" />
                            <span class="text-xs uppercase text-gray-600">#{{ backgroundColor }}</span>
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button type="tertiary" size="xs" icon="fal fa-fill-drip" :label="face.background.image_file_id ? ctrans('Change image') : ctrans('Background image')" :loading="uploading" @click="backgroundInput?.click()" />
                        <Button v-if="face.background.image_file_id" type="negative" size="xs" icon="fal fa-times" :label="ctrans('Remove')" @click="clearBackgroundImage" />
                    </div>
                    <p class="text-xs leading-relaxed text-gray-500">
                        {{ ctrans("Click an item to move, resize or rotate it. Arrow keys nudge by 1 mm (Shift: 5 mm), Delete removes it, Ctrl+Z undoes.") }}
                    </p>
                </template>
            </aside>
        </div>
    </div>
</template>
