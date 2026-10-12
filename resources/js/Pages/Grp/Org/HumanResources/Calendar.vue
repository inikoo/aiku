<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { ctrans } from '@/Composables/useTrans'
import { useLayoutStore } from '@/Stores/layout'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from '@/Composables/capitalize'
import { PageHeadingTypes } from '@/types/PageHeading'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Select from 'primevue/select'
import ToggleSwitch from 'primevue/toggleswitch'
import { fieldFocusClass } from '@/Composables/useHrFormControls'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faChevronLeft, faChevronRight , faDownload } from '@fal'

library.add(faChevronLeft, faChevronRight, faDownload)

type CalendarHoliday = {
    date: string
    label: string
}

type CalendarDay = {
    date: string
    dayOfMonth: number | null
    isCurrentMonth: boolean
    isHoliday: boolean
    holidayLabel: string | null
}

type CalendarMonth = {
    month: number
    name: string
    weeks: CalendarDay[][]
}

type HolidayRange = {
    from: string
    to: string
    label: string
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    year?: number
    month?: number | null
    holidays?: CalendarHoliday[]
    holidayRanges?: HolidayRange[]
    tabs?: unknown
    holidayYearPeriod?: {
        id: number
        label: string
        start_date: string
        end_date: string
    } | null
    allHolidayYears?: {
        id: number
        label: string
        start_date: string
        end_date: string
        is_active: boolean
    }[]
    defaultPeriod?: {
        start_date: string
        end_date: string
    }
}>()

const layout = useLayoutStore()
const stickyTopClass = computed(() => (layout.hasTopBanner ? 'top-[61px] lg:top-[68px]' : 'top-[33px] lg:top-10'))

const currentYear = new Date().getFullYear()

const displayYear = computed(() => props.year ?? currentYear)

const initialMonth = props.month && props.month >= 1 && props.month <= 12 ? String(props.month) : ''

const filterYear = ref<string>(String(displayYear.value))
const filterMonth = ref<string>(initialMonth)
const filterHolidayYear = ref<number | null>(props.holidayYearPeriod?.id ?? null)

const useHolidayYearPeriod = ref<boolean>(!!props.holidayYearPeriod)

const selectedHolidayYear = computed(() => {
    if (!props.allHolidayYears) return null
    return props.allHolidayYears.find(hy => hy.id === filterHolidayYear.value) || props.holidayYearPeriod
})

watch(useHolidayYearPeriod, (val) => {
    if (val) {
        filterMonth.value = ''
        if (!filterHolidayYear.value && props.allHolidayYears?.length) {
            const active = props.allHolidayYears.find(hy => hy.is_active)
            filterHolidayYear.value = active ? active.id : props.allHolidayYears[0].id
        }
    }
})

const yearOptions = computed(() => {
    const years = new Set(Array.from({ length: 7 }, (_, index) => currentYear - 3 + index))

    years.add(Number(filterYear.value))

    return [...years]
        .sort((a, b) => a - b)
        .map((year) => ({
            value: String(year),
            label: String(year),
        }))
})

const monthOptions = [
    { value: '1', label: ctrans('January') },
    { value: '2', label: ctrans('February') },
    { value: '3', label: ctrans('March') },
    { value: '4', label: ctrans('April') },
    { value: '5', label: ctrans('May') },
    { value: '6', label: ctrans('June') },
    { value: '7', label: ctrans('July') },
    { value: '8', label: ctrans('August') },
    { value: '9', label: ctrans('September') },
    { value: '10', label: ctrans('October') },
    { value: '11', label: ctrans('November') },
    { value: '12', label: ctrans('December') },
]

const monthModel = computed<string | null>({
    get: () => filterMonth.value || null,
    set: (value) => {
        filterMonth.value = value ?? ''
    },
})

const holidayYearOptions = computed(() =>
    (props.allHolidayYears ?? []).map((holidayYear) => ({
        value: holidayYear.id,
        label: `${holidayYear.label} (${holidayYear.start_date} - ${holidayYear.end_date})`,
    }))
)

const periodTitle = computed(() => {
    if (useHolidayYearPeriod.value && selectedHolidayYear.value) {
        return selectedHolidayYear.value.label
    }

    if (filterMonth.value) {
        return `${monthNames[Number(filterMonth.value) - 1]} ${filterYear.value}`
    }

    return `${ctrans('Year')} ${filterYear.value}`
})

