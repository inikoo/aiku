<script setup lang="ts">
import { Head, Link, router, useForm } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"
import Dialog from "primevue/dialog"
import DatePicker from "primevue/datepicker"
import Select from "primevue/select"
import InputText from "primevue/inputtext"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import AppointmentActions from "@/Components/CRM/AppointmentActions.vue"
import SegmentedToggle from "@/Components/Utils/SegmentedToggle.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBan, faCalendarAlt, faCalendarDay, faCheck, faChevronLeft, faChevronRight, faClock, faExclamationTriangle, faExternalLink, faTimes, faTrashAlt } from "@fal"

library.add(faBan, faCalendarAlt, faCalendarDay, faCheck, faChevronLeft, faChevronRight, faClock, faExclamationTriangle, faExternalLink, faTimes, faTrashAlt)

type CalendarAppointment = {
    id: number
    time: string
    ends_time: string
    date_label: string
    contact_name: string
    email: string | null
    phone: string | null
    appointment_type_name: string | null
    staff_name: string | null
    state: "requested" | "accepted" | "completed" | "no_show"
    state_label: string
    is_blocked: boolean
    can_accept: boolean
    can_decline: boolean
    can_reschedule: boolean
}

type CalendarBlock = {
    id: number
    is_all_day: boolean
    time: string | null
    period_label: string
    reason: string | null
    appointment_type_name: string | null
    created_by: string | null
}

type CalendarDay = {
    date: string
    day: number
    in_month: boolean
    is_today: boolean
    blocks: CalendarBlock[]
    appointments: CalendarAppointment[]
}

const props = defineProps<{
    pageHead: PageHeadingTypes
    title: string
    calendar: {
        month: string
        label: string
        previous: string
        next: string
        current: string
        timezone: string
        weekdays: string[]
        total: number
        weeks: CalendarDay[][]
    }
    canEdit: boolean
    appointmentTypes: { id: number; name: string }[]
    routes: {
        calendar: routeType
        show: routeType
        store_block: routeType
        delete_block: routeType
    }
}>()

const locale = useLocaleStore()
const loadingMonth = ref("")

const goTo = (month: string) => {
    loadingMonth.value = month
    router.get(route(props.routes.calendar.name, { ...props.routes.calendar.parameters, month }), {}, {
        preserveScroll: true,
        preserveState: true,
        only: ["calendar"],
        onFinish: () => (loadingMonth.value = ""),
    })
}

const appointmentUrl = (id: number) => route(props.routes.show.name, { ...props.routes.show.parameters, appointment: id })

const stateClass = (state: CalendarAppointment["state"]) => ({
    requested: "border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100",
    accepted: "border-green-200 bg-green-50 text-green-800 hover:bg-green-100",
    completed: "border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100",
    no_show: "border-gray-200 bg-white text-gray-400 line-through hover:bg-gray-50",
}[state])

const blockClass = "border-gray-300 bg-gray-100 text-gray-600 hover:bg-gray-200"

const legend = computed(() => [
    { state: "requested" as const, label: ctrans("Requested") },
    { state: "accepted" as const, label: ctrans("Accepted") },
    { state: "completed" as const, label: ctrans("Completed") },
    { state: "no_show" as const, label: ctrans("No show") },
])

const daysWithEntries = computed(() => props.calendar.weeks.flat().filter(day => day.in_month && (day.appointments.length || day.blocks.length)))

const tooltip = (appointment: CalendarAppointment) => [
    `${appointment.time} ${appointment.contact_name}`,
    appointment.appointment_type_name,
    appointment.staff_name,
    appointment.state_label,
    appointment.is_blocked ? ctrans("In blocked time") : null,
].filter(Boolean).join(" · ")

const blockLabel = (block: CalendarBlock) => [
    block.is_all_day ? ctrans("All day") : block.time,
    block.reason ?? ctrans("Blocked"),
].join(" ")

const allAppointments = computed(() => props.calendar.weeks.flat().flatMap(day => day.appointments))

const selectedAppointmentId = ref<number | null>(null)
const selectedAppointment = computed(() => allAppointments.value.find(appointment => appointment.id === selectedAppointmentId.value) ?? null)
const isAppointmentOpen = computed({
    get: () => selectedAppointment.value !== null,
    set: (isOpen: boolean) => {
        if (!isOpen) {
            selectedAppointmentId.value = null
        }
    },
})

