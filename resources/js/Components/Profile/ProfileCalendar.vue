<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { computed, ref, watch } from "vue"
import axios from "axios"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ctrans } from "@/Composables/useTrans"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCalendarAlt, faCalendarDay, faChevronLeft, faChevronRight, faClipboardListCheck, faTicket } from "@fal"

library.add(faCalendarAlt, faCalendarDay, faChevronLeft, faChevronRight, faClipboardListCheck, faTicket)

type Kind = "appointment" | "task" | "ticket"

type CalendarEntry = {
    kind: Kind
    key: string
    date: string
    time: string | null
    title: string
    subtitle: string | null
    state: string
    is_done: boolean
    is_overdue?: boolean
    url: string
}

type CalendarDay = {
    date: string
    day: number
    in_month: boolean
    is_today: boolean
    entries: CalendarEntry[]
}

type Calendar = {
    month: string
    label: string
    previous: string
    next: string
    current: string
    timezone: string
    weekdays: string[]
    counts: Record<Kind, number>
    weeks: CalendarDay[][]
}

const props = defineProps<{
    data: Calendar | null
}>()

const calendar = ref<Calendar | null>(props.data)
watch(() => props.data, (data) => (calendar.value = data))

const loadingMonth = ref("")
const loadError = ref(false)

const goTo = async (month: string) => {
    loadingMonth.value = month
    loadError.value = false
    try {
        const { data } = await axios.get(route("grp.profile.calendar.index", { month }))
        calendar.value = data
    } catch {
        loadError.value = true
    }
    loadingMonth.value = ""
}

const kinds = computed(() => [
    { kind: "appointment" as const, label: ctrans("Appointments I arrange"), icon: "fal fa-calendar-alt" },
    { kind: "task" as const, label: ctrans("Task deadlines"), icon: "fal fa-clipboard-list-check" },
    { kind: "ticket" as const, label: ctrans("Ticket deadlines"), icon: "fal fa-ticket" },
])

const hidden = ref<Record<Kind, boolean>>({ appointment: false, task: false, ticket: false })

const kindClass: Record<Kind, string> = {
    appointment: "border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100",
    task: "border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100",
    ticket: "border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100",
}

const entryClass = (entry: CalendarEntry) => {
    if (entry.is_done) {
        return "border-gray-200 bg-gray-50 text-gray-400 line-through hover:bg-gray-100"
    }
    if (entry.is_overdue) {
        return "border-red-200 bg-red-50 text-red-700 hover:bg-red-100"
    }
    return kindClass[entry.kind]
}

const kindIcon = (kind: Kind) => kinds.value.find(item => item.kind === kind)?.icon

const visible = (entries: CalendarEntry[]) => entries.filter(entry => !hidden.value[entry.kind])

const tooltip = (entry: CalendarEntry) => [entry.time, entry.title, entry.subtitle, entry.is_overdue ? ctrans("Overdue") : null].filter(Boolean).join(" · ")

const daysWithEntries = computed(() => (calendar.value?.weeks ?? []).flat().filter(day => day.in_month && visible(day.entries).length))
</script>

<template>
    <div class="space-y-4 p-4">
        <p v-if="!calendar" class="text-sm text-gray-500">{{ ctrans("The calendar could not be loaded.") }}</p>

        <template v-else>
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
                </div>
                <div class="text-right">
                    <h2 class="text-lg font-semibold text-gray-800">{{ calendar.label }}</h2>
                    <div class="text-xs text-gray-500">{{ calendar.timezone }}</div>
                </div>
            </div>

            <p v-if="loadError" class="text-sm text-red-600">{{ ctrans("That month could not be loaded. Please try again.") }}</p>

            <div class="flex flex-wrap gap-2 text-xs">
                <button
                    v-for="item in kinds"
                    :key="item.kind"
                    type="button"
                    class="flex items-center gap-1.5 rounded-full border px-3 py-1 focus:outline-none focus:ring-2 focus:ring-[--app-accent]"
                    :class="hidden[item.kind] ? 'border-gray-200 bg-white text-gray-400' : kindClass[item.kind]"
                    :aria-pressed="!hidden[item.kind]"
                    @click="hidden[item.kind] = !hidden[item.kind]">
                    <FontAwesomeIcon :icon="item.icon" fixed-width aria-hidden="true" />
                    {{ item.label }}
                    <span class="tabular-nums opacity-70">{{ calendar.counts[item.kind] }}</span>
                </button>
                <span class="flex items-center gap-1.5 px-1 text-gray-500">
                    <span class="inline-block h-3 w-3 rounded-sm border border-red-200 bg-red-50" aria-hidden="true" />
                    {{ ctrans("Overdue") }}
                </span>
            </div>

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
                            <li v-for="entry in visible(day.entries)" :key="entry.key">
                                <Link
                                    :href="entry.url"
                                    v-tooltip="tooltip(entry)"
                                    class="block truncate rounded border px-1.5 py-0.5 text-xs focus:outline-none focus:ring-2 focus:ring-[--app-accent]"
                                    :class="entryClass(entry)">
                                    <FontAwesomeIcon :icon="kindIcon(entry.kind)" fixed-width aria-hidden="true" />
                                    <span v-if="entry.time" class="font-semibold tabular-nums">{{ entry.time }}</span>
                                    {{ entry.title }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="md:hidden">
                <p v-if="!daysWithEntries.length" class="rounded-lg border border-gray-200 bg-white p-4 text-sm text-gray-500">
                    {{ ctrans("Nothing planned this month") }}
                </p>
                <ul v-else class="space-y-3">
                    <li v-for="day in daysWithEntries" :key="day.date" class="rounded-lg border border-gray-200 bg-white p-3">
                        <div class="mb-2 text-sm font-semibold" :class="day.is_today ? 'text-[--app-accent]' : 'text-gray-700'">
                            {{ new Date(`${day.date}T00:00:00`).toLocaleDateString(undefined, { weekday: "long", day: "numeric", month: "long" }) }}
                        </div>
                        <ul class="space-y-1">
                            <li v-for="entry in visible(day.entries)" :key="entry.key">
                                <Link :href="entry.url" class="block rounded border px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-[--app-accent]" :class="entryClass(entry)">
                                    <FontAwesomeIcon :icon="kindIcon(entry.kind)" fixed-width aria-hidden="true" />
                                    <span v-if="entry.time" class="font-semibold tabular-nums">{{ entry.time }}</span>
                                    {{ entry.title }}
                                    <span v-if="entry.subtitle" class="block truncate text-xs opacity-80">{{ entry.subtitle }}</span>
                                </Link>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </template>
    </div>
</template>
