<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 07 Sept 2022 23:27:32 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="users" class="mt-5">
        <template #cell(username)="{ item: user }">
            <Link :href="route('sysadmin.users.show',user.id)">
                <template v-if="!user.username">{{ user.username }}</template>
                <span v-else class="italic">{{ labels['usernameNoSet'] }}</span>
            </Link>
        </template>
    </Table>


</template>

<script setup>
import {Head, Link} from '@inertiajs/inertia-vue3';
import {Table} from '@protonemedia/inertiajs-tables-laravel-query-builder';
import  PageHeading from '@/Components/Headings/PageHeading.vue'
defineProps(['users', 'title', 'pageHead', 'labels']);

</script>
