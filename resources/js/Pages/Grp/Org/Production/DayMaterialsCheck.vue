<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Oct 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { computed, reactive, ref } from "vue"
import DatePicker from "primevue/datepicker"
import InputNumber from "primevue/inputnumber"
import Checkbox from "primevue/checkbox"
import Select from "primevue/select"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"
import { useLocaleStore } from "@/Stores/locale"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faClipboardCheck, faCheck, faExclamationTriangle, faPaperPlane, faWarehouse, faCalendarDay } from "@fal"

library.add(faClipboardCheck, faCheck, faExclamationTriangle, faPaperPlane, faWarehouse, faCalendarDay)

type Line = {
    id: number
    code: string
    description: string | null
    unit: string | null
    org_stock_code: string | null
    can_be_requested: boolean
    needed_units: number
    at_factory_units: number
    requested_units: number
    suggested_missing: number
    warehouse_units: number
    warehouse_short: boolean
    box_units: number
    job_orders: string
}

type RequestItem = {
    id: number
    code: string
    boxes: number
    box_units: number
    skos_to_bring: number
    delivered_skos: number
    state: "pending" | "delivered" | "short"
    state_label: string
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    data: { data: Line[] }
    production_id: number
    date: string
    today: string
    factory_location: string | null
    can_check: boolean
    can_manage: boolean
    checker_employee_id: number | null
    checker: string | null
    operators: { id: number, name: string }[]
    jobs_without_materials: string[]
    requests: { id: number, state: string, state_label: string, submitted_at: string, submitted_by: string | null, items: RequestItem[] }[]
}>()

const locale = useLocaleStore()

