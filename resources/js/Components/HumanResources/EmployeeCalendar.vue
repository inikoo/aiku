<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { ctrans } from '@/Composables/useTrans'
import Toggle from '@/Components/Pure/Toggle.vue'
import Select from 'primevue/select'

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
  calendar: {
    title?: string
    year?: number
    month?: number | null
    holidays?: CalendarHoliday[]
    holidayRanges?: HolidayRange[]
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
  }
}>()

const currentYear = new Date().getFullYear()
const displayYear = computed(() => props.calendar.year ?? currentYear)
const initialMonth = props.calendar.month && props.calendar.month >= 1 && props.calendar.month <= 12 ? String(props.calendar.month) : ''

const filterYear = ref<string>(String(displayYear.value))
const filterMonth = ref<string>(initialMonth)
const filterHolidayYear = ref<number | null>(props.calendar.holidayYearPeriod?.id ?? null)

const fieldFocusClass = "[&.p-focus]:!border-[--app-accent]"
const holidayYearOptions = computed(() =>
  (props.calendar.allHolidayYears ?? []).map((holidayYear: any) => ({
    value: holidayYear.id,
    label: `${holidayYear.label} (${holidayYear.start_date} - ${holidayYear.end_date})`,
  }))
)
const yearOptions = computed(() =>
  [-3, -2, -1, 0, 1, 2, 3].map((offset) => {
    const year = String(Number(displayYear.value) + offset)
    return { value: year, label: year }
  })
)
const monthOptions = computed(() => [
  { value: "", label: ctrans("All months") },
  ...monthNames.map((name: string, index: number) => ({ value: index + 1, label: name })),
])
const useHolidayYearPeriod = ref<boolean>(!!props.calendar.holidayYearPeriod)

const holidaysMap = computed<Record<string, CalendarHoliday>>(() => {
  const map: Record<string, CalendarHoliday> = {}
  if (props.calendar.holidays) {
    for (const holiday of props.calendar.holidays) {
      map[holiday.date] = holiday
    }
  }
  return map
})

const selectedHolidayYear = computed(() => {
  if (!props.calendar.allHolidayYears) return props.calendar.holidayYearPeriod
  return props.calendar.allHolidayYears.find(hy => hy.id === filterHolidayYear.value) || props.calendar.holidayYearPeriod
})

const headerTitle = computed(() => {
  if (useHolidayYearPeriod.value && selectedHolidayYear.value) {
    return selectedHolidayYear.value.label
  }

  if (filterMonth.value) {
    const monthIndex = Number(filterMonth.value) - 1
    return `${monthNames[monthIndex]} ${filterYear.value}`
  }

  return `${ctrans('Year')} ${filterYear.value}`
})

watch(useHolidayYearPeriod, (val) => {
  if (val) {
    filterMonth.value = ''
    if (!filterHolidayYear.value && props.calendar.allHolidayYears?.length) {
      const active = props.calendar.allHolidayYears.find(hy => hy.is_active)
      filterHolidayYear.value = active ? active.id : props.calendar.allHolidayYears[0].id
    }
  }
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

  if (!props.calendar.holidayRanges || props.calendar.holidayRanges.length === 0) {
    return result
  }

  for (const range of props.calendar.holidayRanges) {
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
</script>

<template>
  <div class="px-2 pt-4 px-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">
        {{ headerTitle }}
      </h2>

      <div class="flex items-center gap-2 justify-end flex-wrap">
        <template v-if="useHolidayYearPeriod && calendar.allHolidayYears && calendar.allHolidayYears.length">
          <Select
            v-model="filterHolidayYear"
            :options="holidayYearOptions"
            optionLabel="label"
            optionValue="value"
            size="small"
            :aria-label="ctrans('Holiday Year')"
            class="min-w-[220px]"
            :class="fieldFocusClass"
          />
        </template>
        <template v-else>
          <Select
            v-model="filterYear"
            :options="yearOptions"
            optionLabel="label"
            optionValue="value"
            size="small"
            :aria-label="ctrans('Year')"
            :class="fieldFocusClass"
          />

          <Select
            v-model="filterMonth"
            :options="monthOptions"
            optionLabel="label"
            optionValue="value"
            size="small"
            :placeholder="ctrans('All months')"
            :aria-label="ctrans('Month')"
            class="min-w-[9rem]"
            :class="fieldFocusClass"
          />
        </template>

        <div v-if="calendar.allHolidayYears && calendar.allHolidayYears.length" class="flex items-center gap-2 ml-2">
          <span class="text-sm text-gray-600">{{ ctrans('Holiday Year') }}</span>
          <Toggle v-model="useHolidayYearPeriod" />
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
</template>
