<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 14 Sept 2022 02:00:03 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<template layout="App">
    <Head :title="title"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="guests" class="mt-5">
        <template #cell(code)="{ item: guest }">
            <Link :href="route('sysadmin.guests.show',guest.id)">
                {{ guest.code }}
            </Link>
        </template>

    </Table>


</template>

<script setup>
import {Head, Link} from '@inertiajs/inertia-vue3';
import {Table} from '@protonemedia/inertiajs-tables-laravel-query-builder';
import  PageHeading from '@/Components/Headings/PageHeading.vue'
defineProps(['guests', 'title', 'pageHead', 'labels']);

</script>
