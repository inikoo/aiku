<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 09 Aug 2026 15:00:00 Central European Summer Time, Mijas, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"
import DatePicker from "primevue/datepicker"
import Select from "primevue/select"
import Checkbox from "primevue/checkbox"
import InputText from "primevue/inputtext"
import InputNumber from "primevue/inputnumber"
import Dialog from "primevue/dialog"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans, ctransChoice } from "@/Composables/useTrans"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { capitalize } from "@/Composables/capitalize"
import { useFormatTime } from "@/Composables/useFormatTime"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faUserHardHat, faChevronDown, faChevronUp, faCheck, faTimes, faFilter, faClock, faExclamationTriangle } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { PageHeadingTypes } from "@/types/PageHeading"

library.add(faUserHardHat, faChevronDown, faChevronUp, faCheck, faTimes, faFilter, faClock, faExclamationTriangle)

interface TimeEditFields {
    time_edit: null | {
        reason: string | null
        note: string | null
        edited_by: string | null
        edited_at: string
        original_started_at: string | null
        original_ended_at: string | null
    }
    left_open_after_shift_at: string | null
    times_route: null | { name: string, parameters: object }
}

interface ArtisanSession extends TimeEditFields {
    id: number
    state: string
    task_name: string
    artefact_code: string
    job_order_reference: string
    started_at: string
    ended_at: string
    break_minutes: number
    quantity_made: number
    pack_size: number | null
    correct_route: null | { name: string, parameters: object }
    other_steps: { id: number, task_name: string, unit: string | null, pack_size: number | null, quantity_left: number }[]
    quantity_rejected: number
    earned: number
    units_per_hour: number | null
    standard_rate: number | null
    is_under_target: boolean
    is_hourly: boolean
    under_target_review: null | { reason: string, note: string | null, reviewed_by: string | null, reviewed_at: string }
    review_route: null | { name: string, parameters: object }
    void_route: null | { name: string, parameters: object }
}

interface ArtisanJobStep {
    manufacture_task_id: number
    task_name: string
    hours: number
    quantity_made: number
    quantity_rejected: number
    earned: number
    sessions: ArtisanSession[]
}

interface NonProductiveGroup {
    key: string
    label: string | null
    hours: Record<string, number>
}

interface NonProductiveSummary {
    hours: number
    pay: number | null
    activities: Record<string, number>
    days: NonProductiveGroup[]
    weeks: NonProductiveGroup[]
    job_orders: NonProductiveGroup[]
    sessions: (TimeEditFields & {
        id: number
        state: string
        activity: string
        job_order_reference: string | null
        started_at: string
        ended_at: string
        break_minutes: number
        pay: number | null
        void_route: null | { name: string, parameters: object }
    })[]
}

interface ArtisanJob {
    job_order_item_id: number
    job_order_reference: string
    artefact_code: string
    hours: number
    quantity_made: number
    quantity_rejected: number
    earned: number
    steps: ArtisanJobStep[]
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    period: { from: string, to: string }
    manufacture_task_id: number | null
    manufacture_tasks: { id: number, name: string }[]
    under_target: boolean
    time_edited: boolean
    time_edit_reasons: { value: string, label: string }[]
    left_open: { id: number, worker: string, task_name: string, job_order_reference: string | null, started_at: string, left_open_after_shift_at: string, paused_at: string | null, ended_at: string | null, status: 'paused' | 'running' | 'closed' }[]
    artisan_pay_type: string | null
    artisan_pay_types: Record<string, string>
    under_target_reasons: Record<string, string>
    non_productive_activities: { value: string, label: string }[]
    artisans: {
        user_id: number
        worker: string
        artisan_pay_type: string | null
        pay_type_route: null | { name: string, parameters: object }
        number_sessions: number
        hours_worked: number
        quantity_made: number
        quantity_rejected: number
        earned: number
        under_target_open: number
        non_productive: NonProductiveSummary
        jobs: ArtisanJob[]
    }[]
}>()

const from = ref(props.period.from)
const to = ref(props.period.to)

const fromIsoDate = (iso: string): Date => {
    const [year, month, day] = iso.split("-").map(Number)
    return new Date(year, month - 1, day)
}
const toLocalIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
const dateModel = (target: typeof from) =>
    computed<Date | null>({
        get: () => (target.value ? fromIsoDate(target.value) : null),
        set: (value) => {
            if (value) target.value = toLocalIsoDate(value)
        },
    })
