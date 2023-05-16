<script setup lang="ts">
import { ref } from 'vue'
const props = defineProps<{
    elements: Array<{
        key: number
        label: string
        show: boolean
        count: number
    }>
}>()

const emits = defineEmits(['changed'])

const dataFilter = ref(props.elements);
const doubleClick = (elementKey) => {
    let showHelper = dataFilter.value[elementKey].show
    dataFilter.value.forEach(i => {
        i.show = !showHelper
    })
}

</script>


<template>

    <div class="py-2">
        <div v-if="dataFilter.length > 0" class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-2 divide-gray-200">
            <div v-for="(filter, index) of dataFilter" :key="index" class="relative w-full cursor-pointer hover:bg-indigo-300"
                :class="{ 'bg-indigo-200': filter.show }" @dblclick="doubleClick(filter.key)">
                <label :for="(filter.label + filter.key)" class="absolute w-full h-full cursor-pointer" @click="emits('changed', dataFilter)"></label>
                <div class="grid justify-center grid-flow-col items-center">
                    <label class="py-2 select-none cursor-pointer inline pr-2 text-sm">
                        {{ filter.label }} ({{ filter.count }})
                    </label>
                    <input :id="(filter.label + filter.key)" :name="(filter.label + filter.key)"
                        class="sr-only cursor-pointer focus:ring-0" type="checkbox" :checked="filter.show"
                        v-model="filter.show" />
                </div>
            </div>
        </div>
    </div>
</template>
