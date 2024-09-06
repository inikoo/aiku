<script setup lang='ts'>
const props = defineProps<{
    data: {}
}>()
</script>

<template>
    <div>
        Delivery Notes Showcase
        <pre>{{ data }}</pre>
    </div>
</template>