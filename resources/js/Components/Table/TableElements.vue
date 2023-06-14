<script setup lang="ts">
import { ref } from 'vue'
const props = defineProps<{
    // elements: Array<{
    //     key: number
    //     label: string
    //     show: boolean
    //     count: number
    // }>
    elements: {},
}>()

const emits = defineEmits(['changed'])
const isChecked = ref([]);

const handleCheckboxChange = (value: string) => {
    if (isChecked.value.includes(value)) {
        isChecked.value = isChecked.value.filter((item) => item !== value);
    } else {
        isChecked.value.push(value);
    }
}

const doubleClick = (value: string) => {
    if (isChecked.value.includes(value)) {
        isChecked.value = []
    } else {
        isChecked.value = props.elements.flatMap(obj => Object.values(obj.elements))
    }
}
</script>

<template>
    <div class="py-2 space-y-1">
        <div v-for="element in props.elements" class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-2 divide-gray-200">
            <div
                v-for="(value, key, index) of element.elements" :key="key"
                class="relative w-full cursor-pointer py-2 select-none hover:bg-gray-200"
                :class="{ 'bg-indigo-200 hover:bg-indigo-300': isChecked.includes(value) }"
                @click="handleCheckboxChange(value)"
                @dblclick="doubleClick(value)"
            >
                <div class="grid justify-center grid-flow-col items-center text-gray-800 capitalize">
                    {{ value }}
                </div>
            </div>
        </div>
    </div>
</template>