const fromDate = dateModel(from)
const toDate = dateModel(to)
const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const stepOptions = computed(() => [{ id: null, name: ctrans('All steps') }, ...props.manufacture_tasks])
const reasonOptions = computed(() => Object.entries(props.under_target_reasons).map(([value, label]) => ({ value, label })))
const manufactureTaskId = ref<number | null>(props.manufacture_task_id)
const payTypeFilter = ref<string | null>(props.artisan_pay_type)
const payTypeFilterOptions = computed(() => [{ value: null, label: ctrans('All pay types') }, ...Object.entries(props.artisan_pay_types).map(([value, label]) => ({ value, label }))])
const payTypeOptions = computed(() => Object.entries(props.artisan_pay_types).map(([value, label]) => ({ value, label })))
const underTargetOnly = ref(props.under_target)
const timeEditedOnly = ref(props.time_edited)
const reviewing = ref<number | null>(null)
const reviewReason = ref<string | null>(null)
const reviewNote = ref('')
const expanded = ref<Set<number>>(new Set())
const processing = ref(false)

function applyPeriod() {
    router.get(
        window.location.pathname,
        { from: from.value, to: to.value, manufacture_task_id: manufactureTaskId.value || undefined, under_target: underTargetOnly.value ? 1 : undefined, time_edited: timeEditedOnly.value ? 1 : undefined, artisan_pay_type: payTypeFilter.value || undefined },
        { preserveState: false }
    )
}

function setPayType(artisan: { pay_type_route: null | { name: string, parameters: object } }, payType: string) {
    if (!artisan.pay_type_route) return
    processing.value = true
    router.patch(
        route(artisan.pay_type_route.name, artisan.pay_type_route.parameters),
        { artisan_pay_type: payType },
        { preserveScroll: true, onFinish: () => processing.value = false }
    )
}

function toggle(userId: number) {
    if (expanded.value.has(userId)) {
        expanded.value.delete(userId)
    } else {
        expanded.value.add(userId)
    }
    expanded.value = new Set(expanded.value)
}

function hoursLabel(hours: number) {
    const h = Math.floor(hours)
    const m = Math.round((hours - h) * 60)
    return h ? `${h}h ${m}m` : `${m}m`
}

function activitySummary(summary: NonProductiveSummary) {
    return props.non_productive_activities
        .filter(activity => summary.activities[activity.value])
        .map(activity => `${activity.label} ${hoursLabel(summary.activities[activity.value])}`)
        .join(' · ')
}

function voidSession(session: { void_route: null | { name: string, parameters: object }, quantity_made?: number }) {
    if (!session.void_route) return
    if (!window.confirm(ctrans('Void this entry?') + (session.quantity_made !== undefined ? ` ${session.quantity_made}` : ''))) return
    processing.value = true
    router.patch(
        route(session.void_route.name, session.void_route.parameters),
        {},
        { preserveScroll: true, onFinish: () => processing.value = false }
    )
}

const correcting = ref<number | null>(null)
const correctedQuantity = ref<number | null>(null)
const correctError = ref<string | null>(null)
const correctedStepId = ref<number | null>(null)

function movingTo(session: ArtisanSession) {
    return session.other_steps.find((step) => step.id === correctedStepId.value) ?? null
}

function correctedPackSize(session: ArtisanSession) {
    const step = movingTo(session)
    return step ? step.pack_size : session.pack_size
}

function canSaveCorrect(session: ArtisanSession) {
    if (correctedQuantity.value === null) return false
    return movingTo(session) !== null || correctedQuantity.value < session.quantity_made
}

function openCorrect(session: ArtisanSession) {
    correcting.value = session.id
    correctedQuantity.value = null
    correctedStepId.value = null
    correctError.value = null
}

function saveCorrect(session: ArtisanSession) {
    if (!session.correct_route || correctedQuantity.value === null) return
    processing.value = true
    router.patch(
        route(session.correct_route.name, session.correct_route.parameters),
        { quantity_made: correctedQuantity.value, job_order_item_task_id: correctedStepId.value },
        {
            preserveScroll: true,
            onSuccess: () => correcting.value = null,
            onError: (errors) => correctError.value = errors.quantity_made ?? errors.job_order_item_task_id ?? null,
            onFinish: () => processing.value = false,
        }
    )
}

