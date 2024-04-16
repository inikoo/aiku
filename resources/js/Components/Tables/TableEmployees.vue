<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Employee} from "@/types/employee";
import JobPositionBadges from "@/Components/Elements/Badges/JobPositionBadges.vue";
import Icon from "@/Components/Icon.vue"
import Tag from '@/Components/Tag.vue'

const props = defineProps<{
    data: object,
    tab?:string
}>()


function employeeRoute(employee: Employee) {
    switch (route().current()) {
        case 'grp.org.hr.employees.index':
            return route(
                'grp.org.hr.employees.show',
                [route().params['organisation'],employee.slug]);

    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5"   >
        <template  #cell(state)="{ item: employee }">
            <Icon :data="employee['state_icon']"/>
        </template>

        <template #cell(slug)="{ item: employee }">
            <Link :href="employeeRoute(employee)" class="specialUnderline">
                {{ employee['slug'] }}
            </Link>
        </template>

        <template #cell(positions)="{ item: employee }">
            <div class="flex gap-x-1.5">
                <Link v-for="(position, key) in employee.positions" :key="key"
                      href="#"
                      :title="position.name" class="inline-flex">
                    <Tag :label="position.name" stringToColor />
                </Link>
            </div>
        </template>


    </Table>
</template>
