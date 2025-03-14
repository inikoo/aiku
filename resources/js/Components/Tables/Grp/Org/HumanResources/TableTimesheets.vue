<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 12 May 2024 21:59:08 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { useFormatTime, useSecondsToMS } from '@/Composables/useFormatTime'
import { Timesheet } from "@/types/timesheet"
import { useLocaleStore } from '@/Stores/locale'

defineProps<{
    data: {}
    tab?: string
}>()

const locale = useLocaleStore()

const timesheetRoute = (timesheet: Timesheet) => {
    switch (route().current()) {
        case "grp.org.hr.employees.show":
            return route(
                "grp.org.hr.employees.show.timesheets.show",
                [route().params["organisation"],
                route().params["employee"],
                timesheet.id])
        case "grp.overview.hr.timesheets.index":
            return route(
                "grp.org.hr.timesheets.show",
                [
                    timesheet.organisation_slug,
                    timesheet.id
                ])
        default:
            return route(
                "grp.org.hr.timesheets.show",
                [
                    route().params["organisation"],
                    timesheet.id
                ])
    }
}

</script>

<template>
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
                {{ useFormatTime(user.end_at, {localeCode: locale.language.code }) }}
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



    </Table>
</template>
