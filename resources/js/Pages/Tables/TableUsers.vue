<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { User } from "@/types/user";
import { trans } from "laravel-vue-i18n";
// import {ref,computed} from 'vue'

// import TableElements from '@/Components/Table/TableElements.vue'

const props = defineProps<{
    data: object
    // changeElements: any
    // abcdef: any
}>()


function userRoute(user: User) {
    switch (route().current()) {
        case 'sysadmin.users.index':
            return route(
                'sysadmin.users.show',
                [user.username]);
    }
}

// const fakeElements = ref([
//     {
//         key: 0,
//         label: 'hello',
//         show: true,
//         count: 5,
//     },
//     {
//         key: 1,
//         label: 'world',
//         show: false,
//         count: 13,
//     },
//     {
//         key: 2,
//         label: 'bye',
//         show: true,
//         count: 23,
//     },
// ])
// const compFakeElements = computed(() => {
//     return fakeElements.value.filter((i) => 
//         i.show == false
//     )
// })


</script>

<template>
    <!--
    <Table :resource="data" :name="'dep'" class="mt-5">
        <template #cell(code)="{ item: department }">
            <Link :href="departmentRoute(department)">
                {{ department['code'] }}
            </Link>
        </template>

    </Table>
    -->
    <!-- {{ compFakeElements }} -->
    <Table :resource="data" class="mt-5">
        <template #cell(username)="{ item: user }">
            <Link :href="userRoute(user)">
            <template v-if="user.username">{{ user.username }}</template>
            <span v-else class="italic">{{ trans('Not set') }}</span>
            </Link>
        </template>
        <template #cell(name)="{ item: user }">
            {{ user['parent']['name'] }}
        </template>
        <template #cell(parent_type)="{ item: user }">
            <Link v-if="user['parent_type'] === 'Employee'" :href="route('hr.employees.show', user['parent']['slug'])">
            {{ trans('Employee') }}</Link>
            <Link v-else-if="user['parent_type'] === 'Guest'" :href="route('sysadmin.guests.show', user['parent']['slug'])">
            {{ trans('Guest') }}</Link>
        </template>
        <!-- <template #tableElements>
            {{ props.abcdef }}
            <TableElements :elements="fakeElements" v-model="fakeElements" />
        </template> -->
    </Table>
</template>


