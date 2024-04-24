<script setup lang='ts'>
import Table from '@/Components/Table/Table.vue'

    const props = defineProps<{
        data: any
        tab: string
    }>()
</script>

<template>
    <div class="m-8 rounded-md border border-gray-200 overflow-hidden">
        <Table :resource="data" :name="tab">

        </Table>
    </div>
</template>