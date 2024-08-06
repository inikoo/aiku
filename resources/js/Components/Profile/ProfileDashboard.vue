<script setup lang='ts'>
const props = defineProps<{
    data: {}
}>()
</script>

<template>
    <div>
        Profile Dashboard
        <pre>{{ data }}</pre>
    </div>
</template>