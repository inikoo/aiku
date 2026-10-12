<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 12 May 2024 21:59:08 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import Button from '@/Components/Elements/Buttons/Button.vue'
import AttendanceAdjustmentDialog from '@/Components/HumanResources/AttendanceAdjustmentDialog.vue'
import { faPen, faClock } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Table from '@/Components/Table/Table.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { useFormatTime, useSecondsToMS } from '@/Composables/useFormatTime'
import { Timesheet } from "@/types/timesheet"
import { useLocaleStore } from '@/Stores/locale'
import { ctrans } from '@/Composables/useTrans'

library.add(faPen, faClock)

defineProps<{
    data: {}
    tab?: string
    organisation?: string | null
    statistics: {
        on_time: number,
        late_clock_in: number,
        early_clock_out: number,
        no_clock_out: number,
        invalid: number,
        absent: number,
        total: number,
    }
}>()

const locale = useLocaleStore()

const statisticTones = { good: 'text-green-600', warning: 'text-amber-600', bad: 'text-red-600' }
const statisticClass = (value: number, tone: keyof typeof statisticTones) => (value > 0 ? statisticTones[tone] : 'text-gray-400')

const isAdjustmentDialogOpen = ref(false)
const adjustmentTimesheet = ref<Timesheet | null>(null)

const now = new Date()
const earliestAdjustableDate = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate())
const canRequestAdjustment = (timesheet: Timesheet) => new Date(timesheet.date) >= earliestAdjustableDate

const openAdjustmentDialog = (timesheet: Timesheet) => {
    adjustmentTimesheet.value = timesheet
    isAdjustmentDialogOpen.value = true
}

const timesheetRoute = (timesheet: Timesheet) => {
    if (route().current()?.startsWith("grp.clocking_employees.")) {
        return route("grp.clocking_employees.show", [timesheet.id])
    }

    return route(
        "grp.org.hr.timesheets.show",
        [
            (route().params as any)["organisation"],
            timesheet.id
        ])
}

function applyStatus(status: string | null) {
    const params = new URLSearchParams(location.search)
    if (status) {
        params.set('timesheet_status', status)
    } else {
        params.delete('timesheet_status')
    }
    const url = location.pathname + (params.toString() ? `?${params.toString()}` : '')
    router.get(url, {}, { preserveState: true, preserveScroll: true })
}
</script>

<template>
    <div class="mt-4 mx-4 bg-white border border-gray-200 rounded-lg p-4 shadow-sm mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 text-center divide-x divide-gray-100">
            <button type="button" @click="applyStatus('on_time')" class="px-2">
                <div class="text-lg font-bold" :class="statisticClass(statistics.on_time, 'good')">{{ statistics.on_time }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ ctrans("On time") }}</div>
            </button>

            <button type="button" @click="applyStatus('late_clock_in')" class="px-2">
                <div class="text-lg font-bold" :class="statisticClass(statistics.late_clock_in, 'warning')">{{ statistics.late_clock_in }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ ctrans("Late clock in") }}
                </div>
            </button>

            <button type="button" @click="applyStatus('early_clock_out')" class="px-2">
                <div class="text-lg font-bold" :class="statisticClass(statistics.early_clock_out, 'warning')">{{ statistics.early_clock_out }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ ctrans("Early clock out") }}
                </div>
            </button>

            <button type="button" @click="applyStatus('no_clock_out')" class="px-2">
                <div class="text-lg font-bold flex justify-center items-center gap-1" :class="statisticClass(statistics.no_clock_out, 'warning')">
                    {{ statistics.no_clock_out }}
                    <font-awesome-icon :icon="['fal', 'info-circle']" class="text-gray-400 text-[10px]" fixed-width />
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ ctrans("No clock out") }}
                </div>
            </button>

            <button type="button" @click="applyStatus('invalid')" class="px-2">
                <div class="text-lg font-bold" :class="statisticClass(statistics.invalid, 'bad')">{{ statistics.invalid }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ ctrans("Invalid") }}
                </div>
            </button>

            <button type="button" @click="applyStatus(null)" class="px-2 border-r-0 lg:border-r">
                <div class="text-lg font-bold" :class="statisticClass(statistics.absent, 'bad')">{{ statistics.absent }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ ctrans("Absent") }}
                </div>
            </button>

            <button type="button" @click="applyStatus(null)" class="px-2 border-l border-gray-200">
                <div class="text-lg font-bold text-gray-800">{{ statistics.total }}</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ ctrans("Total Logs") }}
                </div>
            </button>
        </div>
    </div>
    <Table :resource="data" class="mt-5" :name="tab">

        <!-- Column: Date -->
        <template #cell(date)="{ item: timesheet }">
            <div class="text-gray-500">
                <Link :href="timesheetRoute(timesheet)" class="whitespace-nowrap primaryLink">
                    {{ useFormatTime(timesheet.date, { localeCode: locale.language.code }) }}
                </Link>
            </div>
        </template>

        <!-- Column: Start at -->
        <template #cell(start_at)="{ item: user }">
            <div class="whitespace-nowrap">
                {{ useFormatTime(user.start_at, { formatTime: 'hh:mm', localeCode: locale.language.code }) }}
            </div>
        </template>

        <!-- Column: End at -->
        <template #cell(end_at)="{ item: user }">
            <div class="whitespace-nowrap">
                {{ useFormatTime(user.end_at, { formatTime: 'hh:mm', localeCode: locale.language.code }) }}
            </div>
        </template>

        <!-- Column: Working duration -->
        <template #cell(working_duration)="{ item: user }">
            <div class="tabular-nums">
                {{ useSecondsToMS(user.working_duration) }}
            </div>
        </template>

        <!-- Column: Breaks Duration -->
        <template #cell(breaks_duration)="{ item: user }">
            <div class="tabular-nums">
                {{ useSecondsToMS(user.breaks_duration) }}
            </div>
        </template>

        <!-- Column: Clock in / out -->
        <template #cell(clockings)="{ item: timesheet }">
            <div class="tabular-nums whitespace-nowrap" :class="timesheet.clock_out_count < timesheet.clock_in_count ? 'text-amber-600 font-medium' : ''">
                {{ timesheet.clock_in_count }} / {{ timesheet.clock_out_count }}
            </div>
        </template>

        <!-- Column: Trackers -->
        <template #cell(trackers)="{ item: timesheet }">
            <div class="tabular-nums whitespace-nowrap" :class="timesheet.number_open_time_trackers > 0 ? 'text-amber-600 font-medium' : ''">
                {{ timesheet.number_open_time_trackers }} / {{ timesheet.number_time_trackers }}
            </div>
        </template>

        <!-- Column: Actions -->
        <template #cell(actions)="{ item: timesheet }">
            <span v-if="timesheet.has_pending_adjustment" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium border bg-yellow-100 text-yellow-800 border-yellow-200 whitespace-nowrap">
                <FontAwesomeIcon icon="fal fa-clock" fixed-width aria-hidden="true" />
                {{ ctrans("Correction pending") }}
            </span>
            <Button v-else-if="canRequestAdjustment(timesheet)" type="tertiary" size="xs" :label="ctrans('Request correction')" @click="openAdjustmentDialog(timesheet)">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-pen" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </template>

    </Table>

    <AttendanceAdjustmentDialog v-model:visible="isAdjustmentDialogOpen" :timesheet="adjustmentTimesheet" :organisation="organisation" />
</template>
