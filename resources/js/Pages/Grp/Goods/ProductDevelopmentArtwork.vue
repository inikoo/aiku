<!--
  - Author Louis Perez
  - Created on 22-10-2026-17h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import axios from "axios"
import pdfWorkerUrl from "pdfjs-dist/legacy/build/pdf.worker.min.mjs?url"
import { notify } from "@kyvg/vue3-notification"
import Popover from "primevue/popover"
import Dialog from "primevue/dialog"
import InputNumber from "primevue/inputnumber"
import InputText from "primevue/inputtext"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import DesignBoxNet from "@/Components/Goods/ProductDevelopment/Design/DesignBoxNet.vue"
import DesignFaceEditor from "@/Components/Goods/ProductDevelopment/Design/DesignFaceEditor.vue"
import DesignLabelContent from "@/Components/Goods/ProductDevelopment/Design/DesignLabelContent.vue"
import { faceSize, hasSize, type DesignData, type DesignDataSources, type DesignDataValues, type DesignImage, type DesignLayout, type FaceKey } from "@/Components/Goods/ProductDevelopment/Design/boxNet"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faArrowLeft, faSpinner, faCheck, faPaperPlane, faDownload, faFilePdf, faFileImage, faWindowMaximize, faWindowRestore, faExclamationCircle, faLock } from "@fal"
library.add(faArrowLeft, faSpinner, faCheck, faPaperPlane, faDownload, faFilePdf, faFileImage, faWindowMaximize, faWindowRestore, faExclamationCircle, faLock)

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    artwork: {
        id: number
        section: string
        sku: { code: string, name: string }
        version: number
        status: { value: string, label: string, color: string, icon: string }
        layout: DesignLayout
        review_comment: string | null
        reviewed_by: string | null
        can_edit: boolean
        routes: Record<"layout" | "image" | "submit" | "pdf", routeType>
    }
    labelContent: InstanceType<typeof DesignLabelContent>["$props"]["data"] & { values: DesignDataValues }
    images: DesignImage[]
    dataSources: DesignDataSources
    developmentRoute: routeType
}>()

const clone = <T,>(value: T): T => JSON.parse(JSON.stringify(value))

const layout = ref<DesignLayout>(clone(props.artwork.layout))
const uploadedImages = ref<DesignImage[]>([])
const imageMap = computed<Record<number, string | null>>(() => Object.fromEntries([...props.images, ...uploadedImages.value].map((image) => [image.id, image.url])))
const canEdit = computed(() => props.artwork.can_edit)
const designData = computed<DesignData>(() => ({ sources: props.dataSources, values: props.labelContent?.values ?? null }))

watch(() => props.artwork.id, () => {
    layout.value = clone(props.artwork.layout)
    lastSaved = JSON.stringify(layout.value)
})

const saveState = ref<"idle" | "saving" | "saved" | "error">("idle")
let saveTimer: ReturnType<typeof setTimeout> | undefined
let lastSaved = JSON.stringify(layout.value)

function scheduleSave() {
    const payload = JSON.stringify(layout.value)
    if (!canEdit.value || payload === lastSaved) {
        return
    }
    clearTimeout(saveTimer)
    saveTimer = setTimeout(() => save(payload), 900)
}

async function save(payload: string) {
    saveState.value = "saving"
    try {
        await axios.patch(route(props.artwork.routes.layout.name, props.artwork.routes.layout.parameters), { layout: JSON.parse(payload) })
        lastSaved = payload
        saveState.value = "saved"
    } catch (error: any) {
        saveState.value = "error"
        const message = Object.values(error?.response?.data?.errors ?? {}).flat()[0] as string | undefined
        notify({ title: ctrans("Design not saved"), text: message ?? error?.response?.data?.message ?? "", type: "error" })
    }
}

watch(layout, scheduleSave, { deep: true })

async function flushSave() {
    clearTimeout(saveTimer)
    const payload = JSON.stringify(layout.value)
    if (canEdit.value && payload !== lastSaved) {
        await save(payload)
    }
}

const unitFactor = computed(() => layout.value.units === "cm" ? 10 : 1)
const unitOptions = [{ value: "mm", label: "mm" }, { value: "cm", label: "cm" }]
const sizeKeys = computed(() => layout.value.kind === "sheet" ? (["width", "height"] as const) : (["width", "length", "height"] as const))

function sizeIn(key: "width" | "length" | "height") {
    const mm = layout.value[key]
    return mm ? Math.round((mm / unitFactor.value) * 10) / 10 : null
}

function setSize(key: "width" | "length" | "height", value: number | null) {
    layout.value[key] = value ? Math.round(value * unitFactor.value * 10) / 10 : null
}

