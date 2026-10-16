<!--
  - Author Louis Perez
  - Created on 17-10-2026-14h-10m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { Head, Link, router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import InputNumber from "primevue/inputnumber"
import Textarea from "primevue/textarea"
import Checkbox from "primevue/checkbox"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import DesignBoxNet, { type NetPin } from "@/Components/Goods/ProductDevelopment/Design/DesignBoxNet.vue"
import DesignFaceEditor from "@/Components/Goods/ProductDevelopment/Design/DesignFaceEditor.vue"
import DesignLabelContent from "@/Components/Goods/ProductDevelopment/Design/DesignLabelContent.vue"
import DesignComments, { type DesignComment } from "@/Components/Goods/ProductDevelopment/Design/DesignComments.vue"
import { faceSize, hasSize, type DesignImage, type DesignLayout, type FaceKey } from "@/Components/Goods/ProductDevelopment/Design/boxNet"
import { productDevelopmentColor } from "@/Components/Goods/ProductDevelopment/productDevelopmentColors"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import type { Mentionable } from "@/Components/Goods/ProductDevelopment/ProductDevelopmentCommentComposer.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faBoxOpen, faTag, faLock, faPencilRuler, faEye, faUndo, faPlus, faPaperPlane, faCheck, faCommentAlt, faArrowLeft, faSpinner, faCloudUpload } from "@fal"
library.add(faLightbulbOn, faBoxOpen, faTag, faLock, faPencilRuler, faEye, faUndo, faPlus, faPaperPlane, faCheck, faCommentAlt, faArrowLeft, faSpinner, faCloudUpload)

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

interface Version {
    id: number
    version: number
    status: Status
    notes: string | null
    layout: DesignLayout
    approved_by: string | null
    approved_at: string | null
    can_edit: boolean
    can_review: boolean
    can_comment: boolean
    checks: Check[]
    all_checked: boolean
    next_round: string | null
    routes: Record<"update" | "image" | "submit" | "review", routeType>
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    rounds: { value: string, label: string, icon: string, unlocked: boolean, status: Status | null, version: number | null, progress: { done: number, total: number } | null, route: routeType }[]
    round: { value: string, label: string, per_sku: boolean }
    skus: { id: number, code: string, name: string, status: Status | null, version: number | null, route: routeType }[]
    sku: number | null
    artworkLocked: boolean
    labelContent: InstanceType<typeof DesignLabelContent>["$props"]["data"] | null
    versions: { id: number, version: number, status: Status, submitted_by: string | null, submitted_at: string | null, created_at: string | null, comments: number, route: routeType }[]
    version: Version | null
    previous: { version: number, layout: DesignLayout } | null
    images: DesignImage[]
    comments: { threads: DesignComment[], mentionables: Mentionable[], route: routeType } | null
    commentStatusRoute: string
    startVersion: { route: routeType, round: string, sku: number | null, next: number } | null
    lockedReason: string | null
    developmentRoute: routeType
}>()

const clone = <T,>(value: T): T => JSON.parse(JSON.stringify(value))

const layout = ref<DesignLayout | null>(props.version ? clone(props.version.layout) : null)
const notes = ref(props.version?.notes ?? "")
const uploadedImages = ref<DesignImage[]>([])
const imageMap = computed<Record<number, string | null>>(() => Object.fromEntries([...props.images, ...uploadedImages.value].map((image) => [image.id, image.url])))

watch(() => props.version?.id, () => {
    layout.value = props.version ? clone(props.version.layout) : null
    notes.value = props.version?.notes ?? ""
    pendingPin.value = null
    editingFace.value = null
})

const canEdit = computed(() => !!props.version?.can_edit)

const saveState = ref<"idle" | "saving" | "saved" | "error">("idle")
let saveTimer: ReturnType<typeof setTimeout> | undefined
let lastSaved = JSON.stringify({ layout: layout.value, notes: notes.value })

function scheduleSave() {
    if (!canEdit.value || !props.version || !layout.value) {
        return
    }
    const payload = JSON.stringify({ layout: layout.value, notes: notes.value })
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
    const payload = JSON.stringify({ layout: layout.value, notes: notes.value })
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
const faceLabels = computed<Record<FaceKey, string>>(() => ({
    front: ctrans("Front"),
    back: ctrans("Back"),
    left: ctrans("Left side"),
    right: ctrans("Right side"),
    top: ctrans("Top"),
    bottom: ctrans("Bottom"),
}))

const compare = ref(false)

const pendingPin = ref<{ x: number, y: number } | null>(null)
const activeCommentId = ref<number | null>(null)

const pins = computed<NetPin[]>(() => {
    const list: NetPin[] = (props.comments?.threads ?? [])
        .filter((thread) => thread.pin && thread.version === props.version?.version)
        .map((thread) => ({ id: thread.id, number: thread.pin!.number, x: thread.pin!.x, y: thread.pin!.y, resolved: thread.status === "resolved", active: thread.id === activeCommentId.value }))
    if (pendingPin.value) {
        list.push({ id: -1, number: list.length + 1, x: pendingPin.value.x, y: pendingPin.value.y, resolved: false, active: true })
    }
    return list
})

function onPin(point: { x: number, y: number }) {
    pendingPin.value = point
    activeCommentId.value = null
}

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

function toggleCheck(check: Check, ticked: boolean) {
    if (props.version) {
        send(props.version.routes.review, { operation: ticked ? "check" : "uncheck", check: check.key }, `check-${check.key}`)
    }
}

function review(operation: "approve" | "request_changes") {
    if (props.version) {
        send(props.version.routes.review, { operation }, operation)
    }
}

function beginVersion() {
    if (!props.startVersion) {
        return
    }
    router.post(route(props.startVersion.route.name, props.startVersion.route.parameters), { round: props.startVersion.round, product_development_item_id: props.startVersion.sku }, {
        onStart: () => busy.value = "start",
        onFinish: () => busy.value = null,
        onError: (errors) => notify({ title: ctrans("Version not started"), text: Object.values(errors).join(" "), type: "error" }),
    })
}

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
                <span v-if="item.status" class="rounded px-1.5 py-0.5 text-xs" :class="productDevelopmentColor(item.status.color).count">v{{ item.version }} · {{ item.status.label }}</span>
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

        <div v-if="lockedReason" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <FontAwesomeIcon icon="fal fa-lock" fixed-width aria-hidden="true" /> {{ lockedReason }}
        </div>

        <div v-else class="grid gap-4 lg:grid-cols-[13rem_minmax(0,1fr)_22rem]">
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
                <Button v-if="startVersion" type="tertiary" full icon="fal fa-plus" :label="ctrans('Start v:version', { version: String(startVersion.next) })" :loading="busy === 'start'" @click="beginVersion" />
            </aside>

            <section class="min-w-0 space-y-3 rounded-lg border border-gray-200 bg-white p-4">
                <template v-if="version && layout">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <template v-for="(key, index) in (['width', 'length', 'height'] as const)" :key="key">
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
                        <label v-if="previous && hasSize(previous.layout)" class="ml-auto flex items-center gap-2 text-sm text-gray-700">
                            <Checkbox v-model="compare" binary />
                            {{ ctrans("Compare with v:version", { version: String(previous.version) }) }}
                        </label>
                    </div>

                    <p class="text-xs text-gray-500">
                        <template v-if="canEdit">{{ ctrans("Click a side of the box to design it") }}</template>
                        <template v-else-if="version.can_comment">{{ ctrans("Box net · click anywhere to pin a comment") }}</template>
                    </p>

                    <div class="grid gap-4" :class="compare && previous ? 'md:grid-cols-2' : ''">
                        <div v-if="compare && previous" class="space-y-1">
                            <span class="text-xs font-medium text-gray-500">v{{ previous.version }}</span>
                            <div class="rounded-md bg-gray-50 p-3">
                                <DesignBoxNet :layout="previous.layout" :images="imageMap" :maxHeight="520" />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span v-if="compare && previous" class="text-xs font-medium text-gray-500">v{{ version.version }}</span>
                            <div class="rounded-md bg-gray-50 p-3">
                                <DesignBoxNet
                                    :layout="layout"
                                    :images="imageMap"
                                    :selectable="canEdit"
                                    :pinnable="!canEdit && version.can_comment"
                                    :pins="pins"
                                    :maxHeight="compare ? 520 : 640"
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
                            :disabled="!hasSize(layout)"
                            :loading="busy === 'submit'"
                            class="disabled:cursor-not-allowed disabled:opacity-50"
                            @click="submitForReview"
                        />
                    </div>
                </template>

                <div v-else class="flex flex-col items-center justify-center gap-3 py-16 text-center text-sm text-gray-500">
                    <FontAwesomeIcon icon="fal fa-pencil-ruler" class="text-3xl text-gray-300" fixed-width aria-hidden="true" />
                    <p>{{ ctrans("No :round design yet", { round: round.label.toLowerCase() }) }}</p>
                    <Button v-if="startVersion" type="primary" icon="fal fa-plus" :label="ctrans('Start v1')" :loading="busy === 'start'" @click="beginVersion" />
                </div>
            </section>

            <aside class="space-y-4">
                <DesignLabelContent v-if="labelContent" :data="labelContent" />
                <section v-if="version && comments" class="rounded-lg border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-800">{{ ctrans("Comments on v:version", { version: String(version.version) }) }}</h2>
                    <DesignComments
                        :data="comments"
                        :version="version.version"
                        :designRoundId="version.id"
                        :statusRoute="commentStatusRoute"
                        :pendingPin="pendingPin"
                        :pendingNumber="pins.length"
                        :activeId="activeCommentId"
                        @cancelPin="pendingPin = null"
                        @posted="pendingPin = null"
                        @focus="(id) => activeCommentId = id"
                    />
                </section>

                <section v-if="version && version.status.value === 'in_review'" class="space-y-3 rounded-lg border border-gray-200 bg-white p-4">
                    <h2 class="text-sm font-semibold text-gray-800">{{ ctrans("Before approving") }}</h2>
                    <ul class="space-y-2">
                        <li v-for="check in version.checks" :key="check.key" class="flex items-start gap-2 text-sm">
                            <Checkbox :modelValue="!!check.ticked_by" binary :disabled="!check.can_tick || busy !== null" :inputId="`check-${check.key}`" class="mt-0.5" @update:modelValue="(value) => toggleCheck(check, !!value)" />
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

        <Dialog
            :visible="!!editingFace && !!layout"
            modal
            :header="editingFace ? `${faceLabels[editingFace]} · v${version?.version}` : ''"
            class="h-[92vh] w-[96vw] max-w-[110rem]"
            :pt="{ content: { class: 'flex-1 min-h-0 !p-0' }, root: { class: 'flex flex-col' } }"
            @update:visible="(value) => !value && (editingFace = null)"
        >
            <DesignFaceEditor
                v-if="editingFace && layout && version"
                v-model="layout.faces[editingFace]"
                :size="faceSize(layout, editingFace)"
                :label="faceLabels[editingFace]"
                :images="imageMap"
                :imageRoute="version.routes.image"
                @imageUploaded="(image) => uploadedImages.push(image)"
            />
        </Dialog>
    </div>
</template>