const periodSelectPt = { label: { class: '!text-base !font-semibold !text-gray-800' } }

const toggleAccentClass = '[&.p-toggleswitch-checked_.p-toggleswitch-slider]:!bg-[--app-accent]'

const exportHolidays = () => {
    window.open(
        route('grp.org.hr.holidays.export', {
            organisation: route().params.organisation,
            year: Number(filterYear.value),
            month: filterMonth.value ? Number(filterMonth.value) : undefined,
            format: 'xlsx',
        }),
        '_blank',
        'noopener'
    )
}

const holidaysMap = computed<Record<string, CalendarHoliday>>(() => {
    const map: Record<string, CalendarHoliday> = {}

    if (props.holidays) {
        for (const holiday of props.holidays) {
            map[holiday.date] = holiday
        }
    }

    return map
})

const monthNames = [
    ctrans('January'),
    ctrans('February'),
    ctrans('March'),
    ctrans('April'),
    ctrans('May'),
    ctrans('June'),
    ctrans('July'),
    ctrans('August'),
    ctrans('September'),
    ctrans('October'),
    ctrans('November'),
    ctrans('December'),
]

const calendarMonths = computed<CalendarMonth[]>(() => {
    const months: CalendarMonth[] = []

    // Determine start and end date based on mode
    let startDate: Date
    let endDate: Date

    if (useHolidayYearPeriod.value && selectedHolidayYear.value) {
        startDate = new Date(selectedHolidayYear.value.start_date)
        endDate = new Date(selectedHolidayYear.value.end_date)
    } else {
        const year = Number(filterYear.value)
        startDate = new Date(year, 0, 1)
        endDate = new Date(year, 11, 31)
    }

    const startYear = startDate.getFullYear()
    const startMonth = startDate.getMonth()
    const totalMonths = (endDate.getFullYear() - startYear) * 12 + (endDate.getMonth() - startMonth) + 1

    for (let i = 0; i < totalMonths; i++) {
        const currentMonthDate = new Date(startYear, startMonth + i, 1)
        const year = currentMonthDate.getFullYear()
        const month = currentMonthDate.getMonth()

        const firstDay = new Date(year, month, 1)
        const daysInMonth = new Date(year, month + 1, 0).getDate()

        const monthLabel = `${monthNames[month]} ${year}`

        const weeks: CalendarDay[][] = []
        let week: CalendarDay[] = []

        const firstWeekdayIndex = (firstDay.getDay() + 6) % 7

        for (let j = 0; j < firstWeekdayIndex; j += 1) {
            week.push({
                date: '',
                dayOfMonth: null,
                isCurrentMonth: false,
                isHoliday: false,
                holidayLabel: null,
            })
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const monthNumber = month + 1
            const dateString = [
                year.toString(),
                monthNumber.toString().padStart(2, '0'),
                day.toString().padStart(2, '0'),
            ].join('-')

            const holiday = holidaysMap.value[dateString]

            week.push({
                date: dateString,
                dayOfMonth: day,
                isCurrentMonth: true,
                isHoliday: !!holiday,
                holidayLabel: holiday ? holiday.label : null,
            })

            if (week.length === 7) {
                weeks.push(week)
                week = []
            }
        }

        if (week.length > 0) {
            while (week.length < 7) {
                week.push({
                    date: '',
                    dayOfMonth: null,
                    isCurrentMonth: false,
                    isHoliday: false,
                    holidayLabel: null,
                })
            }
            weeks.push(week)
        }

        months.push({
            month,
            name: monthLabel,
            weeks,
        })
    }

    return months
})

const visibleMonths = computed<CalendarMonth[]>(() => {
    if (useHolidayYearPeriod.value || !filterMonth.value) {
        return calendarMonths.value
    }

    const monthIndex = Number(filterMonth.value) - 1

    return calendarMonths.value.filter((month) => month.month === monthIndex)
})

