<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 07 Sept 2022 23:27:32 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template layout="App">
    <Head :title="labels.headTitle" />
    <div class="border-b border-gray-200 px-4 py-4 sm:flex sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-medium leading-6 text-gray-900 sm:truncate">{{labels.title}}</h1>
        </div>

    </div>
    <Table :resource="users" class="mt-5">
        <template #cell(username)="{ item: user }">
            <Link :href="route('sysadmin.users.show',user.id)">
                <template v-if="!user.username">{{user.username}}</template><span v-else class="italic">{{ labels['usernameNoSet'] }}</span>
            </Link>
        </template>
    </Table>


</template>

<script setup>
import { Head,Link } from '@inertiajs/inertia-vue3';
import { Table } from "@protonemedia/inertiajs-tables-laravel-query-builder";
defineProps(["users","labels"])

</script>
