<template>
    <div class="w-full bg-gray-100 shadow-lg flex-row items-start text-gray-800 text-xs" >
        <div class="flex flex-col justify-center text-center p-1 pb-3 gap-y-1">
            <div v-for="(option, index) in options" :class="[ $props.selected == index ? 'bg-indigo-300 hover:bg-indigo-300' : '', 'grid bg-gray-200 hover:bg-gray-300 py-1']"
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
    options: {
        key: {
            label: string
        }
    },
    active: boolean,
    selected: number,
}>()

</script>

<style lang="scss" scoped></style>