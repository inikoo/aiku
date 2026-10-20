<!--
  - Author Louis Perez
  - Created on 17-10-2026-14h-10m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import axios from "axios"
import pdfWorkerUrl from "pdfjs-dist/legacy/build/pdf.worker.min.mjs?url"
import { notify } from "@kyvg/vue3-notification"
import Popover from "primevue/popover"
import Dialog from "primevue/dialog"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import Checkbox from "primevue/checkbox"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import DesignBoxNet, { type NetPin } from "@/Components/Goods/ProductDevelopment/Design/DesignBoxNet.vue"
import DesignFaceEditor from "@/Components/Goods/ProductDevelopment/Design/DesignFaceEditor.vue"
import DesignArtworkViewer, { type ArtworkPin, type ArtworkStroke } from "@/Components/Goods/ProductDevelopment/Design/DesignArtworkViewer.vue"
import DesignLabelContent from "@/Components/Goods/ProductDevelopment/Design/DesignLabelContent.vue"
import DesignComments, { type DesignComment } from "@/Components/Goods/ProductDevelopment/Design/DesignComments.vue"
import { faceSize, hasSize, type DesignData, type DesignDataSources, type DesignDataValues, type DesignImage, type DesignLayout, type FaceKey } from "@/Components/Goods/ProductDevelopment/Design/boxNet"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import type { Mentionable } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentCommentComposer.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faBoxOpen, faTag, faLock, faPencilRuler, faEye, faUndo, faPlus, faPaperPlane, faCheck, faCommentAlt, faArrowLeft, faSpinner, faCloudUpload, faMapPin, faPencil, faForward, faFilePdf, faExclamationTriangle, faWindowMaximize, faWindowRestore, faDownload, faFileImage } from "@fal"
library.add(faWindowMaximize, faWindowRestore, faDownload, faFileImage, faLightbulbOn, faBoxOpen, faTag, faLock, faPencilRuler, faEye, faUndo, faPlus, faPaperPlane, faCheck, faCommentAlt, faArrowLeft, faSpinner, faCloudUpload, faMapPin, faPencil, faForward, faFilePdf, faExclamationTriangle)

interface Status {
    value: "draft" | "in_review" | "changes_requested" | "approved"
    label: string
    color: string
    icon: string
}

interface Check {
    key: string
    label: string
    ticked_by: string | null
    ticked_at: string | null
    can_tick: boolean
}

interface VersionUpload {
    kind: "pdf" | "image"
    name: string
    url: string
    preview_url: string
}

interface Version {
    id: number
    version: number
    status: Status
    notes: string | null
    source: "editor" | "upload"
    layout: DesignLayout | null
    upload: VersionUpload | null
    approved_by: string | null
    approved_at: string | null
    can_edit: boolean
    can_review: boolean
    can_comment: boolean
    checks: Check[]
    all_checked: boolean
    next_round: string | null
    routes: Record<"update" | "image" | "submit" | "review" | "artwork", routeType>
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    rounds: { value: string, label: string, icon: string, unlocked: boolean, skipped: boolean, status: Status | null, version: number | null, progress: { done: number, total: number } | null, route: routeType }[]
    round: { value: string, label: string, per_sku: boolean }
    skus: { id: number, code: string, name: string, status: Status | null, version: number | null, route: routeType }[]
    sku: number | null
    artworkLocked: boolean
    labelContent: (InstanceType<typeof DesignLabelContent>["$props"]["data"] & { values: DesignDataValues }) | null
    dataSources: DesignDataSources
    versions: { id: number, version: number, status: Status, submitted_by: string | null, submitted_at: string | null, created_at: string | null, comments: number, route: routeType }[]
    version: Version | null
    previous: { version: number, source: "editor" | "upload", layout: DesignLayout | null, upload: VersionUpload | null } | null
    images: DesignImage[]
    comments: { threads: DesignComment[], mentionables: Mentionable[], route: routeType } | null
    commentStatusRoute: string
    startVersion: { route: routeType, round: string, sku: number | null, next: number, can_build: boolean } | null
    skip: { route: routeType, skipped: { by: string | null, at: string | null } | null, can_skip: boolean, can_undo: boolean } | null
    lockedReason: string | null
    developmentRoute: routeType
}>()

const clone = <T,>(value: T): T => JSON.parse(JSON.stringify(value))

const layout = ref<DesignLayout | null>(props.version?.layout ? clone(props.version.layout) : null)
const notes = ref(props.version?.notes ?? "")
const uploadedImages = ref<DesignImage[]>([])
const imageMap = computed<Record<number, string | null>>(() => Object.fromEntries([...props.images, ...uploadedImages.value].map((image) => [image.id, image.url])))

watch(() => props.version?.id, () => {
    layout.value = props.version?.layout ? clone(props.version.layout) : null
    notes.value = props.version?.notes ?? ""
    lastSaved = payloadNow()
    pendingMarkup.value = null
    editingFace.value = null
})

