<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useLocaleStore } from "@/Stores/locale"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCalendarAlt, faCalendarDay, faChevronLeft, faChevronRight } from "@fal"

library.add(faCalendarAlt, faCalendarDay, faChevronLeft, faChevronRight)

type CalendarAppointment = {
    id: number
    time: string
    contact_name: string
    appointment_type_name: string | null
    staff_name: string | null
    state: "requested" | "accepted" | "completed" | "no_show"
    state_label: string
}

type CalendarDay = {
    date: string
    day: number
    in_month: boolean
    is_today: boolean
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
    routes: {
        calendar: routeType
        show: routeType
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

const legend = computed(() => [
    { state: "requested" as const, label: ctrans("Requested") },
    { state: "accepted" as const, label: ctrans("Accepted") },
    { state: "completed" as const, label: ctrans("Completed") },
    { state: "no_show" as const, label: ctrans("No show") },
])

const daysWithAppointments = computed(() => props.calendar.weeks.flat().filter(day => day.in_month && day.appointments.length))

const tooltip = (appointment: CalendarAppointment) => [
    `${appointment.time} ${appointment.contact_name}`,
    appointment.appointment_type_name,
    appointment.staff_name,
    appointment.state_label,
].filter(Boolean).join(" · ")
</script>

<template>
    <div>
        <Head :title="capitalize(title)" />
        <PageHeading :data="pageHead" />

        <div class="space-y-4 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
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
            </ul>

            <div class="hidden overflow-hidden rounded-lg border border-gray-200 bg-white md:block">
                <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <div v-for="weekday in calendar.weekdays" :key="weekday" class="px-2 py-2">{{ weekday }}</div>
                </div>
                <div v-for="(week, weekIndex) in calendar.weeks" :key="weekIndex" class="grid grid-cols-7 border-b border-gray-100 last:border-b-0">
                    <div
                        v-for="day in week"
                        :key="day.date"
                        class="min-h-28 min-w-0 border-r border-gray-100 p-1.5 last:border-r-0"
                        :class="day.in_month ? 'bg-white' : 'bg-gray-50'">
                        <div class="mb-1 flex justify-end">
                            <span
                                class="flex h-6 min-w-6 items-center justify-center rounded-full px-1 text-xs tabular-nums"
                                :class="day.is_today ? 'bg-[--app-accent] font-semibold text-[--app-accent-text]' : day.in_month ? 'text-gray-700' : 'text-gray-400'">
                                {{ day.day }}
                            </span>
                        </div>
                        <ul class="space-y-1">
                            <li v-for="appointment in day.appointments" :key="appointment.id">
                                <Link
                                    :href="appointmentUrl(appointment.id)"
                                    v-tooltip="tooltip(appointment)"
                                    class="block truncate rounded border px-1.5 py-0.5 text-xs focus:outline-none focus:ring-2 focus:ring-[--app-accent]"
                                    :class="stateClass(appointment.state)">
                                    <span class="font-semibold tabular-nums">{{ appointment.time }}</span>
                                    {{ appointment.contact_name }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="md:hidden">
                <p v-if="!daysWithAppointments.length" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-500">
                    {{ ctrans("No appointments this month") }}
                </p>
                <ul v-else class="space-y-3">
                    <li v-for="day in daysWithAppointments" :key="day.date" class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="mb-2 text-sm font-semibold" :class="day.is_today ? 'text-[--app-accent]' : 'text-gray-700'">
                            {{ new Date(`${day.date}T00:00:00`).toLocaleDateString(undefined, { weekday: "long", day: "numeric", month: "long" }) }}
                        </div>
                        <ul class="space-y-1">
                            <li v-for="appointment in day.appointments" :key="appointment.id">
                                <Link
                                    :href="appointmentUrl(appointment.id)"
                                    class="block rounded border px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-[--app-accent]"
                                    :class="stateClass(appointment.state)">
                                    <span class="font-semibold tabular-nums">{{ appointment.time }}</span>
                                    {{ appointment.contact_name }}
                                    <span class="block truncate text-xs opacity-80">{{ [appointment.appointment_type_name, appointment.staff_name].filter(Boolean).join(" · ") }}</span>
                                </Link>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
