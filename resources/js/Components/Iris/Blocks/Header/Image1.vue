<script setup lang='ts'>
const props = defineProps<{
    data: {
        imgLogo: string
    }
}>()

console.log('img1', props.data)
</script>

<template>
    <div class="justify-self-start self-center">
        <img :src="data.imgLogo" class="h-20" alt="Company Logo">
    </div>
</template>