const selectedBlock = ref<CalendarBlock | null>(null)
const isBlockDetailOpen = computed({
    get: () => selectedBlock.value !== null,
    set: (isOpen: boolean) => {
        if (!isOpen) {
            selectedBlock.value = null
        }
    },
})
const isRemovingBlock = ref(false)

const removeBlock = () => {
    if (!selectedBlock.value) {
        return
    }
    router.delete(route(props.routes.delete_block.name, { ...props.routes.delete_block.parameters, appointmentBlock: selectedBlock.value.id }), {
        preserveScroll: true,
        onStart: () => (isRemovingBlock.value = true),
        onSuccess: () => (selectedBlock.value = null),
        onFinish: () => (isRemovingBlock.value = false),
    })
}

const fromIsoDate = (iso: string): Date => {
    const [year, month, day] = iso.split("-").map(Number)
    return new Date(year, month - 1, day)
}
const toLocalIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const timeOptions: string[] = []
for (let minutes = 0; minutes < 24 * 60; minutes += 15) {
    timeOptions.push(`${String(Math.floor(minutes / 60)).padStart(2, "0")}:${String(minutes % 60).padStart(2, "0")}`)
}

const blockForm = useForm({
    is_all_day: true,
    date_from: "",
    date_to: "",
    time_from: "09:00",
    time_to: "17:00",
    appointment_type_id: null as number | null,
    reason: "",
})

const isBlockFormOpen = ref(false)

const openBlockForm = (date?: string) => {
    blockForm.reset()
    blockForm.clearErrors()
    const day = date ?? toLocalIsoDate(new Date())
    blockForm.date_from = day
    blockForm.date_to = day
    isBlockFormOpen.value = true
}

const blockMode = computed({
    get: () => (blockForm.is_all_day ? "all_day" : "hours"),
    set: (mode: string) => (blockForm.is_all_day = mode === "all_day"),
})
const blockModeOptions = computed(() => [
    { label: ctrans("Whole days"), value: "all_day", icon: faCalendarDay },
    { label: ctrans("Hours"), value: "hours", icon: faClock },
])

const dateModel = (key: "date_from" | "date_to") =>
    computed<Date | null>({
        get: () => (blockForm[key] ? fromIsoDate(blockForm[key]) : null),
        set: (value) => {
            if (!value) {
                return
            }
            blockForm[key] = toLocalIsoDate(value)
            if (key === "date_from" && blockForm.date_to < blockForm.date_from) {
                blockForm.date_to = blockForm.date_from
            }
        },
    })
const dateFrom = dateModel("date_from")
const dateTo = dateModel("date_to")

const today = new Date()
today.setHours(0, 0, 0, 0)

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent] [&_input:focus]:!border-[--app-accent]"
const datePickerPt = { pcInputText: { root: { class: "!w-40" } } }

const submitBlock = () => {
    blockForm
        .transform(data => ({
            ...data,
            date_to: data.is_all_day ? data.date_to : null,
            appointment_type_id: data.appointment_type_id || null,
            reason: data.reason.trim() || null,
        }))
        .post(route(props.routes.store_block.name, props.routes.store_block.parameters), {
            preserveScroll: true,
            onSuccess: () => (isBlockFormOpen.value = false),
        })
}

watch(() => props.calendar, () => {
    if (selectedAppointmentId.value !== null && !selectedAppointment.value) {
        selectedAppointmentId.value = null
    }
})
</script>

