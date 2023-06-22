<template>
    <div class="w-full bg-gray-800 shadow-lg flex-row items-start text-gray-100 text-xs" >
        <div class="flex flex-col justify-center text-center p-1 pb-3 gap-y-1">
            <div v-for="(option, index) in data" :class="[ $props.selected == index ? 'bg-gray-600 hover:bg-gray-500' : '', 'grid hover:bg-gray-700 py-1']"
                @click="$emit('changeLanguage', [option, index])"
            >
                {{ option.label }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineEmits(['changeLanguage'])

const props = defineProps<{
    data: {
        key: {
            label: string
        }
    },
    active: boolean,
    selected: number,
}>()

</script>

<style lang="scss" scoped></style>