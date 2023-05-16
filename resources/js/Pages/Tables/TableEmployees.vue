<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {ref} from 'vue'
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Employee} from "@/types/employee";
import JobPositionBadges from "@/Components/Elements/Badges/JobPositionBadges.vue";
import TableElements from "@/Components/Table/TableElements.vue";

const props = defineProps<{
    data: object
}>()


function employeeRoute(employee: Employee) {
    switch (route().current()) {
        case 'hr.employees.index':
            return route(
                'hr.employees.show',
                [employee.slug]);

    }
}

const fakeElements = [
    {
        key: 0,
        label: 'Terima Kasih',
        show: false,
        count: 11,
    },
    {
        key: 1,
        label: 'Selamat Tinggal',
        show: true,
        count: 7,
    },
    {
        key: 2,
        label: 'Welcome',
        show: true,
        count: 23,
    },
];
//@elementChange="(dataFilter) => changeElements(dataFilter)"
</script>

<template>
    <Table :resource="data" :name="'emp'" class="mt-5"  :elements="fakeElements"  >
        <template #cell(slug)="{ item: employee }">
            <Link :href="employeeRoute(employee)">
                {{ employee['slug'] }}
            </Link>
        </template>
        <template #cell(job_positions)="{ item: employee }">
            <job-position-badges :job_positions="employee['job_positions']"/>
        </template>


    </Table>
</template>
