<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 12 May 2024 21:59:08 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { useFormatTime, useSecondsToMS } from '@/Composables/useFormatTime'
import customer from "@/Pages/Grp/Org/Fulfilment/Customer.vue";

const props = defineProps<{
    data: {}
    tab?: string
}>()

const timesheetRoute = (timesheet) => {
    switch (route().current()) {
        case "grp.org.hr.employees.show":
            return route(
                "grp.org.hr.employees.show.timesheets.show",
                [route().params["organisation"], route().params["employee"], timesheet.slug])
        default:
            return route(
                "grp.org.shops.show.crm.customers.show",
                [
                    route().params["organisation"],
                    route().params["shop"],
                    customer.slug
                ])
    }
}

// console.log(props.data)
</script>

<template>
    <Table :resource="data" class="mt-5" :name="tab">
        <!-- Column: Code -->
        <template #cell(slug)="{ item: timesheet }">
            <Link :href="timesheetRoute(timesheet)" class="whitespace-nowrap specialUnderline">
                {{ timesheet["slug"] }}
            </Link>
        </template>
        
        <!-- Column: Start at -->
        <template #cell(start_at)="{ item: user }">
            <div class="whitespace-nowrap">{{ useFormatTime(user.start_at, {formatTime: 'hm'}) }}</div>
        </template>
        
        <!-- Column: End at -->
        <template #cell(end_at)="{ item: user }">
            <div class="whitespace-nowrap">{{ useFormatTime(user.end_at, {formatTime: 'hm'}) }}</div>
        </template>
        
        <!-- Column: Working duration -->
        <template #cell(working_duration)="{ item: user }">
            <div class="tabular-nums">{{ useSecondsToMS(user.working_duration) }}</div>
        </template>
        
        <!-- Column: Breaks Duration -->
        <template #cell(breaks_duration)="{ item: user }">
            <div class="tabular-nums">{{ useSecondsToMS(user.breaks_duration) }}</div>
        </template>

    </Table>
</template>