const holidaySummariesByMonth = computed<Record<string, { fromDay: number; toDay: number; label: string }[]>>(() => {
    const result: Record<string, { fromDay: number; toDay: number; label: string }[]> = {}

    if (!props.holidayRanges || props.holidayRanges.length === 0) {
        return result
    }

    for (const range of props.holidayRanges) {
        const fromDate = new Date(range.from)
        const toDate = new Date(range.to)

        const startMonth = fromDate.getMonth()
        const startYear = fromDate.getFullYear()

        const key = `${startYear}-${startMonth}`

        if (!result[key]) {
            result[key] = []
        }

        const fromDay = fromDate.getDate()
        const toDay = toDate.getDate()

        result[key].push({
            fromDay,
            toDay,
            label: range.label,
        })
    }

    Object.keys(result).forEach((key) => {
        result[key] = result[key].sort((a, b) => a.fromDay - b.fromDay)
    })

    return result
})

const weekdayLabels = [
    ctrans('Mo'),
    ctrans('Tu'),
    ctrans('We'),
    ctrans('Th'),
    ctrans('Fr'),
    ctrans('Sa'),
    ctrans('Su'),
]

const isWeekendColumn = (index: number): boolean => index === 5 || index === 6

const applyFilters = () => {
    const params: Record<string, unknown> = {
        ...route().params,
        year: Number(filterYear.value),
    }

    if (filterMonth.value) {
        params.month = Number(filterMonth.value)
    }

    router.get(route('grp.org.hr.calendars.index', params), {}, {
        preserveScroll: true,
        preserveState: true,
    })
}

const resetFilters = () => {
    filterYear.value = String(displayYear.value)
    filterMonth.value = ''

    applyFilters()
}

const goPrevious = () => {
    if (useHolidayYearPeriod.value && selectedHolidayYear.value && props.allHolidayYears) {
        const currentIndex = props.allHolidayYears.findIndex(hy => hy.id === selectedHolidayYear.value?.id)
        if (currentIndex < props.allHolidayYears.length - 1) {
            filterHolidayYear.value = props.allHolidayYears[currentIndex + 1].id
        }
        return
    }

    if (filterMonth.value) {
        let year = Number(filterYear.value || displayYear.value)
        let month = Number(filterMonth.value)

        month -= 1

        if (month < 1) {
            month = 12
            year -= 1
        }

        filterYear.value = String(year)
        filterMonth.value = String(month)
    } else {
        let year = Number(filterYear.value || displayYear.value)
        year -= 1
        filterYear.value = String(year)
    }

    applyFilters()
}

const goNext = () => {
    if (useHolidayYearPeriod.value && selectedHolidayYear.value && props.allHolidayYears) {
        const currentIndex = props.allHolidayYears.findIndex(hy => hy.id === selectedHolidayYear.value?.id)

        // Next (Newer) -> index - 1
        if (currentIndex > 0) {
            filterHolidayYear.value = props.allHolidayYears[currentIndex - 1].id
        }
        return
    }

    if (filterMonth.value) {
        let year = Number(filterYear.value || displayYear.value)
        let month = Number(filterMonth.value)

        month += 1

        if (month > 12) {
            month = 1
            year += 1
        }

        filterYear.value = String(year)
        filterMonth.value = String(month)
    } else {
        let year = Number(filterYear.value || displayYear.value)
        year += 1
        filterYear.value = String(year)
    }

    applyFilters()
}
</script>

