<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { User } from "@/types/user"
import { trans } from "laravel-vue-i18n"
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue"
import UserAgent from "@/Components/Elements/Info/UserAgent.vue"
import { useFormatTime, useSecondsToMS } from '@/Composables/useFormatTime'
// import {ref,computed} from 'vue'

// import TableElements from '@/Components/Table/TableElements.vue'

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
