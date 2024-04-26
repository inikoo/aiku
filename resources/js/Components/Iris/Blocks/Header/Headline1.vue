<script setup lang='ts'>
const props = defineProps<{
    data: {
        title: string
        description: string
    }
}>()

console.log('headline1', props.data)
</script>

<template>
    <div class="text-center place-self-center">
        <h3 class="text-indigo-600 text-xl font-bold leading-5">
            {{ data?.title }}
        </h3>
        <span class="text-gray-500 text-sm">
            {{ data?.description }}
        </span>
    </div>
</template>