<script setup lang='ts'>
import Table from '@/Components/Table/Table.vue'

const props = defineProps<{
    data: any[]
    tab: string
}>()
    
</script>

<template>
    <div>
        <Table :resources="data" :name="tab">
        
        </Table>
    </div>
</template>