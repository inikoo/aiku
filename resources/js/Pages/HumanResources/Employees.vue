<template layout="App">
    <Head :title="labels.headTitle" />
    <div class="border-b border-gray-200 px-4 py-4 sm:flex sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-medium leading-6 text-gray-900 sm:truncate">{{labels.title}}</h1>
        </div>

    </div>
    <Table :resource="employees" class="mt-5">
        <template #cell(code)="{ item: employee }">
            <Link :href="route('hr.employees.show',employee.id)">
                {{employee.code}}
            </Link>
        </template>
    </Table>



</template>

<script setup>
import { Head,Link } from '@inertiajs/inertia-vue3';
import { Table } from "@protonemedia/inertiajs-tables-laravel-query-builder";
defineProps(["employees","labels"])

</script>
