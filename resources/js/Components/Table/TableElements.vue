<script setup lang="ts">
import { ref, watch } from 'vue'
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
const isChecked = ref({})

// to store the props to valid data for query
props.elements.forEach(item => {
  const key = item.key;
  const values = Object.keys(item.elements);
  
  isChecked.value[key] = values;
});

const handleCheckboxChange = (key: string, element: string) => {
    if (isChecked.value[element].includes(key)) {
        isChecked.value[element] = isChecked.value[element].filter((item) => item !== key);
    } else {
        isChecked.value[element].push(key);
    }
    
    emits('changed', isChecked.value)
}

const doubleClick = (key: string, element: string) => {
    if (isChecked.value[element].includes(key)) {
        isChecked.value[element] = []
    } else {
        isChecked.value[element] = props.elements.flatMap(obj => Object.keys(obj.elements))
    }
    emits('changed', isChecked.value[element])
}

</script>

<template>
    <div class="py-2 space-y-1">
        <div v-for="element in props.elements" class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-2 divide-gray-200">
            <div
                v-for="(value, key, index) of element.elements" :key="key"
                class="relative w-full cursor-pointer py-2 select-none hover:bg-gray-200"
                :class="{ 'bg-indigo-200 hover:bg-indigo-300': isChecked[element.key].includes(key) }"
                @click="handleCheckboxChange(key, element.key)"
                @dblclick="doubleClick(key, element.key)"
            >
                <div class="grid justify-center grid-flow-col items-center text-gray-800 capitalize">
                    {{ value }}
                </div>
            </div>
        </div>
    </div>
</template>