function openReview(session: ArtisanSession) {
    reviewing.value = session.id
    reviewReason.value = null
    reviewNote.value = ''
}

function saveReview(session: ArtisanSession) {
    if (!session.review_route || !reviewReason.value) return
    processing.value = true
    router.patch(
        route(session.review_route.name, session.review_route.parameters),
        { under_target_reason: reviewReason.value, under_target_note: reviewNote.value || null },
        {
            preserveScroll: true,
            onSuccess: () => reviewing.value = null,
            onFinish: () => processing.value = false,
        }
    )
}

interface EditableSession extends TimeEditFields {
    id: number
    started_at: string
    ended_at: string
    task_name?: string
    activity?: string
}

const editingTimes = ref<EditableSession | null>(null)
const editWorker = ref("")
const editStart = ref<Date | null>(null)
const editEnd = ref<Date | null>(null)
const editReason = ref<string | null>(null)
const editNote = ref("")
const editErrors = ref<Record<string, string>>({})
const outsideShiftConfirmed = ref(false)
const impossibleRateConfirmed = ref(false)
const warningConfirmed = computed(() => outsideShiftConfirmed.value || impossibleRateConfirmed.value)
watch([editStart, editEnd], () => {
    outsideShiftConfirmed.value = false
    impossibleRateConfirmed.value = false
})
const canSaveTimes = computed(() => !!editStart.value && !!editEnd.value && !!editReason.value && (editReason.value !== "other" || editNote.value.trim() !== "") && editEnd.value > editStart.value)

function openTimes(session: EditableSession, worker: string) {
    editingTimes.value = session
    editWorker.value = worker
    editStart.value = new Date(session.started_at)
    editEnd.value = new Date(session.ended_at)
    editReason.value = null
    editNote.value = ""
    editErrors.value = {}
    outsideShiftConfirmed.value = false
    impossibleRateConfirmed.value = false
}

function saveTimes() {
    const session = editingTimes.value
    if (!session?.times_route || !canSaveTimes.value) return
    processing.value = true
    router.patch(
        route(session.times_route.name, session.times_route.parameters),
        {
            started_at: editStart.value?.toISOString(),
            ended_at: editEnd.value?.toISOString(),
            reason: editReason.value,
            note: editNote.value || null,
            outside_shift_confirmed: outsideShiftConfirmed.value,
            impossible_rate_confirmed: impossibleRateConfirmed.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => editingTimes.value = null,
            onError: (errors) => {
                editErrors.value = errors
                outsideShiftConfirmed.value ||= !!errors.outside_shift
                impossibleRateConfirmed.value ||= !!errors.impossible_rate
            },
            onFinish: () => processing.value = false,
        }
    )
}

function clockTime(iso: string | null) {
    return iso ? new Date(iso).toLocaleString([], { weekday: "short", day: "numeric", month: "short", hour: "2-digit", minute: "2-digit" }) : "—"
}

