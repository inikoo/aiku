<script setup lang='ts'>
import Table from '@/Components/Table/Table.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
    data: {}
    tab: string
}>()
    
</script>

<template>
    <!-- <pre>{{ Object.keys(data) }}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(title)="{ item: notification }">
            <Link :href="notification.route" class="primaryLink">
                {{ notification.title }}
            </Link>
        </template>
    </Table>
</template>