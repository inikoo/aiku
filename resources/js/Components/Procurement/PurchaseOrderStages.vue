<script setup lang="ts">
import { computed, ref } from "vue"
import { router } from "@inertiajs/vue3"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import Button from "@/Components/Elements/Buttons/Button.vue"
import DatePicker from "primevue/datepicker"
import Dialog from "primevue/dialog"
import Textarea from "primevue/textarea"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faCheckCircle, faPencil, faTimes, faTruck, faPaperclip, faUpload, faSpinnerThird } from "@fal"
library.add(faCheck, faCheckCircle, faPencil, faTimes, faTruck, faPaperclip, faUpload, faSpinnerThird)

interface Step {
    key: string
    label: string
    date: string | null
    by: string | null
    note?: string | null
}

interface Mark {
    field: string
    label: string
    date: string | null
    skipped: string[]
}

interface Evidence {
    scope: string
    label: string
    count: number
}

interface Stage {
    key: string
    number: number
    label: string
    description: string
    reached: boolean
    current: boolean
    date: string | null
    by: string | null
    expected: string | null
    days_late: number
    deliveries: { reached: number, total: number } | null
    not_required?: boolean
    steps: Step[]
    evidence: Evidence[]
    marks: Mark[]
}

const props = defineProps<{
    stages: Stage[]
    route: routeType
    uploadRoute?: routeType | null
}>()

const uploading = ref<string | null>(null)

async function upload(evidence: Evidence, event: Event) {
    const input = event.target as HTMLInputElement
    const files = Array.from(input.files ?? [])
    input.value = ""
    if (!files.length || !props.uploadRoute) {
        return
    }
    uploading.value = evidence.scope
    try {
        await axios.post(route(props.uploadRoute.name, props.uploadRoute.parameters), { attachments: files, scope: evidence.scope }, { headers: { "Content-Type": "multipart/form-data" } })
        router.reload({ only: ["stages"] })
    } catch (error: any) {
        notify({ title: ctrans("Something went wrong"), text: error?.response?.data?.message ?? "", type: "error" })
    } finally {
        uploading.value = null
    }
}

const formatDate = (date: string | null) => date ? useFormatTime(date, { formatTime: "d MMM yyyy" }) : null

const toIso = (date: Date | null) => date ? `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}` : null

const editing = ref<Mark | null>(null)
const date = ref<Date | null>(null)
const reason = ref("")
const saving = ref(false)
const errors = ref<Record<string, string>>({})

function openMark(mark: Mark) {
    editing.value = mark
    date.value = mark.date ? new Date(mark.date) : new Date()
    reason.value = ""
    errors.value = {}
}

const needsReason = computed(() => {
    if (!editing.value) {
        return false
    }
    if (!date.value) {
        return !!editing.value.date
    }
    const yesterday = new Date()
    yesterday.setDate(yesterday.getDate() - 1)
    return (toIso(date.value) as string) < (toIso(yesterday) as string) || editing.value.skipped.length > 0
})

function save() {
    if (!editing.value) {
        return
    }
    router.patch(route(props.route.name, props.route.parameters), {
        field: editing.value.field,
        date: toIso(date.value),
        reason: reason.value.trim() || null,
    }, {
        preserveScroll: true,
        onStart: () => saving.value = true,
        onFinish: () => saving.value = false,
        onError: (error) => errors.value = error,
        onSuccess: () => editing.value = null,
    })
}
</script>