function sessionDuration(session: { started_at: string, ended_at: string, break_minutes: number }) {
    const seconds = Math.max(0, (new Date(session.ended_at).getTime() - new Date(session.started_at).getTime()) / 1000 - session.break_minutes * 60)
    const h = Math.floor(seconds / 3600)
    const m = Math.round((seconds % 3600) / 60)
    return h ? `${h}h ${m}m` : `${m}m`
}
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />

    <div class="px-4 py-4 max-w-4xl">
        <div class="mb-6 flex items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ ctrans('From') }}</label>
                <DatePicker v-model="fromDate" :maxDate="toDate ?? undefined" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :aria-label="ctrans('From')" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ ctrans('To') }}</label>
                <DatePicker v-model="toDate" :minDate="fromDate ?? undefined" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :aria-label="ctrans('To')" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ ctrans('Step') }}</label>
                <Select v-model="manufactureTaskId" :options="stepOptions" optionLabel="name" optionValue="id" filter :class="fieldFocusClass" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ ctrans('Pay type') }}</label>
                <Select v-model="payTypeFilter" :options="payTypeFilterOptions" optionLabel="label" optionValue="value" :class="fieldFocusClass" />
            </div>
            <label class="flex items-center gap-2 pb-2 text-sm text-gray-600">
                <Checkbox v-model="underTargetOnly" binary />
                {{ ctrans('Under target only') }}
            </label>
            <label class="flex items-center gap-2 pb-2 text-sm text-gray-600">
                <Checkbox v-model="timeEditedOnly" binary />
                {{ ctrans('Edited times only') }}
            </label>
            <Button type="tertiary" :label="ctrans('Apply')" @click="applyPeriod">
                <template #icon><FontAwesomeIcon :icon="['fal', 'filter']" fixed-width aria-hidden="true" /></template>
            </Button>
        </div>

        <div v-if="left_open.length" class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <div class="flex items-center gap-2 font-semibold">
                <FontAwesomeIcon :icon="['fal', 'exclamation-triangle']" fixed-width aria-hidden="true" />
                {{ ctrans('Paused or left running at the end of the shift, not checked yet') }}
            </div>
            <div class="mt-1 text-xs text-amber-800">{{ ctrans('When the operator clocks back in the tablet asks if they finished before the end of the shift. If it is not answered, or the job ran on without a clock-out, set the real times here with Edit times.') }}</div>
            <div v-for="open in left_open" :key="open.id" class="mt-1 flex flex-wrap gap-x-3 tabular-nums">
                <span class="font-medium">{{ open.worker }}</span>
                <span>{{ open.task_name }}<template v-if="open.job_order_reference"> · {{ open.job_order_reference }}</template></span>
                <span>{{ ctrans('started :time', { time: clockTime(open.started_at) }) }}</span>
                <span>{{ ctrans('shift ended :time', { time: clockTime(open.left_open_after_shift_at) }) }}</span>
                <span v-if="open.status === 'paused'">{{ ctrans('paused since :time', { time: clockTime(open.paused_at) }) }}</span>
                <span v-else-if="open.status === 'closed'">{{ ctrans('closed :time', { time: clockTime(open.ended_at) }) }}</span>
                <span v-else>{{ ctrans('still running') }}</span>
            </div>
        </div>

        <div v-if="!artisans.length" class="text-gray-400 text-center py-16 border border-dashed border-gray-200 rounded-lg">
            {{ ctrans('No finished work in this period') }}
        </div>

        <div v-for="artisan in artisans" :key="artisan.user_id" class="mb-3 rounded-xl border border-gray-200 bg-white">
            <button type="button" class="w-full px-4 py-3 flex items-center justify-between gap-4 text-left" @click="toggle(artisan.user_id)">
                <div class="flex items-center gap-2 font-semibold">
                    {{ artisan.worker }}
                    <span v-if="artisan.artisan_pay_type === 'hourly'" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ artisan_pay_types.hourly }}</span>
                </div>
                <div class="flex items-center gap-6 text-sm text-gray-600 tabular-nums">
                    <span>{{ artisan.number_sessions }} {{ ctrans('tasks') }}</span>
                    <span>{{ artisan.hours_worked }} h</span>
                    <span>{{ ctransChoice(':count unit|:count units', artisan.quantity_made) }}</span>
                    <span v-if="artisan.quantity_rejected" class="text-red-500">{{ artisan.quantity_rejected }} {{ ctrans('rejected') }}</span>
                    <span v-if="artisan.non_productive.hours" class="text-gray-500">{{ activitySummary(artisan.non_productive) }}</span>
                    <span v-if="artisan.under_target_open" class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800">
                        {{ artisan.under_target_open }} {{ ctrans('under target') }}
                    </span>
                    <span class="font-semibold text-gray-800">{{ artisan.earned.toFixed(2) }}</span>
                    <FontAwesomeIcon :icon="['fal', expanded.has(artisan.user_id) ? 'chevron-up' : 'chevron-down']" fixed-width class="text-gray-400" />
                </div>
            </button>

            <div v-if="expanded.has(artisan.user_id)" class="border-t border-gray-100 px-4 py-2">
                <div v-if="artisan.pay_type_route" class="py-2 flex items-center gap-3 border-b border-gray-100 text-sm text-gray-600">
                    <span>{{ ctrans('Pay type') }}</span>
                    <SegmentedToggle :modelValue="artisan.artisan_pay_type ?? 'piece_rate'" :options="payTypeOptions" :aria-label="ctrans('Pay type')" @update:modelValue="(payType: string) => setPayType(artisan, payType)" />
                    <span class="text-xs text-gray-400">{{ ctrans('Hourly: no tiers, bonus or under-target flags. Applies to this week\'s sessions too.') }}</span>
                </div>
                <div v-if="artisan.non_productive.sessions.length" class="py-2 border-b border-gray-100">
                    <div class="flex items-center justify-between gap-3 text-sm font-medium text-gray-700">
                        <div>{{ ctrans('Preparation and cleaning') }} <span class="font-normal text-gray-400">· {{ ctrans('paid at base rate, not in units per hour') }}</span></div>
                        <div class="flex items-center gap-4 shrink-0 tabular-nums">
                            <span class="text-gray-400">{{ hoursLabel(artisan.non_productive.hours) }}</span>
                            <span class="w-16 text-right">{{ artisan.non_productive.pay === null ? '—' : artisan.non_productive.pay.toFixed(2) }}</span>
                        </div>
                    </div>

                    <table class="mt-2 w-full text-xs tabular-nums">
                        <thead>
                            <tr class="text-left text-gray-400">
                                <th class="py-1 font-medium">{{ ctrans('Day') }}</th>
                                <th v-for="activity in non_productive_activities" :key="activity.value" class="py-1 font-medium text-right">{{ activity.label }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="day in artisan.non_productive.days" :key="day.key" class="border-t border-gray-50 text-gray-600">
                                <td class="py-1">{{ useFormatTime(day.key) }}</td>
                                <td v-for="activity in non_productive_activities" :key="activity.value" class="py-1 text-right">{{ day.hours[activity.value] ? hoursLabel(day.hours[activity.value]) : '—' }}</td>
                            </tr>
                            <tr v-for="week in artisan.non_productive.weeks" :key="'w' + week.key" class="border-t border-gray-100 font-medium text-gray-700">
                                <td class="py-1">{{ ctrans('Week of :date', { date: useFormatTime(week.key) }) }}</td>
                                <td v-for="activity in non_productive_activities" :key="activity.value" class="py-1 text-right">{{ hoursLabel(week.hours[activity.value]) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="mt-2 w-full text-xs tabular-nums">
                        <thead>
                            <tr class="text-left text-gray-400">
                                <th class="py-1 font-medium">{{ ctrans('Job order') }}</th>
                                <th v-for="activity in non_productive_activities" :key="activity.value" class="py-1 font-medium text-right">{{ activity.label }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="jobOrder in artisan.non_productive.job_orders" :key="jobOrder.key" class="border-t border-gray-50 text-gray-600">
                                <td class="py-1">{{ jobOrder.label ?? ctrans('General, no job order') }}</td>
                                <td v-for="activity in non_productive_activities" :key="activity.value" class="py-1 text-right">{{ jobOrder.hours[activity.value] ? hoursLabel(jobOrder.hours[activity.value]) : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <template v-for="session in artisan.non_productive.sessions" :key="session.id">
                    <div class="py-1 flex items-center justify-between gap-3 text-sm"
                        :class="session.state == 'voided' ? 'opacity-40 line-through' : ''">
                        <span class="flex items-center gap-2 text-gray-400">
                            {{ useFormatTime(session.ended_at) }}
                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ session.activity }}</span>
                            <span class="text-xs">{{ session.job_order_reference ?? ctrans('General') }}</span>
                            <span v-if="session.time_edit" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ ctrans('Times edited') }}</span>
                        </span>
                        <div class="flex items-center gap-4 shrink-0 tabular-nums text-gray-700">
                            <span class="text-gray-400">
                                {{ sessionDuration(session) }}
                                <span v-if="session.break_minutes">({{ session.break_minutes }}m {{ ctrans('break') }})</span>
                            </span>
                            <span class="w-16 text-right">{{ session.pay === null ? '—' : session.pay.toFixed(2) }}</span>
                            <button v-if="session.times_route" type="button"
                                class="text-xs font-medium text-gray-700 hover:underline"
                                @click="openTimes(session, artisan.worker)">
                                {{ ctrans('Edit times') }}
                            </button>
                            <button v-if="session.void_route" type="button"
                                class="text-xs text-red-600 hover:underline disabled:opacity-40"
                                :disabled="processing" @click="voidSession(session)">
                                {{ ctrans('Void') }}
                            </button>
                        </div>
                    </div>
                        <div v-if="session.time_edit" class="pb-1 pl-2 text-xs text-gray-500">
                            {{ ctrans('Times edited (:reason)', { reason: session.time_edit.reason ?? '' }) }}:
                            {{ ctrans('first recorded :from to :to', { from: clockTime(session.time_edit.original_started_at), to: clockTime(session.time_edit.original_ended_at) }) }}
                            <template v-if="session.time_edit.note"> · {{ session.time_edit.note }}</template>
                            — {{ session.time_edit.edited_by ?? ctrans('automatic') }}, {{ clockTime(session.time_edit.edited_at) }}
                        </div>
                    </template>
                </div>

                <div v-for="job in artisan.jobs" :key="job.job_order_item_id" class="py-2 border-b border-gray-100 last:border-0">
                    <div class="flex items-center justify-between gap-3 text-sm font-medium text-gray-700">
                        <div class="truncate">{{ job.artefact_code }} · {{ job.job_order_reference }}</div>
                        <div class="flex items-center gap-4 shrink-0 tabular-nums">
                            <span class="text-gray-400">{{ job.hours }} h</span>
                            <span>{{ job.quantity_made }}</span>
                            <span v-if="job.quantity_rejected" class="text-red-500">-{{ job.quantity_rejected }}</span>
                            <span class="w-16 text-right">{{ job.earned.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div v-for="step in job.steps" :key="step.manufacture_task_id" class="mt-1 pl-4 border-l-2 border-gray-100">
                        <div class="flex items-center justify-between gap-3 text-xs text-gray-500">
                            <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium text-gray-600">{{ step.task_name }}</span>
                            <div class="flex items-center gap-4 shrink-0 tabular-nums">
                                <span>{{ step.hours }} h</span>
                                <span>{{ step.quantity_made }}</span>
                                <span v-if="step.quantity_rejected" class="text-red-500">-{{ step.quantity_rejected }}</span>
                                <span class="w-16 text-right">{{ step.earned.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div v-for="session in step.sessions" :key="session.id">
                        <div
                            class="py-1 flex items-center justify-between gap-3 text-sm"
                            :class="session.state == 'voided' ? 'opacity-40 line-through' : ''">
                            <span class="flex items-center gap-2 text-gray-400">
                                {{ useFormatTime(session.ended_at) }}
                                <span v-if="session.is_hourly" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ artisan_pay_types.hourly }}</span>
                                <span v-if="session.time_edit" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">{{ ctrans('Times edited') }}</span>
                                <span v-else-if="session.left_open_after_shift_at" class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800">{{ ctrans('Left running after shift') }}</span>
                                <span v-if="session.is_under_target"
                                    class="rounded px-1.5 py-0.5 text-xs font-medium"
                                    :class="session.under_target_review ? 'bg-gray-100 text-gray-600' : 'bg-amber-100 text-amber-800'">
                                    {{ session.under_target_review ? session.under_target_review.reason : ctrans('Under target / action required') }}
                                </span>
                            </span>
                            <div class="flex items-center gap-4 shrink-0 tabular-nums text-gray-700">
                                <span class="text-gray-400">
                                    {{ sessionDuration(session) }}
                                    <span v-if="session.break_minutes">({{ session.break_minutes }}m {{ ctrans('break') }})</span>
                                </span>
                                <span>{{ session.quantity_made }}<template v-if="session.pack_size"> {{ ctrans('packs of :size', { size: session.pack_size }) }}</template></span>
                                <span v-if="session.quantity_rejected" class="text-red-500">-{{ session.quantity_rejected }}</span>
                                <span v-if="session.standard_rate !== null" :class="session.is_under_target ? 'text-amber-700' : 'text-gray-400'">
                                    {{ session.units_per_hour ?? 0 }}/{{ session.standard_rate }} {{ ctrans('per hr') }}
                                </span>
                                <span class="w-16 text-right">{{ session.earned.toFixed(2) }}</span>
                                <button
                                    v-if="session.review_route && reviewing !== session.id"
                                    type="button"
                                    class="text-xs font-medium text-gray-700 hover:underline"
                                    @click="openReview(session)"
                                >
                                    {{ session.under_target_review ? ctrans('Change reason') : ctrans('Log reason') }}
                                </button>
                                <button
                                    v-if="session.times_route"
                                    type="button"
                                    class="text-xs font-medium text-gray-700 hover:underline"
                                    @click="openTimes(session, artisan.worker)"
                                >
                                    {{ ctrans('Edit times') }}
                                </button>
                                <button
                                    v-if="session.correct_route && correcting !== session.id"
                                    type="button"
                                    class="text-xs font-medium text-gray-700 hover:underline"
                                    @click="openCorrect(session)"
                                >
                                    {{ session.other_steps.length ? ctrans('Correct quantity or step') : ctrans('Correct quantity') }}
                                </button>
                                <button
                                    v-if="session.void_route"
                                    type="button"
                                    class="text-xs text-red-600 hover:underline disabled:opacity-40"
                                    :disabled="processing"
                                    @click="voidSession(session)"
                                >
                                    {{ ctrans('Void') }}
                                </button>
                            </div>
                        </div>
                        <div v-if="session.time_edit" class="pb-1 pl-2 text-xs text-gray-500">
                            {{ ctrans('Times edited (:reason)', { reason: session.time_edit.reason ?? '' }) }}:
                            {{ ctrans('first recorded :from to :to', { from: clockTime(session.time_edit.original_started_at), to: clockTime(session.time_edit.original_ended_at) }) }}
                            <template v-if="session.time_edit.note"> · {{ session.time_edit.note }}</template>
                            — {{ session.time_edit.edited_by ?? ctrans('automatic') }}, {{ clockTime(session.time_edit.edited_at) }}
                        </div>
                        <div v-if="session.under_target_review?.note && reviewing !== session.id" class="pb-1 pl-2 text-xs text-gray-500">
                            {{ session.under_target_review.note }}
                            <span v-if="session.under_target_review.reviewed_by">— {{ session.under_target_review.reviewed_by }}</span>
                        </div>
                        <div v-if="correcting === session.id" class="mb-2 rounded border border-amber-200 bg-amber-50 p-2 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span>{{ ctrans(':code · :step was entered as :quantity', { code: session.artefact_code, step: session.task_name, quantity: session.quantity_made }) }}<template v-if="session.pack_size"> {{ ctrans('packs of :size', { size: session.pack_size }) }}</template>.</span>
                                <template v-if="session.other_steps.length">
                                    <span>{{ ctrans('Step really worked:') }}</span>
                                    <Select v-model="correctedStepId" :options="session.other_steps" optionLabel="task_name" optionValue="id" showClear :placeholder="session.task_name" :class="fieldFocusClass" />
                                </template>
                                <span>{{ correctedPackSize(session) ? ctrans('Whole packs really made:') : ctrans('Really made:') }}</span>
                                <InputNumber v-model="correctedQuantity" :min="0" :max="movingTo(session) ? (movingTo(session)?.unit ? undefined : movingTo(session)?.quantity_left) : session.quantity_made" :maxFractionDigits="correctedPackSize(session) ? 0 : 3" :suffix="movingTo(session)?.unit ? ' ' + movingTo(session)?.unit : undefined" inputClass="w-24" :class="fieldFocusClass" />
                                <span v-if="correctedPackSize(session) && correctedQuantity !== null" class="font-medium text-amber-800">= {{ (correctedQuantity * (correctedPackSize(session) ?? 1)).toLocaleString() }} × {{ session.artefact_code }}</span>
                                <Button type="tertiary" :label="ctrans('Save correction')" :loading="processing" :disabled="!canSaveCorrect(session)" @click="saveCorrect(session)">
                                    <template #icon><FontAwesomeIcon :icon="['fal', 'check']" class="text-green-600" fixed-width aria-hidden="true" /></template>
                                </Button>
                                <Button type="tertiary" :label="ctrans('Cancel')" @click="correcting = null">
                                    <template #icon><FontAwesomeIcon :icon="['fal', 'times']" class="text-red-500" fixed-width aria-hidden="true" /></template>
                                </Button>
                            </div>
                            <div v-if="movingTo(session)" class="mt-1 text-xs text-gray-600">{{ ctrans('The entry moves to :to with what was made there, :from goes back to what is left to do, and the pay for this entry is worked out again as :to.', { to: movingTo(session)?.task_name ?? '', from: session.task_name }) }}</div>
                            <div v-else class="mt-1 text-xs text-gray-600">{{ ctrans('The job goes back to what was really made, any authorised overproduction is reduced with it, and the pay for this entry is worked out again.') }}</div>
                            <div v-if="correctError" class="mt-1 text-red-700">{{ correctError }}</div>
                        </div>
                        <div v-if="reviewing === session.id" class="mb-2 flex flex-wrap items-end gap-2 rounded border border-amber-200 bg-amber-50 p-2 text-sm">
                            <Select v-model="reviewReason" :options="reasonOptions" optionLabel="label" optionValue="value" :placeholder="ctrans('Reason')" :class="fieldFocusClass" />
                            <InputText v-model="reviewNote" maxlength="1000" :placeholder="ctrans('Notes (optional)')" class="min-w-0 flex-1" />
                            <Button type="tertiary" :label="ctrans('Save')" :disabled="!reviewReason || processing" @click="saveReview(session)">
                                <template #icon><FontAwesomeIcon :icon="['fal', 'check']" class="text-green-600" fixed-width aria-hidden="true" /></template>
                            </Button>
                            <button type="button" class="text-xs text-gray-500 hover:underline" @click="reviewing = null">
                                {{ ctrans('Cancel') }}
                            </button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Dialog :visible="!!editingTimes" modal :header="ctrans('Edit times')" :style="{ width: '32rem' }" @update:visible="(visible: boolean) => { if (!visible) editingTimes = null }">
            <div v-if="editingTimes" class="space-y-3 text-sm">
                <div class="text-gray-600">
                    <span class="font-medium text-gray-800">{{ editWorker }}</span> · {{ editingTimes.task_name ?? editingTimes.activity }}
                </div>
                <div class="text-xs text-gray-500">
                    {{ ctrans('Recorded :from to :to', { from: clockTime(editingTimes.time_edit?.original_started_at ?? editingTimes.started_at), to: clockTime(editingTimes.time_edit?.original_ended_at ?? editingTimes.ended_at) }) }}
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ ctrans('Start') }}</label>
                        <DatePicker v-model="editStart" showTime hourFormat="24" dateFormat="d M yy" :maxDate="editEnd ?? undefined" fluid :class="fieldFocusClass" :aria-label="ctrans('Start')" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">{{ ctrans('End') }}</label>
                        <DatePicker v-model="editEnd" showTime hourFormat="24" dateFormat="d M yy" :minDate="editStart ?? undefined" :maxDate="new Date()" fluid :class="fieldFocusClass" :aria-label="ctrans('End')" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ ctrans('Reason') }}</label>
                    <Select v-model="editReason" :options="time_edit_reasons" optionLabel="label" optionValue="value" :placeholder="ctrans('Choose a reason')" fluid :class="fieldFocusClass" />
                </div>
                <div v-if="editReason">
                    <label class="block text-xs text-gray-500 mb-1">{{ editReason === 'other' ? ctrans('Note') : ctrans('Note (optional)') }}</label>
                    <InputText v-model="editNote" maxlength="1000" fluid />
                </div>
                <div class="text-xs text-gray-500">{{ ctrans('Hours, rate per hour and pay are worked out again for the new times. The first times recorded are kept.') }}</div>
                <div v-for="(message, field) in editErrors" :key="field"
                    class="rounded px-2 py-1"
                    :class="['outside_shift', 'impossible_rate'].includes(field) ? 'border border-amber-200 bg-amber-50 text-amber-800' : 'border border-red-200 bg-red-50 text-red-700'">
                    {{ message }}
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="tertiary" :label="ctrans('Cancel')" @click="editingTimes = null">
                        <template #icon><FontAwesomeIcon :icon="['fal', 'times']" class="text-red-500" fixed-width aria-hidden="true" /></template>
                    </Button>
                    <Button type="tertiary" :label="warningConfirmed ? ctrans('Save anyway') : ctrans('Save times')" :loading="processing" :disabled="!canSaveTimes" @click="saveTimes">
                        <template #icon><FontAwesomeIcon :icon="['fal', warningConfirmed ? 'exclamation-triangle' : 'check']" :class="warningConfirmed ? 'text-amber-600' : 'text-green-600'" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>
