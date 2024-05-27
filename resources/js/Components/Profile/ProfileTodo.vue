<script setup lang='ts'>
const props = defineProps<{
    data: {}
}>()
</script>

<template>
    <div>
        Profile Todo
        <pre>{{ data }}</pre>
    </div>
</template>