const canEdit = computed(() => !!props.version?.can_edit)

const designData = computed<DesignData>(() => ({ sources: props.dataSources, values: props.labelContent?.values ?? null }))

const saveState = ref<"idle" | "saving" | "saved" | "error">("idle")
let saveTimer: ReturnType<typeof setTimeout> | undefined
const payloadNow = () => JSON.stringify(props.version?.source === "editor" && layout.value ? { layout: layout.value, notes: notes.value } : { notes: notes.value })
let lastSaved = payloadNow()

function scheduleSave() {
    if (!canEdit.value || !props.version) {
        return
    }
    const payload = payloadNow()
    if (payload === lastSaved) {
        return
    }
    clearTimeout(saveTimer)
    saveTimer = setTimeout(() => save(payload), 900)
}

async function save(payload: string) {
    if (!props.version) {
        return
    }
    saveState.value = "saving"
    try {
        await axios.patch(route(props.version.routes.update.name, props.version.routes.update.parameters), JSON.parse(payload))
        lastSaved = payload
        saveState.value = "saved"
    } catch (error: any) {
        saveState.value = "error"
        const message = Object.values(error?.response?.data?.errors ?? {}).flat()[0] as string | undefined
        notify({ title: ctrans("Design not saved"), text: message ?? error?.response?.data?.message ?? "", type: "error" })
    }
}

watch([layout, notes], scheduleSave, { deep: true })

async function flushSave() {
    clearTimeout(saveTimer)
    const payload = payloadNow()
    if (canEdit.value && payload !== lastSaved) {
        await save(payload)
    }
}

const unitFactor = computed(() => layout.value?.units === "cm" ? 10 : 1)
const unitOptions = [{ value: "mm", label: "mm" }, { value: "cm", label: "cm" }]

function sizeIn(key: "width" | "length" | "height") {
    const mm = layout.value?.[key]
    return mm ? Math.round((mm / unitFactor.value) * 10) / 10 : null
}

function setSize(key: "width" | "length" | "height", value: number | null) {
    if (layout.value) {
        layout.value[key] = value ? Math.round(value * unitFactor.value * 10) / 10 : null
    }
}

const editingFace = ref<FaceKey | null>(null)

const downloadMenu = ref<InstanceType<typeof Popover> | null>(null)
type DesignExportFormat = "pdf" | "jpg"
const exporting = ref<DesignExportFormat | null>(null)
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

/**
 * The PDF is drawn on the server as vector artwork at real size, from the saved version, so unsaved edits
 * are saved first. The JPG is that PDF rendered here at the dpi chosen, lowered only as far as this device
 * can hold the image (iPhones and iPads allow about 16 million pixels).
 */
async function downloadFromServer(format: DesignExportFormat, designRoundId: number) {
    await flushSave()
    const developmentSlug = (props.developmentRoute.parameters as Record<string, unknown> | undefined)?.productDevelopment
    const response = await axios.get(route("grp.product_developments.design.pdf", { productDevelopment: String(developmentSlug), designRound: designRoundId }), { responseType: "blob" })
    const pdfName = /filename="([^"]+)"/.exec(response.headers["content-disposition"] ?? "")?.[1] ?? "design.pdf"

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

async function download(format: DesignExportFormat) {
    downloadMenu.value?.hide()
    if (!props.version || exporting.value) {
        return
    }
    exporting.value = format
    try {
        await downloadFromServer(format, props.version.id)
    } catch {
        notify({ title: ctrans("Download failed"), text: ctrans("The design could not be turned into a :format", { format: format.toUpperCase() }), type: "error" })
    } finally {
        exporting.value = null
    }
}

/** The designer opens full screen; it can be switched to a window and back */
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
    sheet: ctrans("Label"),
}))

const sizeKeys = computed(() => layout.value?.kind === "sheet" ? (["width", "height"] as const) : (["width", "length", "height"] as const))

const compare = ref(false)

type PendingMarkup = { type: "pin", x: number, y: number, page?: number } | { type: "stroke", page: number, points: [number, number][] }

const pendingMarkup = ref<PendingMarkup | null>(null)
const activeCommentId = ref<number | null>(null)
const tool = ref<"pin" | "draw">("pin")

const threadsOfVersion = computed(() => (props.comments?.threads ?? []).filter((thread) => thread.version === props.version?.version))
const markupCount = computed(() => threadsOfVersion.value.filter((thread) => thread.pin || thread.stroke).length)

const pins = computed<NetPin[]>(() => {
    const list: NetPin[] = threadsOfVersion.value
        .filter((thread) => thread.pin)
        .map((thread) => ({ id: thread.id, number: thread.pin!.number, x: thread.pin!.x, y: thread.pin!.y, resolved: thread.status === "resolved", active: thread.id === activeCommentId.value }))
    if (pendingMarkup.value?.type === "pin") {
        list.push({ id: -1, number: markupCount.value + 1, x: pendingMarkup.value.x, y: pendingMarkup.value.y, resolved: false, active: true })
    }
    return list
})

