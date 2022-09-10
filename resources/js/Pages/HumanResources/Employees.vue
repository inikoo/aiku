<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="employees" class="mt-5">
        <template #cell(code)="{ item: employee }">
            <Link :href="route('hr.employees.show',employee.id)">
                {{ employee.code }}
            </Link>
        </template>
    </Table>
</template>

<script setup>
import {Head, Link} from '@inertiajs/inertia-vue3';
import {Table} from '@protonemedia/inertiajs-tables-laravel-query-builder';
import PageHeading from '@/Components/Headings/PageHeading.vue';

defineProps(['employees', 'title','pageHead']);

</script>
