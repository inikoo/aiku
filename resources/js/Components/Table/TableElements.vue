<script setup lang="ts">
import { ref, watch } from 'vue'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronUp, faCheckSquare, faSquare } from "@/../private/pro-regular-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faChevronUp, faCheckSquare, faSquare)


const props = defineProps<{
    // elements: Array<{
    //     key: number
    //     label: string
    //     show: boolean
    //     count: number
    // }>
    elements: {},
}>()
console.log(props.elements)

const emits = defineEmits(['changed'])
const isChecked = ref({})
const selectedElement = ref(props.elements[0].key)

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
    <div class="px-4 flex items-center text-xs justify-between border-y border-gray-200">
        <Menu as="div" class="relative inline-block text-left" v-slot="{ open }">
            <!-- Initial button -->
            <div class="w-24 min-w-min">
                <MenuButton class="inline-flex relative w-full justify-start items-center rounded-md pr-6 py-1 font-medium text-gray-800 capitalize focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-opacity-75">
                    {{ selectedElement }}
                    <FontAwesomeIcon icon="far fa-chevron-up" class="absolute right-2 transition-all duration-200 ease-in-out" :class="[open ? '' : 'rotate-180']" aria-hidden="true" />
                    <!-- <ChevronDownIcon class="ml-2 -mr-1 h-5 w-5 text-violet-200 hover:text-violet-100" aria-hidden="true" /> -->
                </MenuButton>
            </div>

            <!-- List of button -->
            <transition enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0">
                <MenuItems
                    class="absolute left-0 mt-2 w-40 min-w-min origin-top-right divide-y overflow-hidden divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                    <div class="">
                        <MenuItem v-for="element in props.elements" v-slot="{ active }" @click="selectedElement = element.key"
                            :class="[selectedElement == element.key ? 'bg-gray-100' : '']"
                        >
                            <button :class="[
                                active ? 'bg-gray-300' : 'text-gray-800',
                                'group flex w-full items-center pl-4 py-2 capitalize',
                            ]">
                                <!-- <EditIcon :active="active" class="mr-2 h-5 w-5 text-indigo-400" aria-hidden="true" /> -->
                                {{ element.key }}
                            </button>
                        </MenuItem>
                    </div>
                </MenuItems>
            </transition>
        </Menu>

        <!-- List of element -->
        <div class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-1 divide-gray-300 rounded overflow-hidden">
            <div
                v-for="(value, key, index) of props.elements.find(obj => obj.key === selectedElement).elements" :key="key"
                class="flex items-center gap-x-1 w-full px-3 cursor-pointer py-1 select-none "
                :class="{ '': isChecked[props.elements.find(obj => obj.key === selectedElement).key].includes(key) }"
                @click="handleCheckboxChange(key, props.elements.find(obj => obj.key === selectedElement).key)"
                @dblclick="doubleClick(key, props.elements.find(obj => obj.key === selectedElement).key)"
            >
                <FontAwesomeIcon v-if="isChecked[props.elements.find(obj => obj.key === selectedElement).key].includes(key)" icon="far fa-check-square" aria-hidden="true" />
                <FontAwesomeIcon v-else icon="far fa-square" aria-hidden="true" />
                <div 
                    :class="[
                        isChecked[props.elements.find(obj => obj.key === selectedElement).key].includes(key) ? 'text-gray-800' : 'text-gray-400',
                        'grid justify-center grid-flow-col items-center capitalize']">
                    {{ value }}
                </div>
            </div>
        </div>
        <!-- <div v-s -->
    </div>
</template>
