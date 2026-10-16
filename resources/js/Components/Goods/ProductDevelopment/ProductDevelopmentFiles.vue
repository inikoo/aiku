<!--
  - Author Louis Perez
  - Created on 16-10-2026-16h-05m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import Select from "primevue/select"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faFileCertificate, faVial, faDrawSquare, faCamera, faPalette, faPaperclip, faCloudUpload, faTimes, faChevronDown, faDownload, faFolderOpen } from "@fal"
library.add(faFileCertificate, faVial, faDrawSquare, faCamera, faPalette, faPaperclip, faCloudUpload, faTimes, faChevronDown, faDownload, faFolderOpen)

interface FileRow {
    id: number
    name: string
    mime: string | null
    size: number | null
    version: number
    uploaded_by: string | null
    uploaded_at: string | null
    url: string | null
    preview_url: string | null
}

interface FileGroup {
    key: string
    scope: string
    label: string
    icon: string
    versioned: boolean
    sku: string | null
    current: FileRow[]
    earlier: FileRow[]
}

interface ScopeOption {
    value: string
    label: string
    icon: string
    per_sku: boolean
    versioned: boolean
}

const props = defineProps<{
    data: {
        groups: FileGroup[]
        upload: {
            route: routeType
            accept: string
            max_files: number
            max_mb: number
            scopes: ScopeOption[]
            items: { value: number, label: string }[]
        } | null
    }
}>()

const scope = ref<string | null>(null)
const itemId = ref<number | null>(props.data?.upload?.items.length === 1 ? props.data.upload.items[0].value : null)
const picked = ref<File[]>([])
const isDragging = ref(false)
const uploading = ref(false)
const progress = ref(0)
const errors = ref<Record<string, string>>({})
const openEarlier = ref<Record<string, boolean>>({})
const fileInput = ref<HTMLInputElement | null>(null)
const cameraInput = ref<HTMLInputElement | null>(null)

const selectedScope = computed(() => props.data?.upload?.scopes.find((option) => option.value === scope.value))
const needsSku = computed(() => !!selectedScope.value?.per_sku)
const canSubmit = computed(() => !!scope.value && picked.value.length > 0 && (!needsSku.value || !!itemId.value) && !uploading.value)

function addFiles(list: FileList | null) {
    if (!list) {
        return
    }
    picked.value = [...picked.value, ...Array.from(list)].slice(0, props.data.upload?.max_files ?? 20)
}

function onDrop(event: DragEvent) {
    isDragging.value = false
    addFiles(event.dataTransfer?.files ?? null)
}

function onPick(event: Event) {
    const input = event.target as HTMLInputElement
    addFiles(input.files)
    input.value = ""
}

function upload() {
    if (!props.data.upload || !canSubmit.value) {
        return
    }
    errors.value = {}
    router.post(route(props.data.upload.route.name, props.data.upload.route.parameters), {
        files: picked.value,
        scope: scope.value,
        product_development_item_id: needsSku.value ? itemId.value : null,
    }, {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => { uploading.value = true; progress.value = 0 },
        onProgress: (event) => { progress.value = Math.round(event?.percentage ?? 0) },
        onFinish: () => uploading.value = false,
        onError: (validationErrors) => errors.value = validationErrors,
        onSuccess: () => picked.value = [],
    })
}