const toIsoDate = (date: Date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const checkDate = computed<Date | null>({
    get: () => new Date(`${props.date}T00:00:00`),
    set: (value) => {
        if (!value) return
        router.get(route(route().current() as string, { ...route().params, date: toIsoDate(value) }), {}, { preserveScroll: true })
    },
})

const answers = reactive<Record<number, { enough: boolean, missing: number | null }>>({})
const answerFor = (line: Line) => answers[line.id] ??= { enough: false, missing: null }

function setEnough(line: Line, enough: boolean) {
    answerFor(line).enough = enough
    if (enough) answerFor(line).missing = null
}

function setMissing(line: Line, missing: number | null) {
    answerFor(line).missing = missing
    if (missing) answerFor(line).enough = false
}

const answeredCount = computed(() => Object.values(answers).filter(answer => answer.enough || (answer.missing ?? 0) > 0).length)
const missingLines = computed(() => Object.entries(answers)
    .filter(([, answer]) => !answer.enough && (answer.missing ?? 0) > 0)
    .map(([rawMaterialId, answer]) => ({ raw_material_id: Number(rawMaterialId), missing_units: answer.missing })))

const isSubmitting = ref(false)

function submit() {
    if (!answeredCount.value) return
    router.post(route("grp.models.production.materials_check.submit", { production: props.production_id }), {
        date: props.date,
        lines: missingLines.value,
    }, {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
        onSuccess: () => Object.keys(answers).forEach(key => delete answers[Number(key)]),
    })
}

function assignChecker(employeeId: number | null) {
    router.patch(route("grp.models.production.materials_check.checker", { production: props.production_id }), {
        date: props.date,
        employee_id: employeeId ?? null,
    }, { preserveScroll: true })
}

const boxesLabel = (units: number, boxUnits: number) => {
    if (boxUnits <= 1) return null
    const boxes = Math.ceil(Math.round(units / boxUnits * 1e6) / 1e6)
    return ctransChoice(":count box (:units)|:count boxes (:units)", boxes, { units: locale.number(boxes * boxUnits) })
}

const dayPlanRoute = computed(() => route("grp.org.productions.show.operations.day_plan", { ...route().params, date: props.date }))
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="mx-4 mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 rounded border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700">
        <DatePicker v-model="checkDate" dateFormat="D d M yy" :manualInput="false" showIcon iconDisplay="input" :pt="{ pcInputText: { root: { class: '!w-40 !py-1 !text-xs' } } }" :aria-label="ctrans('Day')" />
        <Link :href="dayPlanRoute" class="font-medium text-gray-700 hover:underline">
            <FontAwesomeIcon icon="fal fa-calendar-day" fixed-width aria-hidden="true" />
            {{ ctrans("Day plan") }}
        </Link>
        <span v-if="factory_location">
            <FontAwesomeIcon icon="fal fa-warehouse" class="text-gray-400" fixed-width aria-hidden="true" />
            {{ ctrans("Brought to :location", { location: factory_location }) }}
        </span>
        <span v-else class="text-amber-700">
            <FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
            {{ ctrans("This production has no factory location yet") }}
        </span>
        <div v-if="can_manage" class="flex items-center gap-2">
            <span class="text-gray-500">{{ ctrans("Checked by") }}:</span>
            <Select
                :modelValue="checker_employee_id"
                :options="operators"
                optionLabel="name"
                optionValue="id"
                filter
                showClear
                :placeholder="ctrans('Manager or supervisor')"
                class="w-52"
                :pt="{ label: { class: '!py-0.5 !text-xs' } }"
                :aria-label="ctrans('Who does the check')"
                @update:modelValue="assignChecker" />
        </div>
        <span v-else-if="checker" class="text-gray-500">{{ ctrans("Checked by :name", { name: checker }) }}</span>
    </div>

    <div v-if="jobs_without_materials.length" class="mx-4 mt-2 rounded border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">
        <FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
        {{ ctransChoice(":count job has no materials in its recipe, so nothing is counted for it|:count jobs have no materials in their recipe, so nothing is counted for them", jobs_without_materials.length) }}:
        {{ jobs_without_materials.join(", ") }}
    </div>

    <div v-for="request in requests" :key="request.id" class="mx-4 mt-2 rounded border border-gray-200 bg-white px-4 py-2 text-sm">
        <div class="flex flex-wrap items-center gap-x-3 text-gray-700">
            <FontAwesomeIcon icon="fal fa-paper-plane" class="text-gray-400" fixed-width aria-hidden="true" />
            <span class="font-medium">{{ request.items.length ? request.state_label : ctrans("Checked") }}</span>
            <span class="text-gray-500">{{ useFormatTime(request.submitted_at, { formatTime: "hm" }) }}<template v-if="request.submitted_by"> · {{ request.submitted_by }}</template></span>
            <span v-if="!request.items.length" class="text-gray-500">{{ ctrans("Nothing missing") }}</span>
        </div>
        <div v-if="request.items.length" class="mt-1 flex flex-wrap gap-1.5">
            <span v-for="item in request.items" :key="item.id" class="rounded border px-1.5 py-0.5 text-xs tabular-nums"
                :class="item.state === 'delivered' ? 'border-green-200 bg-green-50 text-green-800' : item.state === 'short' ? 'border-red-200 bg-red-50 text-red-700' : 'border-gray-200 text-gray-700'">
                <span class="font-medium">{{ item.code }}</span>
                {{ item.box_units > 1 ? ctransChoice(":count box|:count boxes", item.boxes) : locale.number(item.skos_to_bring) }}
                · {{ item.state_label }}<template v-if="item.state === 'short'"> ({{ locale.number(item.delivered_skos) }}/{{ locale.number(item.skos_to_bring) }})</template>
            </span>
        </div>
    </div>

    <div v-if="can_check" class="mx-4 mt-4 flex items-center justify-end gap-3 text-sm">
        <span class="text-gray-500">{{ ctrans(":count checked", { count: String(answeredCount) }) }}</span>
        <Button type="tertiary" :label="ctrans('Send to warehouse')" :loading="isSubmitting" :disabled="!answeredCount" @click="submit">
            <template #icon>
                <FontAwesomeIcon icon="fal fa-paper-plane" class="text-green-600" fixed-width aria-hidden="true" />
            </template>
        </Button>
    </div>

    <Table :resource="data" class="mt-4">
        <template #cell(code)="{ item }">
            <div class="font-medium">{{ item.code }}</div>
            <div class="text-xs text-gray-500">{{ item.description }}</div>
            <div v-if="item.org_stock_code && item.org_stock_code !== item.code" class="text-xs text-gray-400">{{ ctrans("SKO") }} {{ item.org_stock_code }}</div>
        </template>

        <template #cell(job_orders)="{ item }">
            <span class="text-xs text-gray-700">{{ item.job_orders }}</span>
        </template>

        <template #cell(needed_units)="{ item }">
            <span class="font-semibold tabular-nums">{{ locale.number(item.needed_units) }}</span>
            <span v-if="item.unit && item.unit !== 'unit'" class="text-xs text-gray-500"> {{ item.unit }}</span>
        </template>

        <template #cell(at_factory_units)="{ item }">
            <span class="tabular-nums">{{ locale.number(item.at_factory_units) }}</span>
            <div v-if="item.requested_units > 0" class="text-xs tabular-nums text-gray-500">{{ ctrans(":units already asked", { units: locale.number(item.requested_units) }) }}</div>
        </template>

        <template #cell(check)="{ item }">
            <div v-if="!item.can_be_requested" class="text-xs text-gray-400">{{ ctrans("Not kept in the warehouse") }}</div>
            <div v-else class="flex flex-wrap items-center gap-3 text-xs">
                <label class="flex items-center gap-1.5" :class="{ 'opacity-50': !can_check }">
                    <Checkbox :modelValue="answerFor(item).enough" binary :disabled="!can_check" @update:modelValue="(enough: boolean) => setEnough(item, enough)" />
                    {{ ctrans("Have enough") }}
                </label>
                <span class="text-gray-400">{{ ctrans("or missing") }}</span>
                <InputNumber
                    :modelValue="answerFor(item).missing"
                    :min="0"
                    :maxFractionDigits="3"
                    :disabled="!can_check"
                    :placeholder="item.suggested_missing ? locale.number(item.suggested_missing) : ''"
                    :pt="{ pcInputText: { root: { class: '!w-24 !py-0.5 !text-xs !text-right' } } }"
                    :aria-label="ctrans('Missing :code', { code: item.code })"
                    @update:modelValue="(missing: number | null) => setMissing(item, missing)" />
                <span v-if="answerFor(item).missing && boxesLabel(answerFor(item).missing ?? 0, item.box_units)" class="tabular-nums text-gray-700">
                    {{ ctrans("bring :boxes", { boxes: boxesLabel(answerFor(item).missing ?? 0, item.box_units) ?? "" }) }}
                </span>
                <span v-if="item.warehouse_short" class="text-red-600" v-tooltip="ctrans('Warehouse has :units', { units: locale.number(item.warehouse_units) })">
                    <FontAwesomeIcon icon="fal fa-exclamation-triangle" fixed-width aria-hidden="true" />
                    {{ ctrans("Warehouse short") }}
                </span>
            </div>
        </template>
    </Table>
</template>
