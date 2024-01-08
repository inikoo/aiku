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
import Image from "@/Components/Image.vue";
import { faUserCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faUserCircle)


const props = defineProps<{
    data: object,
    tab?:string,
}>()


function userRoute(user: User) {
    switch (route().current()) {
        case 'grp.sysadmin.users.index':
            return route(
                'grp.sysadmin.users.show',
                [user.username]);
    }
}


</script>

<template>

    <Table :resource="data" :name="tab"  class="mt-5">
        <template #cell(username)="{ item: user }">
            <Link :href="userRoute(user)">
                <template v-if="user['username']">{{ user['username'] }}</template>
                <span v-else class="italic">{{ trans('Not set') }}</span>
            </Link>
        </template>

        <template #cell(avatar)="{ item: user }">
            <div class="flex justify-center">
                <Image :src="user['avatar']" class="w-6 aspect-square rounded-full overflow-hidden" :alt="user.username"/>
            </div>
        </template>

        <template #cell(name)="{ item: user }">
            {{ user['parent']['name'] }}
        </template>

        <template #cell(parent_type)="{ item: user }">
            <Link v-if="user['parent_type'] === 'Employee'" :href="route('grp.org.hr.employees.show', user['parent']['slug'])">
            {{ trans('Employee') }}</Link>
            <Link v-else-if="user['parent_type'] === 'Guest'" :href="route('grp.sysadmin.guests.show', user['parent']['slug'])">
            {{ trans('Guest') }}</Link>
        </template>

    </Table>
</template>


