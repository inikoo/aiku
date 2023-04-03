<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {User} from "@/types/user";

const props = defineProps<{
    data: object
}>()


function userRoute(user: User) {
    switch (route().current()) {
        case 'sysadmin.users.index':
            return route(
                'sysadmin.users.show',
                [route().params['user'], user.slug]);
    }
}

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
    <Table :resource="data" :name="'usr'" class="mt-5">
        <template #cell(username)="{ item: user }">
            <Link :href="userRoute(user)">
                <template v-if="user.username">{{ user.username }}</template>
                <span v-else class="italic">{{ user['usernameNoSet'] }}</span>
            </Link>
        </template>
        <template #cell(parent_type)="{ item: user }">
            <Link v-if="user['parent_type']==='Employee'" :href="route('hr.employees.show',user['parent_id'])">{{trans('Employee')}}</Link>
            <Link v-else-if="user['parent_type']==='Guest'" :href="route('sysadmin.guests.show',user['parent_id'])">{{trans('Guest')}}</Link>
        </template>
    </Table>
</template>


