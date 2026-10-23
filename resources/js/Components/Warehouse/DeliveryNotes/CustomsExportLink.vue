<script setup lang="ts">
import { computed, ref, watch } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Dialog from "primevue/dialog"
import Select from "primevue/select"
import InputText from "primevue/inputtext"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faDownload, faFileCsv, faQuestionCircle, faUndo } from "@fal"
import { ctrans } from "@/Composables/useTrans"
import { routeType } from "@/types/route"
import Button from "@/Components/Elements/Buttons/Button.vue"

library.add(faDownload, faFileCsv, faQuestionCircle, faUndo)

const props = defineProps<{
    customsExport: {
        formats: { value: string, label: string }[]
        preview_route: routeType
        download_route: routeType
    } | null
}>()

const isOpen = ref(false)
const format = ref<string | null>(null)
const delimiter = ref("|")
const textDelimiter = ref("")
const problems = ref<string[]>([])
const preview = ref<{
    columns: string[]
    rows: { cells: string[], errors: Record<string, string[]> }[]
    file_errors: string[]
} | null>(null)
// Values typed in the preview, by row then column. They only go into this file, the product is not changed
const edits = ref<Record<number, Record<number, string>>>({})
const isLoadingPreview = ref(false)
const isDownloading = ref(false)
let previewRequest = 0
let editTimer: ReturnType<typeof setTimeout> | undefined

const urlFor = (target: routeType | undefined) => {
    if (!target || !format.value || delimiter.value.length !== 1) {
        return null
    }

    return route(target.name, target.parameters)
}

const payload = () => ({
    format: format.value,
    delimiter: delimiter.value,
    ...(textDelimiter.value ? { text_delimiter: textDelimiter.value } : {}),
    edits: edits.value,
})

const cellValue = (rowIndex: number, cellIndex: number) => edits.value[rowIndex]?.[cellIndex] ?? preview.value?.rows[rowIndex]?.cells[cellIndex] ?? ""

const isEdited = (rowIndex: number, cellIndex: number) => edits.value[rowIndex]?.[cellIndex] !== undefined

const editCell = (rowIndex: number, cellIndex: number, value: string) => {
    const row = { ...(edits.value[rowIndex] ?? {}) }
    if (value === preview.value?.rows[rowIndex]?.cells[cellIndex]) {
        delete row[cellIndex]
    } else {
        row[cellIndex] = value
    }

    const next = { ...edits.value }
    if (Object.keys(row).length) {
        next[rowIndex] = row
    } else {
        delete next[rowIndex]
    }
    edits.value = next

    clearTimeout(editTimer)
    editTimer = setTimeout(() => loadPreview(false), 400)
}

const resetEdits = () => {
    edits.value = {}
    loadPreview(false)
}

const hasEdits = computed(() => Object.keys(edits.value).length > 0)

const rowFailures = computed(() => (preview.value?.rows ?? []).flatMap((row, rowIndex) =>
    Object.entries(row.errors).flatMap(([columnIndex, messages]) =>
        messages.map(message => ({ row: rowIndex + 1, column: preview.value?.columns[Number(columnIndex)], message }))
    )
))

const responseBody = async (error: any) => {
    const data = error?.response?.data

    return data instanceof Blob ? JSON.parse(await data.text()) : data
}

const showError = (body: any) => {
    notify({
        title: ctrans("Something went wrong"),
        text: body?.message ?? "",
        type: "error",
    })
}

// withSkeleton is off while editing cells, so the table (and the cell being typed in) stays in place
const loadPreview = async (withSkeleton = true) => {
    const url = urlFor(props.customsExport?.preview_route)
    if (!url) {
        return
    }

    const request = ++previewRequest
    if (withSkeleton) {
        isLoadingPreview.value = true
        preview.value = null
    }
    problems.value = []
    try {
        const { data } = await axios.post(url, payload())
        if (request === previewRequest) {
            preview.value = data
        }
    } catch (error: any) {
        if (request === previewRequest) {
            showError(await responseBody(error))
        }
    } finally {
        if (request === previewRequest) {
            isLoadingPreview.value = false
        }
    }
}

const openDialog = () => {
    format.value = props.customsExport?.formats[0]?.value ?? null
    edits.value = {}
    isOpen.value = true
}

watch([isOpen, format, delimiter, textDelimiter], ([open]) => {
    if (open && delimiter.value.length === 1) {
        loadPreview()
    }
})

const onDownload = async () => {
    const url = urlFor(props.customsExport?.download_route)
    if (!url) {
        return
    }

    clearTimeout(editTimer)
    isDownloading.value = true
    try {
        const response = await axios.post(url, payload(), { responseType: "blob" })
        const filename = /filename="?([^";]+)"?/.exec(response.headers["content-disposition"] ?? "")?.[1] ?? "customs-items.csv"
        const objectUrl = URL.createObjectURL(response.data)
        const link = document.createElement("a")
        link.href = objectUrl
        link.download = filename
        link.click()
        URL.revokeObjectURL(objectUrl)
        isOpen.value = false
    } catch (error: any) {
        const body = await responseBody(error)
        if (error?.response?.status === 422 && body?.errors) {
            problems.value = Object.values(body.errors).flat() as string[]
        } else {
            showError(body)
        }
    } finally {
        isDownloading.value = false
    }
}
</script>

