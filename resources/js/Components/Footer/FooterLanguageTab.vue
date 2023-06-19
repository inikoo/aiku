<template>
    <div class="w-full bg-gray-700 shadow-lg flex-row items-start text-white" >
        <div class="flex flex-col justify-center text-center p-1 pb-3 gap-y-1">
            <div v-for="option in options" class="grid bg-gray-500 hover:bg-gray-400 py-1">
                {{ option }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">

const props = defineProps<{
    options: [],
    active: boolean,
}>()
</script>

<style lang="scss" scoped></style>