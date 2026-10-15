<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { useFormatTime } from "@/Composables/useFormatTime"

defineProps<{
    data: object
    tab?: string
}>()
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: batch }">
            <Link :href="route(batch.job_order_route.name, batch.job_order_route.parameters)" class="primaryLink">{{ batch.reference }}</Link>
        </template>
        <template #cell(artisan)="{ item: batch }">
            {{ batch.artisan ?? "-" }}
        </template>
        <template #cell(state)="{ item: batch }">
            {{ batch.state_label }}
        </template>
        <template #cell(date)="{ item: batch }">
            {{ useFormatTime(batch.date) }}
        </template>
        <template #cell(tasks)="{ item: batch }">
            <span class="tabular-nums">{{ batch.number_tasks_done }} / {{ batch.number_tasks }}</span>
        </template>
        <template #cell(quantity)="{ item: batch }">
            <span class="tabular-nums">{{ batch.quantity }}</span>
        </template>
        <template #cell(quantity_made)="{ item: batch }">
            <span class="tabular-nums">{{ batch.quantity_made }}</span>
        </template>
        <template #cell(quantity_received)="{ item: batch }">
            <span class="tabular-nums">{{ batch.quantity_received }}</span>
        </template>
    </Table>
</template>