const artworkPins = computed<ArtworkPin[]>(() => [
    ...threadsOfVersion.value.filter((thread) => thread.pin).map((thread) => ({ id: thread.id, number: thread.pin!.number, x: thread.pin!.x, y: thread.pin!.y, page: thread.pin!.page ?? 1, resolved: thread.status === "resolved", active: thread.id === activeCommentId.value })),
    ...(pendingMarkup.value?.type === "pin" ? [{ id: -1, number: markupCount.value + 1, x: pendingMarkup.value.x, y: pendingMarkup.value.y, page: pendingMarkup.value.page ?? 1, resolved: false, active: true }] : []),
])

const artworkStrokes = computed<ArtworkStroke[]>(() => [
    ...threadsOfVersion.value.filter((thread) => thread.stroke).map((thread) => ({ id: thread.id, number: thread.stroke!.number, page: thread.stroke!.page, points: thread.stroke!.points, resolved: thread.status === "resolved", active: thread.id === activeCommentId.value })),
    ...(pendingMarkup.value?.type === "stroke" ? [{ id: -2, number: markupCount.value + 1, page: pendingMarkup.value.page, points: pendingMarkup.value.points, resolved: false, active: true }] : []),
])

function onPin(point: { x: number, y: number, page?: number }) {
    pendingMarkup.value = { type: "pin", ...point }
    activeCommentId.value = null
}

function onStroke(stroke: { page: number, points: [number, number][] }) {
    pendingMarkup.value = { type: "stroke", ...stroke }
    activeCommentId.value = null
}

const toolOptions = computed(() => [
    { value: "pin" as const, label: ctrans("Pin"), icon: "fal fa-map-pin" },
    { value: "draw" as const, label: ctrans("Draw"), icon: "fal fa-pencil" },
])

const canSubmit = computed(() => props.version?.source === "upload" ? !!props.version.upload : hasSize(layout.value))

const busy = ref<string | null>(null)

function send(routeName: routeType, payload: Record<string, string>, key: string, onSuccess?: () => void) {
    router.patch(route(routeName.name, routeName.parameters), payload, {
        preserveScroll: true,
        onStart: () => busy.value = key,
        onFinish: () => busy.value = null,
        onSuccess: () => onSuccess?.(),
        onError: (errors) => notify({ title: ctrans("Not saved"), text: Object.values(errors).join(" "), type: "error" }),
    })
}

async function submitForReview() {
    if (!props.version) {
        return
    }
    await flushSave()
    send(props.version.routes.submit, {}, "submit")
}

const CHECK_DEBOUNCE_MS = 500

/** A tick shows at once; it is saved once that box has been left alone for half a second */
const pendingChecks = ref<Record<string, boolean>>({})
const checkTimers = new Map<string, ReturnType<typeof setTimeout>>()

const isTicked = (check: Check) => pendingChecks.value[check.key] ?? !!check.ticked_by

function toggleCheck(check: Check, ticked: boolean) {
    pendingChecks.value[check.key] = ticked
    scheduleCheckSave(check, CHECK_DEBOUNCE_MS)
}

function scheduleCheckSave(check: Check, delay: number) {
    clearTimeout(checkTimers.get(check.key))
    checkTimers.set(check.key, setTimeout(() => {
        if (busy.value !== null) {
            scheduleCheckSave(check, 150)
            return
        }
        checkTimers.delete(check.key)
        const saved = !!props.version?.checks.find((candidate) => candidate.key === check.key)?.ticked_by
        const wanted = pendingChecks.value[check.key]
        if (!props.version || wanted === undefined || wanted === saved) {
            delete pendingChecks.value[check.key]
            return
        }
        send(props.version.routes.review, { operation: wanted ? "check" : "uncheck", check: check.key }, `check-${check.key}`)
    }, delay))
}

watch(() => props.version?.checks, (checks) => {
    for (const check of checks ?? []) {
        if (!checkTimers.has(check.key) && pendingChecks.value[check.key] === !!check.ticked_by) {
            delete pendingChecks.value[check.key]
        }
    }
}, { deep: true })

onBeforeUnmount(() => checkTimers.forEach((timer) => clearTimeout(timer)))

function review(operation: "approve" | "request_changes") {
    if (props.version) {
        send(props.version.routes.review, { operation }, operation)
    }
}

const choosingSource = ref(false)

function startVersionClicked() {
    if (props.startVersion?.can_build) {
        choosingSource.value = true
        return
    }
    beginVersion("upload")
}