const editingFace = ref<FaceKey | null>(null)
const editorMaximized = ref(true)
watch(editingFace, (face, previous) => {
    if (face && !previous) {
        editorMaximized.value = true
    }
})
const faceLabels = computed<Record<FaceKey, string>>(() => ({
    front: ctrans("Front"),
    back: ctrans("Back"),
    left: ctrans("Left side"),
    right: ctrans("Right side"),
    top: ctrans("Top"),
    bottom: ctrans("Bottom"),
    sheet: props.artwork.section,
}))

/* Downloads: the PDF is drawn on the server as vector artwork at real size from what is saved; the JPG is that PDF rendered here */

const downloadMenu = ref<InstanceType<typeof Popover> | null>(null)
type ExportFormat = "pdf" | "jpg"
const exporting = ref<ExportFormat | null>(null)
const exportFormats = computed(() => [
    { value: "pdf" as const, label: "PDF", hint: ctrans("Vector, real size"), icon: "fal fa-file-pdf" },
    { value: "jpg" as const, label: "JPG", hint: "", icon: "fal fa-file-image" },
])
const JPG_DPI = { min: 72, max: 600, default: 300 }
const jpgDpi = ref(JPG_DPI.default)
const POINTS_PER_INCH = 72
const isAppleMobile = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1)
const MAX_IMAGE_PIXELS = isAppleMobile ? 16_000_000 : 50_000_000
const MAX_IMAGE_SIDE = 16_000

function saveBlob(blob: Blob, fileName: string) {
    const link = document.createElement("a")
    link.href = URL.createObjectURL(blob)
    link.download = fileName
    link.click()
    setTimeout(() => URL.revokeObjectURL(link.href), 10000)
}

async function downloadAs(format: ExportFormat) {
    await flushSave()
    const response = await axios.get(route(props.artwork.routes.pdf.name, props.artwork.routes.pdf.parameters), { responseType: "blob" })
    const pdfName = /filename="([^"]+)"/.exec(response.headers["content-disposition"] ?? "")?.[1] ?? "artwork.pdf"

    if (format === "pdf") {
        saveBlob(response.data, pdfName)
        return
    }

    const { getDocument, GlobalWorkerOptions } = await import("pdfjs-dist/legacy/build/pdf.mjs")
    GlobalWorkerOptions.workerSrc = pdfWorkerUrl
    const loadingTask = getDocument({ data: await (response.data as Blob).arrayBuffer() })
    const pdfDocument = await loadingTask.promise
    const page = await pdfDocument.getPage(1)
    const pageSize = page.getViewport({ scale: 1 })

    const wanted = Math.min(Math.max(Math.round(jpgDpi.value || JPG_DPI.default), JPG_DPI.min), JPG_DPI.max)
    const largest = Math.min(
        POINTS_PER_INCH * Math.sqrt(MAX_IMAGE_PIXELS / (pageSize.width * pageSize.height)),
        POINTS_PER_INCH * MAX_IMAGE_SIDE / Math.max(pageSize.width, pageSize.height),
    )
    const dpi = Math.max(Math.floor(Math.min(wanted, largest)), JPG_DPI.min)

    const viewport = page.getViewport({ scale: dpi / POINTS_PER_INCH })
    const canvas = document.createElement("canvas")
    canvas.width = Math.floor(viewport.width)
    canvas.height = Math.floor(viewport.height)
    const context = canvas.getContext("2d")!
    context.fillStyle = "#ffffff"
    context.fillRect(0, 0, canvas.width, canvas.height)
    await page.render({ canvasContext: context, viewport, canvas } as Parameters<typeof page.render>[0]).promise
    await loadingTask.destroy()

    const jpg = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, "image/jpeg", 0.92))
    canvas.width = 0
    canvas.height = 0
    if (!jpg) {
        throw new Error("jpg")
    }
    saveBlob(jpg, pdfName.replace(/\.pdf$/, `-${dpi}dpi.jpg`))
    if (dpi < wanted) {
        notify({ title: ctrans("Saved at :dpi dpi", { dpi: String(dpi) }), text: ctrans("That is the most this device can hold for a design this size."), type: "info" })
    }
}

async function download(format: ExportFormat) {
    downloadMenu.value?.hide()
    if (exporting.value) {
        return
    }
    exporting.value = format
    try {
        await downloadAs(format)
    } catch {
        notify({ title: ctrans("Download failed"), text: ctrans("The design could not be turned into a :format", { format: format.toUpperCase() }), type: "error" })
    } finally {
        exporting.value = null
    }
}

/* Sending for review */

