<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 19 Oct 2026 Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { router } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import axios from "axios"
import Dialog from "primevue/dialog"
import Textarea from "primevue/textarea"
import FileUpload from "primevue/fileupload"
import MultiSelect from "primevue/multiselect"
import Select from "primevue/select"
import Tag from "primevue/tag"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faArrowLeft, faCheck, faEye, faTimes } from "@fal"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import type { SeoKeywordOptions, SeoKeywordRoutes, SeoKeywordTag, SeoResearchQuery } from "@/Components/Seo/types"

library.add(faArrowLeft, faCheck, faEye, faTimes)

type ImportRow = { line: number, keyword: string, status: string, reason: string | null }

type ImportResult = { rows: ImportRow[], summary: Record<string, number> }

const MAX_LINES = 1000

const props = defineProps<{
    routes: SeoKeywordRoutes
    options: SeoKeywordOptions
    query: SeoResearchQuery
    tags: SeoKeywordTag[]
    tab: string
}>()

const visible = defineModel<boolean>("visible", { default: false })

const locale = useLocaleStore()

const text = ref("")
const fileName = ref<string | null>(null)
const countryCodes = ref<string[]>(props.query.country_code ? [props.query.country_code] : [])
const languageCode = ref(props.query.language_code)
const frequency = ref("on_request")
const tagIds = ref<number[]>([])

const preview = ref<ImportResult | null>(null)
const result = ref<ImportResult | null>(null)
const error = ref<string | null>(null)
const busy = ref(false)

const lines = computed(() => text.value.trimEnd().split(/\r?\n/))

const lineCount = computed(() => lines.value.filter((line) => line.trim()).length)

watch([text, countryCodes], () => preview.value = null)

const statusLabels = computed<Record<string, { label: string, severity: string }>>(() => ({
    new: { label: ctrans("New"), severity: "success" },
    add_countries: { label: ctrans("Adds countries or devices"), severity: "success" },
    tracked: { label: ctrans("Already tracked"), severity: "secondary" },
    duplicate: { label: ctrans("Duplicate"), severity: "warn" },
    invalid: { label: ctrans("Invalid"), severity: "danger" },
    failed: { label: ctrans("Failed"), severity: "danger" },
}))

const readFile = async (file: File) => {
    error.value = null
    fileName.value = file.name

    try {
        const XLSX = await import("xlsx")
        const workbook = XLSX.read(await file.arrayBuffer(), { type: "array" })
        const sheet = workbook.Sheets[workbook.SheetNames[0]]
        const rows = XLSX.utils.sheet_to_json<unknown[]>(sheet, { header: 1, blankrows: false, defval: "" })

        const keywords = rows
            .map((row) => String((row as unknown[]).find((cell) => String(cell ?? "").trim() !== "") ?? "").trim())
            .filter((keyword, index) => keyword !== "" && !(index === 0 && /^keywords?$/i.test(keyword)))

        text.value = keywords.join("\n")
    } catch {
        error.value = ctrans("The file could not be read. Use a CSV or Excel file with one keyword per row in the first column.")
    }
}

const send = async (dryRun: boolean) => {
    busy.value = true
    error.value = null

    try {
        const response = await axios.post<ImportResult>(route(props.routes.bulkTrack.name, props.routes.bulkTrack.parameters), {
            lines: lines.value,
            country_codes: countryCodes.value,
            language_code: languageCode.value,
            frequency: frequency.value,
            tag_ids: tagIds.value,
            dry_run: dryRun,
        })

        if (dryRun) {
            preview.value = response.data
        } else {
            result.value = response.data
            router.reload({ only: [props.tab, "keywordFilters"] })
        }
    } catch (exception) {
        const errors = (exception as { response?: { data?: { errors?: Record<string, string[]>, message?: string } } }).response?.data

        error.value = Object.values(errors?.errors ?? {})[0]?.[0] ?? errors?.message ?? ctrans("Something went wrong, try again.")
    } finally {
        busy.value = false
    }
}

const toTrack = computed(() => (preview.value?.summary.new ?? 0) + (preview.value?.summary.add_countries ?? 0))

const summaryItems = (summary: Record<string, number>) => Object.entries(statusLabels.value)
    .filter(([status]) => summary[status])
    .map(([status, label]) => ({ status, label: label.label, severity: label.severity, count: summary[status] }))

const reset = () => {
    text.value = ""
    fileName.value = null
    preview.value = null
    result.value = null
    error.value = null
}

watch(visible, (isVisible) => {
    if (!isVisible) {
        reset()
    }
})

const shownRows = computed(() => (result.value ?? preview.value)?.rows ?? [])
</script>