function beginVersion(source: "editor" | "upload") {
    if (!props.startVersion) {
        return
    }
    choosingSource.value = false
    router.post(route(props.startVersion.route.name, props.startVersion.route.parameters), { round: props.startVersion.round, product_development_item_id: props.startVersion.sku, source }, {
        onStart: () => busy.value = "start",
        onFinish: () => busy.value = null,
        onError: (errors) => notify({ title: ctrans("Version not started"), text: Object.values(errors).join(" "), type: "error" }),
    })
}

const artworkInput = ref<HTMLInputElement | null>(null)
const uploadProgress = ref<number | null>(null)

function onArtworkPicked(event: Event) {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0]
    input.value = ""
    if (!file || !props.version) {
        return
    }
    router.post(route(props.version.routes.artwork.name, props.version.routes.artwork.parameters), { file }, {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => uploadProgress.value = 0,
        onProgress: (event) => uploadProgress.value = Math.round(event?.percentage ?? 0),
        onFinish: () => uploadProgress.value = null,
        onError: (errors) => notify({ title: ctrans("File not uploaded"), text: Object.values(errors).join(" "), type: "error" }),
    })
}

const confirmingSkip = ref(false)

function skipRound(operation: "skip" | "undo") {
    if (!props.skip) {
        return
    }
    router.patch(route(props.skip.route.name, props.skip.route.parameters), { round: round.value, operation }, {
        preserveScroll: true,
        onStart: () => busy.value = "skip",
        onFinish: () => busy.value = null,
        onSuccess: () => confirmingSkip.value = false,
        onError: (errors) => notify({ title: ctrans("Not changed"), text: Object.values(errors).join(" "), type: "error" }),
    })
}

const round = computed(() => props.round)