<template>
    <div>
    <div class="grid grid-cols-2 gap-px bg-gray-200 border-b border-gray-300 md:grid-cols-4 xl:grid-cols-7">
        <div
            v-for="stage in stages"
            :key="stage.key"
            class="bg-white p-3 text-sm"
            :class="stage.current ? 'bg-[--app-accent-soft]' : ''"
            v-tooltip="stage.description"
        >
            <div class="flex items-center gap-2 font-medium" :class="stage.reached ? 'text-gray-800' : 'text-gray-400'">
                <FontAwesomeIcon v-if="stage.reached" icon="fal fa-check-circle" class="text-green-600" fixed-width aria-hidden="true" />
                <span v-else class="w-4 text-center tabular-nums">{{ stage.number }}</span>
                {{ stage.label }}
            </div>

            <div class="mt-1 tabular-nums" :class="stage.date ? 'text-gray-700' : 'text-gray-400'">
                <template v-if="stage.not_required">{{ ctrans("Not required") }}</template>
                <template v-else-if="stage.date">{{ formatDate(stage.date) }}</template>
                <template v-else-if="stage.reached">{{ ctrans("Not recorded") }}</template>
                <template v-else>-</template>
                <span v-if="stage.by" class="text-xs text-gray-500"> · {{ stage.by }}</span>
            </div>

            <div v-if="!stage.reached && stage.expected" class="mt-0.5 text-xs tabular-nums" :class="stage.days_late ? 'text-red-600' : 'text-gray-500'">
                {{ ctrans("Expected :date", { date: formatDate(stage.expected) as string }) }}
                <template v-if="stage.days_late"> · {{ ctrans(":days days late", { days: stage.days_late }) }}</template>
            </div>

            <div v-if="stage.deliveries && stage.deliveries.total" class="mt-1 flex items-center gap-1 text-xs text-gray-500">
                <FontAwesomeIcon icon="fal fa-truck" fixed-width aria-hidden="true" />
                {{ ctrans(":reached of :total deliveries", { reached: stage.deliveries.reached, total: stage.deliveries.total }) }}
            </div>

            <dl v-if="stage.steps.length" class="mt-2 space-y-0.5 text-xs">
                <div v-for="step in stage.steps" :key="step.key" class="flex justify-between gap-2">
                    <dt class="text-gray-500">{{ step.label }}</dt>
                    <dd class="tabular-nums text-right" :class="step.date ? 'text-gray-700' : 'text-gray-400'" v-tooltip="step.note ?? step.by">
                        {{ formatDate(step.date) ?? "-" }}
                        <div v-if="step.note" class="text-gray-500">{{ step.note }}</div>
                    </dd>
                </div>
            </dl>

            <div v-if="stage.evidence.length" class="mt-2 space-y-0.5 text-xs">
                <div v-for="evidence in stage.evidence" :key="evidence.scope" class="flex items-center justify-between gap-2">
                    <span :class="evidence.count ? 'text-gray-700' : 'text-gray-400'">
                        <FontAwesomeIcon icon="fal fa-paperclip" fixed-width aria-hidden="true" />
                        {{ evidence.label }}<template v-if="evidence.count"> · {{ evidence.count }}</template>
                    </span>
                    <label v-if="uploadRoute" class="cursor-pointer text-gray-500 hover:text-gray-800" v-tooltip="ctrans('Upload :document', { document: evidence.label })">
                        <FontAwesomeIcon :icon="uploading === evidence.scope ? 'fal fa-spinner-third' : 'fal fa-upload'" :spin="uploading === evidence.scope" fixed-width aria-hidden="true" />
                        <input type="file" multiple accept="image/*,.pdf" class="hidden" :disabled="!!uploading" @change="(event) => upload(evidence, event)" />
                    </label>
                </div>
            </div>

            <div v-if="stage.marks.length" class="mt-2 flex flex-wrap gap-1">
                <Button
                    v-for="mark in stage.marks"
                    :key="mark.field"
                    type="tertiary"
                    size="xs"
                    :label="mark.label"
                    @click="openMark(mark)"
                >
                    <template #icon>
                        <FontAwesomeIcon :icon="mark.date ? 'fal fa-pencil' : 'fal fa-check'" class="text-gray-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </div>
    </div>

    <Dialog
        :visible="!!editing"
        modal
        :header="editing?.label"
        :style="{ width: '28rem' }"
        @update:visible="(visible: boolean) => !visible && (editing = null)"
    >
        <div class="space-y-4 text-sm">
            <label class="block space-y-1">
                <span class="block text-xs text-gray-500">{{ ctrans("Date") }}</span>
                <DatePicker v-model="date" dateFormat="yy-mm-dd" :maxDate="new Date()" showIcon showButtonBar class="w-full" />
                <span v-if="errors.date" class="block text-xs text-red-600">{{ errors.date }}</span>
            </label>

            <p v-if="editing?.skipped.length" class="text-xs text-amber-700">
                {{ ctrans("Not recorded yet: :stages", { stages: editing.skipped.join(", ") }) }}
            </p>

            <label v-if="needsReason" class="block space-y-1">
                <span class="block text-xs text-gray-500">{{ ctrans("Reason (kept in the order's history)") }}</span>
                <Textarea v-model="reason" rows="3" autoResize class="w-full" />
                <span v-if="errors.reason" class="block text-xs text-red-600">{{ errors.reason }}</span>
            </label>
            <span v-if="errors.field" class="block text-xs text-red-600">{{ errors.field }}</span>
        </div>

        <template #footer>
            <Button type="tertiary" :label="ctrans('Cancel')" @click="editing = null">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                </template>
            </Button>
            <Button type="tertiary" :label="ctrans('Save')" :loading="saving" :disabled="needsReason && !reason.trim()" @click="save">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </template>
    </Dialog>
    </div>
</template>