const formatWhen = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy, HH:mm" }) : ""
const formatSize = (bytes: number | null) => !bytes ? "" : bytes > 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`
</script>

<template>
    <div v-if="data" class="space-y-4 p-4">
        <section v-if="data.upload" class="space-y-3 rounded-lg border border-gray-200 p-3">
            <div class="grid gap-2 sm:grid-cols-2">
                <Select v-model="scope" :options="data.upload.scopes" optionLabel="label" optionValue="value" size="small" :placeholder="ctrans('What are you uploading?')" class="w-full" overlayClass="text-sm">
                    <template #option="{ option }">
                        <span class="flex items-center gap-2">
                            <FontAwesomeIcon :icon="option.icon" class="text-gray-400" fixed-width aria-hidden="true" />
                            {{ option.label }}
                        </span>
                    </template>
                </Select>
                <Select v-if="needsSku" v-model="itemId" :options="data.upload.items" optionLabel="label" optionValue="value" size="small" :placeholder="ctrans('Which SKU?')" class="w-full" overlayClass="text-sm" :invalid="!!errors.product_development_item_id" />
            </div>

            <div
                class="flex flex-col items-center justify-center gap-2 rounded-md border-2 border-dashed px-4 py-6 text-center text-sm transition-colors"
                :class="isDragging ? 'border-[--app-accent] bg-[--app-accent-soft]' : 'border-gray-200 text-gray-500'"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="onDrop"
            >
                <FontAwesomeIcon icon="fal fa-cloud-upload" class="text-2xl text-gray-400" fixed-width aria-hidden="true" />
                <span>{{ ctrans("Drop files here, or") }}</span>
                <div class="flex flex-wrap justify-center gap-2">
                    <Button type="tertiary" size="xs" :label="ctrans('Choose files')" @click="fileInput?.click()">
                        <template #icon><FontAwesomeIcon icon="fal fa-folder-open" fixed-width aria-hidden="true" /></template>
                    </Button>
                    <Button type="tertiary" size="xs" :label="ctrans('Take photo')" @click="cameraInput?.click()">
                        <template #icon><FontAwesomeIcon icon="fal fa-camera" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>
                <span class="text-xs text-gray-400">{{ ctrans("PDF, AI, PSD, JPG, PNG, ZIP · up to :count files, :size MB each", { count: String(data.upload.max_files), size: String(data.upload.max_mb) }) }}</span>
                <input ref="fileInput" type="file" multiple :accept="data.upload.accept" class="hidden" @change="onPick" />
                <input ref="cameraInput" type="file" multiple accept="image/*" capture="environment" class="hidden" @change="onPick" />
            </div>

            <ul v-if="picked.length" class="divide-y divide-gray-100 rounded-md border border-gray-100 text-sm">
                <li v-for="(file, index) in picked" :key="index" class="flex items-center gap-2 px-3 py-1.5">
                    <span class="min-w-0 flex-1 truncate text-gray-700">{{ file.name }}</span>
                    <span class="shrink-0 text-xs tabular-nums text-gray-400">{{ formatSize(file.size) }}</span>
                    <button type="button" class="shrink-0 text-red-500 hover:text-red-600" :aria-label="ctrans('Remove')" @click="picked.splice(index, 1)">
                        <FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
                    </button>
                </li>
            </ul>

            <p v-if="Object.keys(errors).length" class="text-xs text-red-600">{{ Object.values(errors)[0] }}</p>

            <div class="flex items-center justify-end gap-3">
                <span v-if="selectedScope?.versioned" class="text-xs text-gray-500">{{ ctrans("A new upload replaces the current file; the old one is kept as an earlier version") }}</span>
                <div v-if="uploading" class="h-1.5 w-32 overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full bg-[--app-accent] transition-all" :style="{ width: progress + '%' }" />
                </div>
                <Button type="primary" size="xs" icon="fal fa-cloud-upload" :label="ctrans('Upload')" :loading="uploading" :disabled="!canSubmit" class="disabled:cursor-not-allowed disabled:opacity-50" @click="upload" />
            </div>
        </section>

        <section v-for="group in data.groups" :key="group.key" class="rounded-lg border border-gray-200">
            <header class="flex items-center gap-2 border-b border-gray-100 px-3 py-2 text-sm">
                <FontAwesomeIcon :icon="group.icon" class="text-gray-400" fixed-width aria-hidden="true" />
                <span class="font-medium text-gray-800">{{ group.label }}</span>
                <span v-if="group.sku" class="text-xs text-gray-500">{{ group.sku }}</span>
            </header>

            <ul class="divide-y divide-gray-100">
                <li v-for="file in group.current" :key="file.id" class="flex items-center gap-3 px-3 py-2 text-sm">
                    <img v-if="file.preview_url" :src="file.preview_url" :alt="file.name" class="h-10 w-10 shrink-0 rounded object-cover ring-1 ring-gray-200" loading="lazy" />
                    <div class="min-w-0 flex-1">
                        <a v-if="file.url" :href="file.url" class="primaryLink block truncate">{{ file.name }}</a>
                        <span v-else class="block truncate text-gray-400">{{ file.name }}</span>
                        <span class="text-xs text-gray-500">
                            <template v-if="group.versioned">v{{ file.version }} · </template>{{ [file.uploaded_by, formatWhen(file.uploaded_at), formatSize(file.size)].filter(Boolean).join(" · ") }}
                        </span>
                    </div>
                    <a v-if="file.url" :href="file.url" class="shrink-0 text-[--app-accent] hover:text-[--app-accent-strong]" :aria-label="ctrans('Download')">
                        <FontAwesomeIcon icon="fal fa-download" fixed-width aria-hidden="true" />
                    </a>
                </li>
            </ul>

            <div v-if="group.earlier.length" class="border-t border-gray-100">
                <button type="button" class="flex w-full items-center gap-1.5 px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700" @click="openEarlier[group.key] = !openEarlier[group.key]">
                    <FontAwesomeIcon icon="fal fa-chevron-down" class="transition-transform" :class="openEarlier[group.key] ? '' : '-rotate-90'" fixed-width aria-hidden="true" />
                    {{ ctrans("Earlier versions (:count)", { count: String(group.earlier.length) }) }}
                </button>
                <ul v-show="openEarlier[group.key]" class="divide-y divide-gray-50 bg-gray-50/50">
                    <li v-for="file in group.earlier" :key="file.id" class="flex items-center gap-3 px-3 py-1.5 text-xs text-gray-500">
                        <span class="w-8 shrink-0 tabular-nums">{{ file.version ? "v" + file.version : ctrans("before") }}</span>
                        <a v-if="file.url" :href="file.url" class="min-w-0 flex-1 truncate hover:text-gray-800 hover:underline">{{ file.name }}</a>
                        <span class="shrink-0">{{ [file.uploaded_by, formatWhen(file.uploaded_at)].filter(Boolean).join(" · ") }}</span>
                    </li>
                </ul>
            </div>
        </section>

        <p v-if="!data.groups.length" class="py-6 text-center text-sm text-gray-400">{{ ctrans("No files yet") }}</p>
    </div>
</template>
