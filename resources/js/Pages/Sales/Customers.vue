<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup>
import {Head, Link} from '@inertiajs/inertia-vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['customers', 'title','pageHead']);

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="customers" class="mt-5">


        <template #cell(reference)="{ item: customer }">
            <Link :href="route('customers.show',customer.id)">
                {{ customer.reference }}
            </Link>
        </template>

    </Table>
</template>