const formatWhen = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM" }) : ""
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <Link :href="route(developmentRoute.name, developmentRoute.parameters)">
                <Button type="negative" icon="fal fa-arrow-left" :label="ctrans('Back to development')" />
            </Link>
        </template>
    </PageHeading>

    <div class="space-y-4 px-4 pb-6 md:px-6">
        <div class="border-b border-gray-200">
        <nav class="flex gap-2 overflow-x-auto overflow-y-hidden [scrollbar-width:thin]" :aria-label="ctrans('Design rounds')">
            <component
                :is="item.unlocked ? Link : 'span'"
                v-for="item in rounds"
                :key="item.value"
                :href="item.unlocked ? route(item.route.name, item.route.parameters) : undefined"
                class="flex shrink-0 items-center gap-2 border-b-2 px-3 py-2 text-sm"
                :class="item.value === round.value ? 'border-[--app-accent] font-medium text-[--app-accent]' : item.unlocked ? 'border-transparent text-gray-600 hover:text-gray-900' : 'cursor-not-allowed border-transparent text-gray-400'"
            >
                <FontAwesomeIcon :icon="item.unlocked ? item.icon : 'fal fa-lock'" fixed-width aria-hidden="true" />
                {{ item.label }}
                <span v-if="item.skipped" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ ctrans("Skipped") }}</span>
                <span v-else-if="item.status" class="rounded px-1.5 py-0.5 text-xs" :class="productDevelopmentColor(item.status.color).count">v{{ item.version }} · {{ item.status.label }}</span>
                <span v-else-if="item.progress && item.progress.total" class="rounded px-1.5 py-0.5 text-xs" :class="item.progress.done === item.progress.total ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'">{{ ctrans(":done of :total SKUs approved", { done: String(item.progress.done), total: String(item.progress.total) }) }}</span>
            </component>
        </nav>
        </div>

        <div v-if="artworkLocked" class="rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 text-sm text-green-800">
            <FontAwesomeIcon icon="fal fa-lock" fixed-width aria-hidden="true" /> {{ ctrans("The artwork is signed off and locked: no new versions can be started") }}
        </div>

        <div v-if="round.per_sku && !lockedReason" class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:thin]" :aria-label="ctrans('SKUs')">
            <Link
                v-for="item in skus"
                :key="item.id"
                :href="route(item.route.name, item.route.parameters)"
                preserve-scroll
                class="flex shrink-0 items-center gap-2 rounded-md border px-3 py-1.5 text-sm transition-colors"
                :class="item.id === sku ? 'border-[--app-accent] bg-[--app-accent-soft] text-[--app-accent-deep]' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'"
            >
                <span class="font-medium">{{ item.code }}</span>
                <span v-if="item.status" class="rounded px-1.5 py-0.5 text-xs" :class="productDevelopmentColor(item.status.color).count">v{{ item.version }} · {{ item.status.label }}</span>
                <span v-else class="text-xs text-gray-400">{{ ctrans("Not started") }}</span>
            </Link>
        </div>

        <div v-if="skip?.skipped" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            <span>
                <FontAwesomeIcon icon="fal fa-forward" fixed-width aria-hidden="true" />
                {{ ctrans("The :round round was skipped by :user on :date", { round: round.label.toLowerCase(), user: skip.skipped.by ?? "", date: formatWhen(skip.skipped.at) }) }}
            </span>
            <Button v-if="skip.can_undo" type="tertiary" size="xs" icon="fal fa-undo" :label="ctrans('Bring it back')" :loading="busy === 'skip'" @click="skipRound('undo')" />
        </div>
        <div v-else-if="skip?.can_skip && !lockedReason" class="flex justify-end">
            <Button type="tertiary" size="xs" icon="fal fa-forward" :label="ctrans('Skip the :round round', { round: round.label.toLowerCase() })" @click="confirmingSkip = true" />
        </div>

        <div v-if="lockedReason" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <FontAwesomeIcon icon="fal fa-lock" fixed-width aria-hidden="true" /> {{ lockedReason }}
        </div>

        <div v-else-if="!skip?.skipped" class="grid gap-4 lg:grid-cols-[13rem_minmax(0,1fr)] xl:grid-cols-[13rem_minmax(0,1fr)_22rem]">
            <aside class="space-y-2">
                <h2 class="text-sm font-medium text-gray-700">{{ ctrans("Versions") }}</h2>
                <Link
                    v-for="item in versions"
                    :key="item.id"
                    :href="route(item.route.name, item.route.parameters)"
                    class="block rounded-lg border bg-white px-3 py-2.5 text-sm transition-colors"
                    :class="item.id === version?.id ? 'border-[--app-accent] ring-1 ring-[--app-accent]' : 'border-gray-200 hover:border-gray-300'"
                    preserve-scroll
                >
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900">v{{ item.version }}</span>
                        <span class="rounded px-1.5 py-0.5 text-xs" :class="productDevelopmentColor(item.status.color).count">{{ item.status.label }}</span>
                    </div>
                    <div class="mt-0.5 text-xs text-gray-500">
                        {{ [item.submitted_by, formatWhen(item.submitted_at ?? item.created_at)].filter(Boolean).join(" · ") }}
                        <template v-if="item.comments"> · {{ ctrans(":count comments", { count: String(item.comments) }) }}</template>
                    </div>
                </Link>
                <p v-if="!versions.length" class="text-sm text-gray-400">{{ ctrans("No version yet") }}</p>
                <Button v-if="startVersion" type="tertiary" full icon="fal fa-plus" :label="ctrans('Start v:version', { version: String(startVersion.next) })" :loading="busy === 'start'" @click="startVersionClicked" />
            </aside>

            <section class="min-w-0 space-y-3 rounded-lg border border-gray-200 bg-white p-4">
                <template v-if="version && version.source === 'upload'">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="flex min-w-0 items-center gap-2 text-sm text-gray-700">
                            <FontAwesomeIcon :icon="version.upload?.kind === 'pdf' ? 'fal fa-file-pdf' : 'fal fa-image'" class="text-gray-400" fixed-width aria-hidden="true" />
                            <a v-if="version.upload" :href="version.upload.url" class="primaryLink truncate">{{ version.upload.name }}</a>
                            <span v-else class="text-gray-400">{{ ctrans("No file yet") }}</span>
                        </span>
                        <Button v-if="canEdit" type="tertiary" size="xs" icon="fal fa-cloud-upload" :label="version.upload ? ctrans('Replace file') : ctrans('Upload PDF or picture')" :loading="uploadProgress !== null" @click="artworkInput?.click()" />
                        <SegmentedToggle v-if="!canEdit && version.can_comment && version.upload" v-model="tool" :options="toolOptions" :ariaLabel="ctrans('Markup tool')" class="ml-auto" />
                        <label v-if="previous?.upload" class="flex items-center gap-2 text-sm text-gray-700" :class="!canEdit && version.can_comment ? '' : 'ml-auto'">
                            <Checkbox v-model="compare" binary />
                            {{ ctrans("Compare with v:version", { version: String(previous.version) }) }}
                        </label>
                        <input ref="artworkInput" type="file" accept="application/pdf,image/jpeg,image/png,image/webp" class="hidden" @change="onArtworkPicked" />
                    </div>
                    <div v-if="uploadProgress !== null" class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full bg-[--app-accent] transition-all" :style="{ width: uploadProgress + '%' }" />
                    </div>
                    <p v-if="!canEdit && version.can_comment && version.upload" class="text-xs text-gray-500">
                        {{ tool === "draw" ? ctrans("Draw on the artwork, then write your comment") : ctrans("Click the artwork to pin a comment") }}
                    </p>

                    <div v-if="version.upload" class="grid min-w-0 gap-4" :class="compare && previous?.upload ? 'md:grid-cols-2' : ''">
                        <div v-if="compare && previous?.upload" class="min-w-0 space-y-1">
                            <span class="text-xs font-medium text-gray-500">v{{ previous.version }}</span>
                            <DesignArtworkViewer :upload="previous.upload" />
                        </div>
                        <div class="min-w-0 space-y-1">
                            <span v-if="compare && previous?.upload" class="text-xs font-medium text-gray-500">v{{ version.version }}</span>
                            <DesignArtworkViewer
                                :upload="version.upload"
                                :pins="artworkPins"
                                :strokes="artworkStrokes"
                                :nextNumber="markupCount + 1"
                                :tool="!canEdit && version.can_comment ? tool : null"
                                @pin="onPin"
                                @stroke="onStroke"
                                @focus="(id) => activeCommentId = id"
                            />
                        </div>
                    </div>
                    <button
                        v-else-if="canEdit"
                        type="button"
                        class="flex w-full flex-col items-center justify-center gap-2 rounded-md border-2 border-dashed border-gray-200 px-4 py-14 text-sm text-gray-500 hover:border-[--app-accent] hover:text-[--app-accent]"
                        @click="artworkInput?.click()"
                    >
                        <FontAwesomeIcon icon="fal fa-cloud-upload" class="text-3xl" fixed-width aria-hidden="true" />
                        {{ ctrans("Upload the :round as a PDF or picture", { round: round.label.toLowerCase() }) }}
                        <span class="text-xs text-gray-400">PDF, JPG, PNG, WEBP · {{ ctrans("up to 50 MB") }}</span>
                    </button>
                    <p v-else class="py-10 text-center text-sm text-gray-400">{{ ctrans("The designer has not uploaded the file yet") }}</p>

                    <div v-if="canEdit" class="space-y-1">
                        <label class="text-xs text-gray-500">{{ ctrans("Note for reviewers") }}</label>
                        <Textarea v-model="notes" rows="2" autoResize class="w-full text-sm" :placeholder="ctrans('What changed in this version?')" />
                    </div>
                    <p v-else-if="version.notes" class="whitespace-pre-line rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-700">{{ version.notes }}</p>

                    <div v-if="canEdit" class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-3">
                        <span class="text-xs text-gray-500">
                            <template v-if="saveState === 'saving'"><FontAwesomeIcon icon="fal fa-spinner" spin fixed-width aria-hidden="true" /> {{ ctrans("Saving…") }}</template>
                            <template v-else-if="saveState === 'saved'"><FontAwesomeIcon icon="fal fa-check" fixed-width aria-hidden="true" /> {{ ctrans("Saved") }}</template>
                            <template v-else-if="saveState === 'error'"><span class="text-red-600">{{ ctrans("Not saved") }}</span></template>
                        </span>
                        <Button
                            type="primary"
                            icon="fal fa-paper-plane"
                            :label="ctrans('Send v:version for review', { version: String(version.version) })"
                            :disabled="!canSubmit"
                            :loading="busy === 'submit'"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            @click="submitForReview"
                        />
                    </div>
                </template>

                <template v-else-if="version && layout">
                    <div class="flex flex-wrap items-center gap-3">
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
                        <div class="ml-auto flex flex-wrap items-center gap-3">
                            <label v-if="previous?.layout && hasSize(previous.layout)" class="flex items-center gap-2 text-sm text-gray-700">
                                <Checkbox v-model="compare" binary />
                                {{ ctrans("Compare with v:version", { version: String(previous.version) }) }}
                            </label>
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
                                        <button
                                            type="button"
                                            role="menuitem"
                                            class="flex min-w-0 flex-1 items-center gap-2.5 px-2 py-2 text-left text-gray-800"
                                            @click="download(format.value)"
                                        >
                                            <FontAwesomeIcon :icon="format.icon" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                                            <span class="flex-1">{{ format.label }}</span>
                                            <span v-if="format.hint" class="text-xs text-gray-400">{{ format.hint }}</span>
                                        </button>
                                        <label v-if="format.value === 'jpg'" class="flex shrink-0 items-center gap-1 pr-2 text-xs text-gray-500" @click.stop>
                                            <InputNumber
                                                v-model="jpgDpi"
                                                :min="JPG_DPI.min"
                                                :max="JPG_DPI.max"
                                                :step="50"
                                                :useGrouping="false"
                                                size="small"
                                                inputClass="!w-16 !py-1 text-right text-xs"
                                                :aria-label="ctrans('JPG resolution in dpi')"
                                                @keydown.enter="download('jpg')"
                                            />
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

                    <p class="text-xs text-gray-500">
                        <template v-if="canEdit">{{ layout.kind === "sheet" ? ctrans("Click the label to design it") : ctrans("Click a side of the box to design it") }}</template>
                        <template v-else-if="version.can_comment">{{ layout.kind === "sheet" ? ctrans("Label · click anywhere to pin a comment") : ctrans("Box net · click anywhere to pin a comment") }}</template>
                    </p>

                    <div class="grid min-w-0 gap-4" :class="compare && previous?.layout ? 'md:grid-cols-2' : ''">
                        <div v-if="compare && previous?.layout" class="min-w-0 space-y-1">
                            <span class="text-xs font-medium text-gray-500">v{{ previous.version }}</span>
                            <div class="rounded-md bg-gray-50 p-3">
                                <DesignBoxNet :layout="previous.layout" :images="imageMap" :maxHeight="520" :data="designData" />
                            </div>
                        </div>
                        <div class="min-w-0 space-y-1">
                            <span v-if="compare && previous?.layout" class="text-xs font-medium text-gray-500">v{{ version.version }}</span>
                            <div class="rounded-md bg-gray-50 p-3">
                                <DesignBoxNet
                                    :layout="layout"
                                    :images="imageMap"
                                    :selectable="canEdit"
                                    :pinnable="!canEdit && version.can_comment"
                                    :pins="pins"
                                    :maxHeight="compare ? 520 : 640"
                                    :data="designData"
                                    @select="(face) => editingFace = face"
                                    @pin="onPin"
                                    @focusPin="(id) => activeCommentId = id"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-if="canEdit" class="space-y-1">
                        <label class="text-xs text-gray-500">{{ ctrans("Note for reviewers") }}</label>
                        <Textarea v-model="notes" rows="2" autoResize class="w-full text-sm" :placeholder="ctrans('What changed in this version?')" />
                    </div>
                    <p v-else-if="version.notes" class="whitespace-pre-line rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-700">{{ version.notes }}</p>

                    <div v-if="canEdit" class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-3">
                        <span class="text-xs text-gray-500">
                            <template v-if="saveState === 'saving'"><FontAwesomeIcon icon="fal fa-spinner" spin fixed-width aria-hidden="true" /> {{ ctrans("Saving…") }}</template>
                            <template v-else-if="saveState === 'saved'"><FontAwesomeIcon icon="fal fa-check" fixed-width aria-hidden="true" /> {{ ctrans("Saved") }}</template>
                            <template v-else-if="saveState === 'error'"><span class="text-red-600">{{ ctrans("Not saved") }}</span></template>
                        </span>
                        <Button
                            type="primary"
                            icon="fal fa-paper-plane"
                            :label="ctrans('Send v:version for review', { version: String(version.version) })"
                            :disabled="!canSubmit"
                            :loading="busy === 'submit'"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            @click="submitForReview"
                        />
                    </div>
                </template>

                <div v-else class="flex flex-col items-center justify-center gap-3 py-16 text-center text-sm text-gray-500">
                    <FontAwesomeIcon icon="fal fa-pencil-ruler" class="text-3xl text-gray-300" fixed-width aria-hidden="true" />
                    <p>{{ ctrans("No :round design yet", { round: round.label.toLowerCase() }) }}</p>
                    <Button v-if="startVersion" type="primary" icon="fal fa-plus" :label="ctrans('Start v1')" :loading="busy === 'start'" @click="startVersionClicked" />
                </div>
            </section>

            <aside class="min-w-0 space-y-4 lg:col-span-2 lg:grid lg:grid-cols-2 lg:items-start lg:gap-4 lg:space-y-0 xl:col-span-1 xl:block xl:space-y-4">
                <section v-if="version && comments" class="rounded-lg border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-800">{{ ctrans("Comments on v:version", { version: String(version.version) }) }}</h2>
                    <DesignComments
                        :data="comments"
                        :version="version.version"
                        :designRoundId="version.id"
                        :statusRoute="commentStatusRoute"
                        :pendingMarkup="pendingMarkup"
                        :pendingNumber="markupCount + 1"
                        :activeId="activeCommentId"
                        @cancelPin="pendingMarkup = null"
                        @posted="pendingMarkup = null"
                        @focus="(id) => activeCommentId = id"
                    />
                </section>

                <DesignLabelContent v-if="labelContent" :data="labelContent" :collapsed="version?.status.value === 'in_review'" />

                <section v-if="version && version.status.value === 'in_review'" class="space-y-3 rounded-lg border border-gray-200 bg-white p-4">
                    <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("Before approving") }}</h2>
                    <ul class="space-y-2">
                        <li v-for="check in version.checks" :key="check.key" class="flex items-start gap-2 text-sm">
                            <Checkbox :modelValue="isTicked(check)" binary :disabled="!check.can_tick" :inputId="`check-${check.key}`" class="mt-0.5" @update:modelValue="(value) => toggleCheck(check, !!value)" />
                            <label :for="`check-${check.key}`" class="min-w-0">
                                <span class="text-gray-800">{{ check.label }}</span>
                                <span v-if="check.ticked_by" class="block text-xs text-gray-500">{{ check.ticked_by }} · {{ formatWhen(check.ticked_at) }}</span>
                            </label>
                        </li>
                    </ul>
                    <div v-if="version.can_review" class="space-y-2 pt-1">
                        <div class="grid grid-cols-2 gap-2">
                            <Button type="negative" full icon="fal fa-undo" :label="ctrans('Request changes')" :loading="busy === 'request_changes'" @click="review('request_changes')" />
                            <Button type="primary" full icon="fal fa-check" :label="ctrans('Approve')" :disabled="!version.all_checked" :loading="busy === 'approve'" class="disabled:cursor-not-allowed disabled:opacity-50" @click="review('approve')" />
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ version.next_round
                                ? ctrans("Approving locks v:version and creates the :round task for the designer.", { version: String(version.version), round: version.next_round.toLowerCase() })
                                : ctrans("Approving locks v:version.", { version: String(version.version) }) }}
                        </p>
                    </div>
                </section>

                <section v-if="version && version.status.value === 'approved'" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <FontAwesomeIcon icon="fal fa-lock" fixed-width aria-hidden="true" />
                    {{ ctrans("Approved by :user on :date, locked", { user: version.approved_by ?? "", date: formatWhen(version.approved_at) }) }}
                </section>
            </aside>
        </div>

        <Dialog v-model:visible="choosingSource" modal :header="ctrans('How do you want to make v:version?', { version: String(startVersion?.next ?? 1) })" class="w-[36rem] max-w-[calc(100vw-2rem)]">
            <div class="grid gap-3 sm:grid-cols-2">
                <button type="button" class="flex flex-col items-start gap-2 rounded-lg border border-gray-200 p-4 text-left transition-colors hover:border-[--app-accent] hover:bg-[--app-accent-soft]" @click="beginVersion('editor')">
                    <FontAwesomeIcon icon="fal fa-pencil-ruler" class="text-xl text-[--app-accent]" fixed-width aria-hidden="true" />
                    <span class="font-medium text-gray-900">{{ ctrans("Create it in Aiku") }}</span>
                    <span class="text-xs text-gray-500">{{ round.value === "label" ? ctrans("Lay out the label page: text, images, compliance and barcode areas.") : ctrans("Lay out each side of the box: text, images, compliance and barcode areas.") }}</span>
                </button>
                <button type="button" class="flex flex-col items-start gap-2 rounded-lg border border-gray-200 p-4 text-left transition-colors hover:border-[--app-accent] hover:bg-[--app-accent-soft]" @click="beginVersion('upload')">
                    <FontAwesomeIcon icon="fal fa-cloud-upload" class="text-xl text-[--app-accent]" fixed-width aria-hidden="true" />
                    <span class="font-medium text-gray-900">{{ ctrans("Upload a PDF or picture") }}</span>
                    <span class="text-xs text-gray-500">{{ ctrans("Use artwork made elsewhere. Reviewers pin and draw their comments on it.") }}</span>
                </button>
            </div>
        </Dialog>

        <Dialog v-model:visible="confirmingSkip" modal :header="ctrans('Skip the :round round?', { round: round.label.toLowerCase() })" class="w-[30rem] max-w-[calc(100vw-2rem)]">
            <div class="flex gap-3 text-sm text-gray-700">
                <FontAwesomeIcon icon="fal fa-exclamation-triangle" class="mt-0.5 text-lg text-amber-500" fixed-width aria-hidden="true" />
                <p>{{ ctrans("No :round artwork will be designed or reviewed for this development, and the next round opens straight away. Only skip it if this development really does not need it. You can bring it back until the next round is approved.", { round: round.label.toLowerCase() }) }}</p>
            </div>
            <template #footer>
                <Button type="tertiary" :label="ctrans('Cancel')" @click="confirmingSkip = false" />
                <Button type="negative" icon="fal fa-forward" :label="ctrans('Skip the :round round', { round: round.label.toLowerCase() })" :loading="busy === 'skip'" @click="skipRound('skip')" />
            </template>
        </Dialog>

        <Dialog
            :visible="!!editingFace && !!layout"
            modal
            :class="editorMaximized ? '!m-0 h-screen !max-h-screen w-screen !max-w-none !rounded-none' : 'h-[92vh] w-[96vw] max-w-[110rem]'"
            :pt="{ content: { class: 'flex-1 min-h-0 !p-0' }, root: { class: 'flex flex-col' } }"
            @update:visible="(value) => !value && (editingFace = null)"
        >
            <template #header>
                <div class="flex min-w-0 flex-1 items-center gap-2">
                    <span class="truncate text-lg font-semibold text-gray-900">{{ editingFace ? [faceLabels[editingFace], skus.find((item) => item.id === sku)?.code, `v${version?.version}`].filter(Boolean).join(" · ") : "" }}</span>
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
                v-if="editingFace && layout && version"
                :modelValue="layout.faces[editingFace]!"
                @update:modelValue="(face) => layout && editingFace && (layout.faces[editingFace] = face)"
                :size="faceSize(layout, editingFace)"
                :label="faceLabels[editingFace]"
                :images="imageMap"
                :imageRoute="version.routes.image"
                :data="designData"
                @imageUploaded="(image) => uploadedImages.push(image)"
            />
        </Dialog>
    </div>
</template>