<template>
    <Dialog v-model:visible="visible" modal :header="ctrans('Add keywords in bulk')" :style="{ width: 'min(52rem, 95vw)' }">
        <div v-if="result" class="space-y-4 text-sm">
            <p class="font-medium text-gray-900">{{ ctrans("Import finished") }}</p>
            <div class="flex flex-wrap gap-2">
                <Tag v-for="item in summaryItems(result.summary)" :key="item.status" :severity="item.severity" :value="`${item.label}: ${locale.number(item.count)}`" />
            </div>
            <p class="text-gray-600">{{ ctrans(":added keywords are now tracked. Skipped and failed lines are listed below.", { added: locale.number((result.summary.new ?? 0) + (result.summary.add_countries ?? 0)) }) }}</p>
        </div>

        <div v-else-if="!preview" class="space-y-4 text-sm">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-700">{{ ctrans("Countries") }}</span>
                    <MultiSelect v-model="countryCodes" :options="options.countries" optionLabel="label" optionValue="value" :maxSelectedLabels="2" :selectedItemsLabel="ctrans('{0} countries')" :placeholder="ctrans('Select countries')" filter class="w-full" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-700">{{ ctrans("Preferred language") }}</span>
                    <Select v-model="languageCode" :options="options.languages" optionLabel="label" optionValue="value" filter class="w-full" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-700">{{ ctrans("Check") }}</span>
                    <Select v-model="frequency" :options="options.frequencies" optionLabel="label" optionValue="value" class="w-full" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-700">{{ ctrans("Tags") }}</span>
                    <MultiSelect v-model="tagIds" :options="tags" optionLabel="name" optionValue="id" display="chip" filter :placeholder="tags.length ? ctrans('Optional') : ctrans('No tags yet')" :disabled="!tags.length" class="w-full" />
                </label>
            </div>

            <label class="flex flex-col gap-1">
                <span class="flex items-baseline justify-between gap-2">
                    <span class="font-medium text-gray-700">{{ ctrans("Keywords, one per line") }}</span>
                    <span class="text-xs tabular-nums" :class="lineCount > MAX_LINES ? 'text-red-600' : 'text-gray-500'">{{ ctrans(":count of :max", { count: locale.number(lineCount), max: locale.number(MAX_LINES) }) }}</span>
                </span>
                <Textarea v-model="text" rows="10" class="w-full font-mono text-sm" :placeholder="ctrans('incense sticks\nsandalwood incense\nincense holder')" />
            </label>

            <div class="flex flex-wrap items-center gap-3">
                <FileUpload mode="basic" :auto="false" customUpload :chooseButtonProps="{ severity: 'secondary', outlined: true, size: 'small' }" accept=".csv,.txt,.xlsx,.xls,.ods" :chooseLabel="ctrans('Import CSV or Excel')" @select="(event) => event.files?.[0] && readFile(event.files[0])" />
                <span v-if="fileName" class="text-xs text-gray-500">{{ ctrans("Read from :file", { file: fileName }) }}</span>
            </div>
            <p class="text-xs text-gray-500">{{ ctrans("A file is read from the first column of its first sheet, a heading called Keyword is skipped. You can edit the list before the preview.") }}</p>
        </div>

        <div v-else class="space-y-3 text-sm">
            <div class="flex flex-wrap gap-2">
                <Tag v-for="item in summaryItems(preview.summary)" :key="item.status" :severity="item.severity" :value="`${item.label}: ${locale.number(item.count)}`" />
            </div>
            <p class="text-gray-600">
                {{ ctrans(":count keywords will be tracked in :countries countries on mobile and desktop. Duplicates, invalid lines and keywords already tracked everywhere are skipped.", { count: locale.number(toTrack), countries: countryCodes.length }) }}
                <template v-if="tagIds.length">{{ ctrans("The tags go on every valid keyword, including those already tracked.") }}</template>
            </p>
        </div>

        <div v-if="shownRows.length" class="mt-3 max-h-80 overflow-y-auto rounded-lg ring-1 ring-gray-200">
            <table class="w-full text-sm">
                <thead class="sticky top-0 whitespace-nowrap bg-gray-50 text-left text-xs text-gray-500">
                    <tr>
                        <th scope="col" class="px-3 py-2 text-right font-medium">{{ ctrans("Line") }}</th>
                        <th scope="col" class="px-3 py-2 font-medium">{{ ctrans("Keyword") }}</th>
                        <th scope="col" class="px-3 py-2 font-medium">{{ ctrans("Result") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="row in shownRows" :key="row.line">
                        <td class="px-3 py-1.5 text-right tabular-nums text-gray-500">{{ row.line }}</td>
                        <td class="px-3 py-1.5 text-gray-900">{{ row.keyword }}</td>
                        <td class="px-3 py-1.5">
                            <Tag :severity="statusLabels[row.status]?.severity" :value="statusLabels[row.status]?.label ?? row.status" />
                            <span v-if="row.reason" class="ml-2 text-xs text-gray-500">{{ row.reason }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="error" role="alert" class="mt-3 text-sm text-red-600">{{ error }}</p>

        <template #footer>
            <div class="flex w-full justify-end gap-2">
                <template v-if="result">
                    <Button type="tertiary" icon="fal fa-check" :label="ctrans('Done')" @click="visible = false" />
                </template>
                <template v-else-if="preview">
                    <Button type="tertiary" icon="fal fa-arrow-left" :label="ctrans('Back')" @click="preview = null" />
                    <Button type="tertiary" icon="fal fa-check" :label="ctrans('Track :count keywords', { count: locale.number(toTrack) })" :loading="busy" :disabled="!toTrack && !tagIds.length" @click="send(false)" />
                </template>
                <template v-else>
                    <Button type="tertiary" icon="fal fa-times" :label="ctrans('Cancel')" @click="visible = false" />
                    <Button type="tertiary" icon="fal fa-eye" :label="ctrans('Preview')" :loading="busy" :disabled="!lineCount || lineCount > MAX_LINES || !countryCodes.length" @click="send(true)" />
                </template>
            </div>
        </template>
    </Dialog>
</template>