<template>
    <div>
        <Head :title="capitalize(title)" />
        <PageHeading :data="pageHead" />

        <div class="space-y-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <Button type="tertiary" :label="ctrans('Previous')" :loading="loadingMonth === calendar.previous" @click="goTo(calendar.previous)">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-chevron-left" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <Button type="tertiary" :label="ctrans('Today')" :disabled="calendar.month === calendar.current" :loading="loadingMonth === calendar.current" @click="goTo(calendar.current)">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-calendar-day" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <Button type="tertiary" :label="ctrans('Next')" :loading="loadingMonth === calendar.next" @click="goTo(calendar.next)">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-chevron-right" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <Button v-if="canEdit" type="tertiary" :label="ctrans('Block time')" @click="openBlockForm()">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-ban" class="text-gray-500" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                </div>
                <div class="text-right">
                    <h2 class="text-lg font-semibold text-gray-800">{{ calendar.label }}</h2>
                    <div class="text-xs text-gray-500">
                        {{ ctrans(":count appointments", { count: locale.number(calendar.total) }) }} · {{ calendar.timezone }}
                    </div>
                </div>
            </div>

            <ul class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-600">
                <li v-for="item in legend" :key="item.state" class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm border" :class="stateClass(item.state)" aria-hidden="true" />
                    {{ item.label }}
                </li>
                <li class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm border" :class="blockClass" aria-hidden="true" />
                    {{ ctrans("Blocked") }}
                </li>
            </ul>

            <div class="hidden overflow-hidden rounded-lg border border-gray-200 bg-white md:block">
                <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <div v-for="weekday in calendar.weekdays" :key="weekday" class="px-2 py-2">{{ weekday }}</div>
                </div>
                <div v-for="(week, weekIndex) in calendar.weeks" :key="weekIndex" class="grid grid-cols-7 border-b border-gray-100 last:border-b-0">
                    <div
                        v-for="day in week"
                        :key="day.date"
                        class="group min-h-28 min-w-0 border-r border-gray-100 p-1.5 last:border-r-0"
                        :class="day.in_month ? 'bg-white' : 'bg-gray-50'">
                        <div class="mb-1 flex items-center justify-between">
                            <button
                                v-if="canEdit"
                                type="button"
                                v-tooltip="ctrans('Block time')"
                                class="flex h-6 w-6 items-center justify-center rounded text-gray-400 opacity-0 hover:bg-gray-100 hover:text-gray-600 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-[--app-accent] group-hover:opacity-100"
                                :aria-label="ctrans('Block time')"
                                @click="openBlockForm(day.date)">
                                <FontAwesomeIcon icon="fal fa-ban" fixed-width aria-hidden="true" />
                            </button>
                            <span v-else />
                            <span
                                class="flex h-6 min-w-6 items-center justify-center rounded-full px-1 text-xs tabular-nums"
                                :class="day.is_today ? 'bg-[--app-accent] font-semibold text-[--app-accent-text]' : day.in_month ? 'text-gray-700' : 'text-gray-400'">
                                {{ day.day }}
                            </span>
                        </div>
                        <ul class="space-y-1">
                            <li v-for="block in day.blocks" :key="`block-${block.id}`">
                                <button
                                    type="button"
                                    v-tooltip="[block.period_label, block.appointment_type_name, block.reason].filter(Boolean).join(' · ')"
                                    class="block w-full truncate rounded border px-1.5 py-0.5 text-left text-xs focus:outline-none focus:ring-2 focus:ring-[--app-accent]"
                                    :class="blockClass"
                                    @click="selectedBlock = block">
                                    <FontAwesomeIcon icon="fal fa-ban" fixed-width aria-hidden="true" />
                                    {{ blockLabel(block) }}
                                </button>
                            </li>
                            <li v-for="appointment in day.appointments" :key="appointment.id">
                                <button
                                    type="button"
                                    v-tooltip="tooltip(appointment)"
                                    class="block w-full truncate rounded border px-1.5 py-0.5 text-left text-xs focus:outline-none focus:ring-2 focus:ring-[--app-accent]"
                                    :class="stateClass(appointment.state)"
                                    @click="selectedAppointmentId = appointment.id">
                                    <FontAwesomeIcon v-if="appointment.is_blocked" icon="fal fa-exclamation-triangle" class="text-amber-600" fixed-width :aria-label="ctrans('In blocked time')" />
                                    <span class="font-semibold tabular-nums">{{ appointment.time }}</span>
                                    {{ appointment.contact_name }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="md:hidden">
                <p v-if="!daysWithEntries.length" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-500">
                    {{ ctrans("No appointments this month") }}
                </p>
                <ul v-else class="space-y-3">
                    <li v-for="day in daysWithEntries" :key="day.date" class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="mb-2 text-sm font-semibold" :class="day.is_today ? 'text-[--app-accent]' : 'text-gray-700'">
                            {{ new Date(`${day.date}T00:00:00`).toLocaleDateString(undefined, { weekday: "long", day: "numeric", month: "long" }) }}
                        </div>
                        <ul class="space-y-1">
                            <li v-for="block in day.blocks" :key="`block-${block.id}`">
                                <button type="button" class="block w-full rounded border px-2 py-1 text-left text-sm focus:outline-none focus:ring-2 focus:ring-[--app-accent]" :class="blockClass" @click="selectedBlock = block">
                                    <FontAwesomeIcon icon="fal fa-ban" fixed-width aria-hidden="true" />
                                    {{ blockLabel(block) }}
                                    <span v-if="block.appointment_type_name" class="block truncate text-xs opacity-80">{{ block.appointment_type_name }}</span>
                                </button>
                            </li>
                            <li v-for="appointment in day.appointments" :key="appointment.id">
                                <button
                                    type="button"
                                    class="block w-full rounded border px-2 py-1 text-left text-sm focus:outline-none focus:ring-2 focus:ring-[--app-accent]"
                                    :class="stateClass(appointment.state)"
                                    @click="selectedAppointmentId = appointment.id">
                                    <FontAwesomeIcon v-if="appointment.is_blocked" icon="fal fa-exclamation-triangle" class="text-amber-600" fixed-width :aria-label="ctrans('In blocked time')" />
                                    <span class="font-semibold tabular-nums">{{ appointment.time }}</span>
                                    {{ appointment.contact_name }}
                                    <span class="block truncate text-xs opacity-80">{{ [appointment.appointment_type_name, appointment.staff_name].filter(Boolean).join(" · ") }}</span>
                                </button>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <Dialog v-model:visible="isAppointmentOpen" modal :header="selectedAppointment?.contact_name" :style="{ width: '30rem' }" :breakpoints="{ '575px': '95vw' }">
            <div v-if="selectedAppointment" class="space-y-4 text-sm">
                <div>
                    <div class="font-semibold text-gray-900">{{ selectedAppointment.date_label }}</div>
                    <div class="text-gray-700 tabular-nums">{{ selectedAppointment.time }}–{{ selectedAppointment.ends_time }} · {{ calendar.timezone }}</div>
                </div>
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-gray-700">
                    <template v-if="selectedAppointment.appointment_type_name">
                        <dt class="text-gray-500">{{ ctrans("Type") }}</dt>
                        <dd>{{ selectedAppointment.appointment_type_name }}</dd>
                    </template>
                    <template v-if="selectedAppointment.staff_name">
                        <dt class="text-gray-500">{{ ctrans("With") }}</dt>
                        <dd>{{ selectedAppointment.staff_name }}</dd>
                    </template>
                    <dt class="text-gray-500">{{ ctrans("State") }}</dt>
                    <dd>{{ selectedAppointment.state_label }}</dd>
                    <template v-if="selectedAppointment.email">
                        <dt class="text-gray-500">{{ ctrans("Email") }}</dt>
                        <dd class="truncate">{{ selectedAppointment.email }}</dd>
                    </template>
                    <template v-if="selectedAppointment.phone">
                        <dt class="text-gray-500">{{ ctrans("Phone") }}</dt>
                        <dd>{{ selectedAppointment.phone }}</dd>
                    </template>
                </dl>
                <p v-if="selectedAppointment.is_blocked" class="flex gap-2 rounded border border-amber-200 bg-amber-50 p-2 text-amber-800">
                    <FontAwesomeIcon icon="fal fa-exclamation-triangle" class="mt-0.5" fixed-width aria-hidden="true" />
                    {{ ctrans("This appointment falls in blocked time. Reschedule or decline it if it cannot go ahead.") }}
                </p>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3">
                    <Link :href="appointmentUrl(selectedAppointment.id)">
                        <Button type="tertiary" size="xs" :label="ctrans('Open appointment')">
                            <template #icon>
                                <FontAwesomeIcon icon="fal fa-external-link" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </Link>
                    <AppointmentActions v-if="canEdit" :appointment="selectedAppointment" compact />
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="isBlockDetailOpen" modal :header="ctrans('Blocked time')" :style="{ width: '28rem' }" :breakpoints="{ '575px': '95vw' }">
            <div v-if="selectedBlock" class="space-y-3 text-sm text-gray-700">
                <div class="font-semibold text-gray-900">{{ selectedBlock.period_label }}</div>
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1">
                    <dt class="text-gray-500">{{ ctrans("Applies to") }}</dt>
                    <dd>{{ selectedBlock.appointment_type_name ?? ctrans("All appointment types") }}</dd>
                    <template v-if="selectedBlock.reason">
                        <dt class="text-gray-500">{{ ctrans("Reason") }}</dt>
                        <dd>{{ selectedBlock.reason }}</dd>
                    </template>
                    <template v-if="selectedBlock.created_by">
                        <dt class="text-gray-500">{{ ctrans("Blocked by") }}</dt>
                        <dd>{{ selectedBlock.created_by }}</dd>
                    </template>
                </dl>
                <p class="text-xs text-gray-500">{{ ctrans("Visitors cannot book this time on the website. Appointments already booked in it stay until you reschedule or decline them.") }}</p>
            </div>
            <template v-if="canEdit" #footer>
                <Button type="tertiary" :label="ctrans('Remove block')" :loading="isRemovingBlock" @click="removeBlock">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-trash-alt" class="text-amber-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </template>
        </Dialog>

        <Dialog v-model:visible="isBlockFormOpen" modal :header="ctrans('Block time')" :style="{ width: '30rem' }" :breakpoints="{ '575px': '95vw' }">
            <form class="space-y-4 text-sm" @submit.prevent="submitBlock">
                <SegmentedToggle v-model="blockMode" :options="blockModeOptions" :ariaLabel="ctrans('Block whole days or hours')" />

                <div v-if="blockForm.is_all_day" class="flex flex-wrap items-center gap-2">
                    <DatePicker v-model="dateFrom" :minDate="today" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="datePickerPt" :aria-label="ctrans('From')" />
                    <span class="text-gray-400">–</span>
                    <DatePicker v-model="dateTo" :minDate="dateFrom ?? today" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="datePickerPt" :aria-label="ctrans('To')" />
                </div>
                <div v-else class="flex flex-wrap items-center gap-2">
                    <DatePicker v-model="dateFrom" :minDate="today" dateFormat="d M yy" :manualInput="false" showIcon iconDisplay="input" :class="fieldFocusClass" :pt="datePickerPt" :aria-label="ctrans('Date')" />
                    <Select v-model="blockForm.time_from" :options="timeOptions" :class="fieldFocusClass" class="w-28" :aria-label="ctrans('From')" />
                    <span class="text-gray-400">–</span>
                    <Select v-model="blockForm.time_to" :options="timeOptions" :class="fieldFocusClass" class="w-28" :aria-label="ctrans('To')" />
                </div>
                <p class="text-xs text-gray-500">{{ ctrans("Times are in :timezone.", { timezone: calendar.timezone }) }}</p>
                <p v-for="field in ['date_from', 'date_to', 'time_from', 'time_to']" v-show="blockForm.errors[field]" :key="field" class="text-xs text-red-600">{{ blockForm.errors[field] }}</p>

                <div class="space-y-1">
                    <label for="block-type" class="block text-xs font-medium text-gray-600">{{ ctrans("Applies to") }}</label>
                    <Select v-model="blockForm.appointment_type_id" inputId="block-type" :options="appointmentTypes" optionLabel="name" optionValue="id" :placeholder="ctrans('All appointment types')" showClear :class="fieldFocusClass" class="w-full" />
                    <p v-if="blockForm.errors.appointment_type_id" class="text-xs text-red-600">{{ blockForm.errors.appointment_type_id }}</p>
                </div>

                <div class="space-y-1">
                    <label for="block-reason" class="block text-xs font-medium text-gray-600">{{ ctrans("Reason (optional)") }}</label>
                    <InputText id="block-reason" v-model="blockForm.reason" maxlength="255" :placeholder="ctrans('e.g. Stocktake, bank holiday')" :class="fieldFocusClass" class="w-full" />
                    <p v-if="blockForm.errors.reason" class="text-xs text-red-600">{{ blockForm.errors.reason }}</p>
                </div>

                <p class="text-xs text-gray-500">{{ ctrans("Visitors will not be offered this time on the website. Appointments already booked in it are kept and marked, so you can reschedule or decline them.") }}</p>

                <div class="flex justify-end gap-2">
                    <Button type="tertiary" :label="ctrans('Cancel')" @click="isBlockFormOpen = false">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <Button type="tertiary" :label="ctrans('Block')" :loading="blockForm.processing" @click="submitBlock">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                </div>
            </form>
        </Dialog>
    </div>
</template>