const updateNote = ref("")
const submitting = ref(false)

async function submitForReview() {
    await flushSave()
    router.patch(route(props.artwork.routes.submit.name, props.artwork.routes.submit.parameters), { update_note: updateNote.value || null }, {
        preserveScroll: true,
        onStart: () => submitting.value = true,
        onFinish: () => submitting.value = false,
        onSuccess: () => {
            updateNote.value = ""
            notify({ title: ctrans("Sent for review"), type: "success" })
        },
        onError: (errors) => notify({ title: ctrans("Not sent"), text: Object.values(errors).join(" "), type: "error" }),
    })
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <Link :href="route(developmentRoute.name, developmentRoute.parameters)">
                <Button type="tertiary" icon="fal fa-arrow-left" :label="ctrans('Back to design')" />
            </Link>
        </template>
    </PageHeading>

    <div class="grid gap-4 px-4 pb-6 pt-4 md:px-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="min-w-0 space-y-3 rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-md border px-2 py-0.5 text-xs" :class="productDevelopmentColor(artwork.status.color).active">
                    <FontAwesomeIcon :icon="artwork.status.icon" fixed-width aria-hidden="true" />
                    {{ artwork.status.label }}<template v-if="artwork.version"> · V{{ artwork.version }}</template>
                </span>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <template v-for="(key, index) in sizeKeys" :key="key">
                        <span v-if="index" class="text-gray-400">×</span>
                        <label class="flex items-center gap-1">
                            <span class="text-xs text-gray-500">{{ ctrans(key === 'width' ? 'W' : key === 'length' ? 'L' : 'H') }}</span>
                            <InputNumber
                                :modelValue="sizeIn(key)"
                                :disabled="!canEdit"
                                :min="0"
                                :maxFractionDigits="1"
                                size="small"
                                inputClass="w-20"
                                :placeholder="ctrans(key === 'width' ? 'Width' : key === 'length' ? 'Length' : 'Height')"
                                @update:modelValue="(value) => setSize(key, value)"
                            />
                        </label>
                    </template>
                    <SegmentedToggle v-if="canEdit" v-model="layout.units" :options="unitOptions" :ariaLabel="ctrans('Units')" />
                    <span v-else class="text-gray-500">{{ layout.units }}</span>
                </div>
                <div class="ml-auto">
                    <Button
                        v-if="hasSize(layout)"
                        type="tertiary"
                        size="xs"
                        icon="fal fa-download"
                        :label="ctrans('Download')"
                        :loading="!!exporting"
                        aria-haspopup="menu"
                        @click="(event: Event) => downloadMenu?.toggle(event)"
                    />
                    <Popover ref="downloadMenu">
                        <div class="w-60 text-sm" role="menu">
                            <div v-for="format in exportFormats" :key="format.value" class="flex items-center gap-1 rounded-md hover:bg-gray-50">
                                <button type="button" role="menuitem" class="flex min-w-0 flex-1 items-center gap-2.5 px-2 py-2 text-left text-gray-800" @click="download(format.value)">
                                    <FontAwesomeIcon :icon="format.icon" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                                    <span class="flex-1">{{ format.label }}</span>
                                    <span v-if="format.hint" class="text-xs text-gray-400">{{ format.hint }}</span>
                                </button>
                                <label v-if="format.value === 'jpg'" class="flex shrink-0 items-center gap-1 pr-2 text-xs text-gray-500" @click.stop>
                                    <InputNumber v-model="jpgDpi" :min="JPG_DPI.min" :max="JPG_DPI.max" :step="50" :useGrouping="false" size="small" inputClass="!w-16 !py-1 text-right text-xs" :aria-label="ctrans('JPG resolution in dpi')" @keydown.enter="download('jpg')" />
                                    dpi
                                </label>
                            </div>
                            <div class="mx-2 mb-1 mt-1.5 space-y-1 rounded-md px-2 py-1.5 text-xs" :class="jpgDpi > JPG_DPI.default ? 'bg-amber-50 text-amber-800' : 'bg-gray-50 text-gray-500'">
                                <p>{{ ctrans("JPG is made on this device from the PDF, :min to :max dpi.", { min: String(JPG_DPI.min), max: String(JPG_DPI.max) }) }}</p>
                                <p v-if="jpgDpi > JPG_DPI.default">{{ ctrans("Above :dpi dpi it can take a while and use a lot of memory, especially on phones. iPhones and iPads save large designs at a lower dpi.", { dpi: String(JPG_DPI.default) }) }}</p>
                                <p v-else>{{ ctrans("For printing, use the PDF: it stays sharp at any size.") }}</p>
                            </div>
                        </div>
                    </Popover>
                </div>
            </div>

            <div v-if="artwork.status.value === 'changes_required' && artwork.review_comment" class="flex gap-2 rounded-md border border-orange-200 bg-orange-50 px-3 py-2 text-sm text-orange-900">
                <FontAwesomeIcon icon="fal fa-exclamation-circle" class="mt-0.5 shrink-0" fixed-width aria-hidden="true" />
                <span class="whitespace-pre-line"><span v-if="artwork.reviewed_by" class="font-medium">{{ artwork.reviewed_by }}: </span>{{ artwork.review_comment }}</span>
            </div>
            <p v-else-if="!canEdit" class="flex items-center gap-2 text-xs text-gray-500">
                <FontAwesomeIcon icon="fal fa-lock" fixed-width aria-hidden="true" />
                {{ artwork.status.value === "in_review" ? ctrans("In review: it can be changed again once it is approved or sent back.") : ctrans("This artwork cannot be changed now.") }}
            </p>
            <p v-else class="text-xs text-gray-500">{{ layout.kind === "sheet" ? ctrans("Click the page to design it") : ctrans("Click a side of the box to design it") }}</p>

            <div class="rounded-md bg-gray-50 p-3">
                <DesignBoxNet :layout="layout" :images="imageMap" :selectable="canEdit" :maxHeight="640" :data="designData" @select="(face) => editingFace = face" />
            </div>

            <div v-if="canEdit" class="flex flex-wrap items-end gap-3 border-t border-gray-100 pt-3">
                <label class="min-w-[16rem] flex-1 space-y-1">
                    <span class="text-xs text-gray-500">{{ ctrans("What changed (optional)") }}</span>
                    <InputText v-model="updateNote" size="small" class="w-full" maxlength="255" :placeholder="ctrans('e.g. Corrected barcode')" />
                </label>
                <span class="text-xs text-gray-500">
                    <template v-if="saveState === 'saving'"><FontAwesomeIcon icon="fal fa-spinner" spin fixed-width aria-hidden="true" /> {{ ctrans("Saving…") }}</template>
                    <template v-else-if="saveState === 'saved'"><FontAwesomeIcon icon="fal fa-check" fixed-width aria-hidden="true" /> {{ ctrans("Saved") }}</template>
                    <template v-else-if="saveState === 'error'"><span class="text-red-600">{{ ctrans("Not saved") }}</span></template>
                </span>
                <Button type="tertiary" :label="ctrans('Send for review')" :disabled="!hasSize(layout)" :loading="submitting" @click="submitForReview">
                    <template #icon><FontAwesomeIcon icon="fal fa-paper-plane" class="text-green-600" fixed-width aria-hidden="true" /></template>
                </Button>
            </div>
        </section>

        <aside class="min-w-0">
            <DesignLabelContent :data="labelContent" />
        </aside>

        <Dialog
            :visible="!!editingFace"
            modal
            :class="editorMaximized ? '!m-0 h-screen !max-h-screen w-screen !max-w-none !rounded-none' : 'h-[92vh] w-[96vw] max-w-[110rem]'"
            :pt="{ content: { class: 'flex-1 min-h-0 !p-0' }, root: { class: 'flex flex-col' } }"
            @update:visible="(value) => !value && (editingFace = null)"
        >
            <template #header>
                <div class="flex min-w-0 flex-1 items-center gap-2">
                    <span class="truncate text-lg font-semibold text-gray-900">{{ editingFace ? [faceLabels[editingFace], artwork.sku.code].filter(Boolean).join(" · ") : "" }}</span>
                    <button
                        type="button"
                        class="ml-auto flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800"
                        v-tooltip="editorMaximized ? ctrans('Show as a window') : ctrans('Maximise')"
                        :aria-label="editorMaximized ? ctrans('Show as a window') : ctrans('Maximise')"
                        @click="editorMaximized = !editorMaximized"
                    >
                        <FontAwesomeIcon :icon="editorMaximized ? 'fal fa-window-restore' : 'fal fa-window-maximize'" fixed-width aria-hidden="true" />
                    </button>
                </div>
            </template>
            <DesignFaceEditor
                v-if="editingFace"
                :modelValue="layout.faces[editingFace]!"
                @update:modelValue="(face) => editingFace && (layout.faces[editingFace] = face)"
                :size="faceSize(layout, editingFace)"
                :label="faceLabels[editingFace]"
                :images="imageMap"
                :imageRoute="artwork.routes.image"
                :data="designData"
                @imageUploaded="(image) => uploadedImages.push(image)"
            />
        </Dialog>
    </div>
</template>