<template layout="Grp">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <Button
                type="tertiary"
                icon="fal fa-download"
                :label="ctrans('Export')"
                @click="exportHolidays"
            />
        </template>
    </PageHeading>

    <div class="px-6 pb-10 pt-5">
        <div class="mt-5 bg-white shadow-sm rounded-lg p-4">
            <div
                class="sticky z-10 -mx-4 -mt-4 mb-5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 rounded-t-lg border-b border-gray-200 bg-white px-4 pb-3 pt-4 lg:gap-y-3"
                :class="stickyTopClass"
            >
                <div class="flex max-w-full items-center gap-2 sm:flex-wrap">
                    <Button
                        type="tertiary"
                        size="s"
                        icon="fal fa-chevron-left"
                        :tooltip="ctrans('Previous')"
                        @click="goPrevious"
                    />

                    <h2 class="sr-only" aria-live="polite">{{ periodTitle }}</h2>

                    <Select
                        v-if="useHolidayYearPeriod && holidayYearOptions.length"
                        v-model="filterHolidayYear"
                        :options="holidayYearOptions"
                        optionLabel="label"
                        optionValue="value"
                        size="small"
                        :aria-label="ctrans('Holiday Year')"
                        class="w-[220px] min-w-0 sm:w-auto sm:min-w-[220px]"
                        :class="fieldFocusClass"
                        :pt="periodSelectPt"
                    />
                    <template v-else>
                        <Select
                            v-model="monthModel"
                            :options="monthOptions"
                            optionLabel="label"
                            optionValue="value"
                            size="small"
                            showClear
                            :placeholder="ctrans('All months')"
                            :aria-label="ctrans('Month')"
                            class="w-44 min-w-0 sm:shrink-0"
                            :class="fieldFocusClass"
                            :pt="periodSelectPt"
                            @change="applyFilters"
                        />

                        <Select
                            v-model="filterYear"
                            :options="yearOptions"
                            optionLabel="label"
                            optionValue="value"
                            size="small"
                            :aria-label="ctrans('Year')"
                            class="w-28 shrink-0"
                            :class="fieldFocusClass"
                            :pt="periodSelectPt"
                            @change="applyFilters"
                        />
                    </template>

                    <Button
                        type="tertiary"
                        size="s"
                        icon="fal fa-chevron-right"
                        :tooltip="ctrans('Next')"
                        @click="goNext"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    <div v-if="holidayYearOptions.length" class="flex items-center gap-2 sm:ml-2">
                        <label for="calendar-holiday-year-toggle" class="cursor-pointer text-sm text-gray-600">{{ ctrans('Holiday Year') }}</label>
                        <ToggleSwitch
                            v-model="useHolidayYearPeriod"
                            inputId="calendar-holiday-year-toggle"
                            :class="toggleAccentClass"
                        />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="month in visibleMonths"
                    :key="month.month"
                    class="space-y-3"
                >
                    <div class="text-sm font-semibold text-gray-800">
                        {{ month.name }}
                    </div>

                    <div class="grid grid-cols-7 text-[11px] text-gray-400">
                        <div
                            v-for="(label, labelIndex) in weekdayLabels"
                            :key="label"
                            class="py-1 text-center"
                            :class="isWeekendColumn(labelIndex) ? 'text-gray-400 bg-gray-50' : ''"
                        >
                            {{ label }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-white p-2 shadow-sm ring-1 ring-gray-100">
                        <div
                            v-for="(week, weekIndex) in month.weeks"
                            :key="weekIndex"
                            class="grid grid-cols-7 gap-1"
                        >
                            <div
                                v-for="(day, dayIndex) in week"
                                :key="dayIndex"
                                class="h-7 text-[11px]"
                            >
                                <div
                                    v-if="day.isCurrentMonth && day.dayOfMonth"
                                    class="flex h-full w-full items-center justify-center rounded"
                                    :class="day.isHoliday
                                        ? 'bg-red-100 text-red-700 hover:bg-red-200 cursor-pointer'
                                        : isWeekendColumn(dayIndex)
                                            ? 'bg-gray-50 text-gray-500 hover:bg-gray-100'
                                            : 'text-gray-700 hover:bg-gray-100'"
                                    v-tooltip="day.isHoliday && day.holidayLabel ? day.holidayLabel : undefined"
                                >
                                    <span class="leading-none">
                                        {{ day.dayOfMonth }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="holidaySummariesByMonth[`${month.name.split(' ')[1]}-${month.month}`] && holidaySummariesByMonth[`${month.name.split(' ')[1]}-${month.month}`].length"
                        class="mt-2 border-t border-gray-200 pt-2 text-[11px]"
                    >
                        <div
                            v-for="summary in holidaySummariesByMonth[`${month.name.split(' ')[1]}-${month.month}`]"
                            :key="`${summary.fromDay}-${summary.toDay}-${summary.label}`"
                            class="flex gap-1 text-red-600"
                        >
                            <span class="font-semibold">
                                <span v-if="summary.fromDay === summary.toDay">
                                    {{ summary.fromDay }}
                                </span>
                                <span v-else>
                                    {{ summary.fromDay }}-{{ summary.toDay }}
                                </span>
                            </span>
                            <span>
                                {{ summary.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
