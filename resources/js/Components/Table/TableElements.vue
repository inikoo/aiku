<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
const props = defineProps<{
    elements: Array<{
        key: number
        label: string
        show: boolean
        count: number
    }>,
}>()

const data = ref(props.elements);
// const emit = defineEmits(['update:modelValue']);
// const delayClick = ref(500)
// const isClick = ref(false)
// const timer: any = ref()

// const times = ref(0)
// const handleClick = () => {
//     console.log("Single Click")
//     isClick.value = true

//     if (isClick.value) {
//         timer.value = setTimeout(() => {
//             isClick.value ? emit('update:modelValue', data) : ''
//         }, 200)
//     } else {
//         clearTimeout(timer.value)
//     }

//     // emit('update:modelValue', data)
// }
const inputButton = ref()
const doubleClick = (elementKey) => {
    let showHelper = data.value[elementKey].show
    data.value.forEach(i => {
        // console.log(i.show, showHelper)
        i.show = !showHelper
    })
}

</script>


<template>
    <div class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-2 divide-gray-200 py-3">
        <label :for="(element.label + element.key)" v-for="(element, index) of data" :key="index"
            class="w-full cursor-pointer hover:bg-indigo-300"
            :class="{ 'bg-indigo-200': element.show }"
            @dblclick="doubleClick(element.key)"
        >
            <div class="grid justify-center grid-flow-col items-center">
                <label class="py-2 select-none cursor-pointer inline pr-2">
                    {{ element.label }} ({{ element.count }})
                </label>
                <input ref="inputButton" :id="(element.label + element.key)" :name="(element.label + element.key)"
                    class="cursor-pointer focus:ring-0" type="checkbox" :checked="element.show" v-model="element.show" />
            </div>
        </label>
    </div>
</template>
