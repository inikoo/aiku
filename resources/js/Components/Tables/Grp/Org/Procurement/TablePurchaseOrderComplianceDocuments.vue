<script setup lang="ts">
import { ref } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Table from "@/Components/Table/Table.vue"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faUpload, faSpinnerThird, faExclamationTriangle } from "@fal"
library.add(faCheck, faUpload, faSpinnerThird, faExclamationTriangle)

interface Document {
    scope: string
    label: string
    count: number
    required: boolean
    supplier_only: boolean
}

const props = defineProps<{
    data: object
    tab?: string
    compliance?: {
        eta: string | null
        deadline: string | null
        buffer_days: number
        missing: number
        overdue: boolean
        upload_route: routeType | null
    } | null
}>()

const documentScopes = ["sds", "ifra", "clp", "allergen_declarations", "doc", "cpsr", "test_reports"]

const uploading = ref<string | null>(null)

async function upload(tradeUnitId: number, document: Document, event: Event) {
    const input = event.target as HTMLInputElement
    const files = Array.from(input.files ?? [])
    input.value = ""
    if (!files.length || !props.compliance?.upload_route) {
        return
    }
    uploading.value = `${tradeUnitId}-${document.scope}`
    try {
        await axios.post(
            route(props.compliance.upload_route.name, { ...props.compliance.upload_route.parameters, tradeUnit: tradeUnitId }),
            { attachments: files, scope: document.scope },
            { headers: { "Content-Type": "multipart/form-data" } }
        )
        router.reload({ only: [props.tab ?? "compliance", "compliance_summary"] })
    } catch (error: any) {
        notify({ title: ctrans("Something went wrong"), text: error?.response?.data?.message ?? "", type: "error" })
    } finally {
        uploading.value = null
    }
}
</script>

<template>
    <div>
        <p v-if="compliance" class="px-4 pt-3 text-sm" :class="compliance.overdue ? 'text-red-600' : 'text-gray-600'">
            <FontAwesomeIcon v-if="compliance.overdue" icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
            <template v-if="compliance.deadline">
                {{ ctrans("Documents due by :date, the arrival estimate (:eta) less :days days.", { date: useFormatTime(compliance.deadline, { formatTime: "d MMM yyyy" }), eta: useFormatTime(compliance.eta as string, { formatTime: "d MMM yyyy" }), days: compliance.buffer_days }) }}
            </template>
            <template v-else>{{ ctrans("No arrival estimate yet, so no due date for the documents.") }}</template>
            <template v-if="compliance.missing"> {{ ctrans(":count required documents missing.", { count: compliance.missing }) }}</template>
        </p>

        <Table :resource="data" :name="tab">
            <template v-for="scope in documentScopes" :key="scope" #[`cell(${scope})`]="{ item }">
                <div class="flex items-center justify-center gap-2">
                    <span v-if="item[scope]?.count" class="text-green-600 tabular-nums" v-tooltip="ctrans(':count uploaded', { count: item[scope].count })">
                        <FontAwesomeIcon icon="fal fa-check" fixed-width aria-hidden="true" />
                        <template v-if="item[scope].count > 1">{{ item[scope].count }}</template>
                    </span>
                    <span v-else :class="item[scope]?.required ? 'text-red-600' : 'text-gray-300'" v-tooltip="item[scope]?.required ? ctrans('Required') : ctrans('Optional')">-</span>
                    <label v-if="compliance?.upload_route" class="cursor-pointer text-gray-400 hover:text-gray-700" v-tooltip="ctrans('Upload :document', { document: item[scope]?.label })">
                        <FontAwesomeIcon :icon="uploading === `${item.id}-${scope}` ? 'fal fa-spinner-third' : 'fal fa-upload'" :spin="uploading === `${item.id}-${scope}`" fixed-width aria-hidden="true" />
                        <input type="file" multiple accept="image/*,.pdf" class="hidden" :disabled="!!uploading" @change="(event) => upload(item.id, item[scope], event)" />
                    </label>
                </div>
            </template>
        </Table>
    </div>
</template>
