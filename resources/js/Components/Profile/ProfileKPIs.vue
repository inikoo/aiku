<script setup lang='ts'>
const props = defineProps<{
    data: {}
}>()
</script>

<template>
    <div>
        Profile KPIs
        <pre>{{ data }}</pre>
    </div>
</template>