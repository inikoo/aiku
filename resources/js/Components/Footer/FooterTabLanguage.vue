<template>
    <div class="w-full bg-gray-100 shadow-lg flex-row items-start text-gray-800 text-xs" >
        <div class="flex flex-col justify-center text-center p-1 pb-3 gap-y-1">
            <div v-for="option in options" class="grid bg-gray-300 hover:bg-gray-200 py-1">
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