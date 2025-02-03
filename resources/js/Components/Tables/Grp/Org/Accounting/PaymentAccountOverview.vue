<script setup lang='ts'>
import Dashboard from '@/Components/DataDisplay/Dashboard/Dashboard.vue'


const props = defineProps<{
    data: {}
}>()
</script>

<template>
    <div>
        <Dashboard :data="data" />
    </div>
</template>