<template>
    <div v-if="customsExport">
        <Button :label="ctrans('Customs items file')" type="tertiary" size="xs" @click="openDialog">
            <template #icon>
                <FontAwesomeIcon icon="fal fa-file-csv" fixed-width aria-hidden="true" />
            </template>
        </Button>

        <Dialog
            v-model:visible="isOpen"
            modal
            dismissableMask
            :draggable="false"
            :header="ctrans('Customs items file')"
            :style="{ width: '72rem' }"
            :breakpoints="{ '1199px': '90vw', '575px': '95vw' }"
        >
            <div class="space-y-4">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="w-full max-w-sm">
                        <label class="block text-sm text-gray-600 mb-1">{{ ctrans("Format") }}</label>
                        <Select v-model="format" :options="customsExport.formats" optionLabel="label" optionValue="value" class="w-full" />
                    </div>
                    <div>
                        <label for="customs-export-delimiter" class="flex items-center gap-1 text-sm text-gray-600 mb-1">
                            {{ ctrans("Field delimiter") }}
                            <FontAwesomeIcon
                                v-tooltip="ctrans('Must match the Field Delimiter set in DHL\'s item upload settings, otherwise DHL will not read the file into separate columns.')"
                                icon="fal fa-question-circle" class="text-gray-400 cursor-help" fixed-width aria-hidden="true" />
                        </label>
                        <InputText id="customs-export-delimiter" v-model="delimiter" maxlength="1" class="w-16 text-center" />
                    </div>
                    <div>
                        <label for="customs-export-text-delimiter" class="flex items-center gap-1 text-sm text-gray-600 mb-1">
                            {{ ctrans("Text delimiter") }}
                            <FontAwesomeIcon
                                v-tooltip="ctrans('Must match the Text Delimiter set in DHL\'s item upload settings. Leave it empty when DHL\'s is empty, so the file is written without quotes around the text.')"
                                icon="fal fa-question-circle" class="text-gray-400 cursor-help" fixed-width aria-hidden="true" />
                        </label>
                        <InputText id="customs-export-text-delimiter" v-model="textDelimiter" maxlength="1" class="w-16 text-center" />
                    </div>
                </div>

                <div v-if="problems.length || preview?.file_errors.length || rowFailures.length"
                    class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-600 max-h-48 overflow-y-auto">
                    <div class="font-semibold mb-1">{{ ctrans("Fix these before downloading:") }}</div>
                    <ul class="list-disc pl-5 space-y-0.5">
                        <li v-for="(problem, problemIndex) in [...problems, ...(preview?.file_errors ?? [])]" :key="`file-${problemIndex}`">{{ problem }}</li>
                        <li v-for="(failure, failureIndex) in rowFailures" :key="`row-${failureIndex}`">
                            {{ ctrans("Row :row", { row: String(failure.row) }) }} · {{ failure.column }}: {{ failure.message }}
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="text-sm text-gray-600">
                            {{ ctrans("Preview") }}
                            <span class="text-gray-400">· {{ ctrans("Click a cell to change it for this file only, the product is not changed.") }}</span>
                        </div>
                        <Button v-if="hasEdits" :label="ctrans('Undo changes')" type="tertiary" size="xs" @click="resetEdits">
                            <template #icon>
                                <FontAwesomeIcon icon="fal fa-undo" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </div>
                    <div v-if="isLoadingPreview" class="space-y-2">
                        <div v-for="index in 4" :key="index" class="h-6 rounded bg-gray-100 animate-pulse" />
                    </div>
                    <div v-else-if="preview?.rows.length" class="overflow-x-auto max-h-96 border border-gray-200 rounded">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-2 py-1.5 text-left font-semibold text-gray-600 border-b border-gray-200">#</th>
                                    <th v-for="(column, columnIndex) in preview.columns" :key="columnIndex"
                                        class="px-2 py-1.5 text-left font-semibold text-gray-600 whitespace-nowrap border-b border-gray-200">
                                        {{ column }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, rowIndex) in preview.rows" :key="rowIndex" class="odd:bg-white even:bg-gray-50">
                                    <td class="px-2 py-1 text-gray-400 border-b border-gray-100">{{ rowIndex + 1 }}</td>
                                    <td v-for="(cell, cellIndex) in row.cells" :key="cellIndex"
                                        v-tooltip="row.errors[cellIndex]?.join(' ') || (isEdited(rowIndex, cellIndex) ? ctrans('Was: :value', { value: cell || '-' }) : undefined)"
                                        class="p-0 border-b border-gray-100"
                                        :class="row.errors[cellIndex] ? 'bg-red-100 text-red-700 font-semibold' : isEdited(rowIndex, cellIndex) ? 'bg-amber-50 text-amber-800' : 'text-gray-700'">
                                        <input
                                            :value="cellValue(rowIndex, cellIndex)"
                                            :size="Math.max(cellValue(rowIndex, cellIndex).length, 4)"
                                            class="block px-2 py-1 bg-transparent border border-transparent rounded-sm hover:border-gray-300 focus:border-gray-400 focus:outline-none focus:ring-0 text-xs"
                                            @input="editCell(rowIndex, cellIndex, ($event.target as HTMLInputElement).value)"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-sm text-gray-400 italic">{{ ctrans("Nothing to preview") }}</div>
                </div>

                <div class="flex justify-end">
                    <Button :label="ctrans('Download')" type="primary" :loading="isDownloading" @click="onDownload">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-download" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>
