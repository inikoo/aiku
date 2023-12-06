<script setup lang="ts">
import { ref } from 'vue'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown, faCheckSquare, faSquare } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faChevronDown, faCheckSquare, faSquare)


const props = defineProps<{
    elements: {},
    title: {
        title: string
        leftIcon: any
    }
}>()
// console.log(props.elements)

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
    <div class="px-4 py-2 -mt-2 flex items-center text-xs justify-between border-b border-gray-200">
        <div class="text-2xl flex items-center py-1 gap-x-2">
            <FontAwesomeIcon v-if="title.leftIcon" :icon="title.leftIcon" aria-hidden="true" />
            <p class="inline font-semibold leading-none">{{ title.title }}</p>
        </div>
        <div class="flex items-center justify-end border border-gray-200 divide-x divide-gray-200 rounded">
            <!-- List of element (checkbox) -->
            <div class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-1 divide-gray-300 rounded overflow-hidden">
                <div
                    v-for="(value, key, index) of props.elements.find(obj => obj.key === selectedElement).elements" :key="key"
                    class="flex items-center gap-x-1 w-full px-3 cursor-pointer py-2 select-none "
                    :class="{ '': isChecked[props.elements.find(obj => obj.key === selectedElement).key].includes(key) }"
                    @click="handleCheckboxChange(key, props.elements.find(obj => obj.key === selectedElement).key)"
                    @dblclick="doubleClick(key, props.elements.find(obj => obj.key === selectedElement).key)"
                >
                    <FontAwesomeIcon v-if="isChecked[props.elements.find(obj => obj.key === selectedElement).key].includes(key)" icon="far fa-check-square" aria-hidden="true" />
                    <FontAwesomeIcon v-else icon="far fa-square" aria-hidden="true" />
                    <div
                        :class="[
                            isChecked[props.elements.find(obj => obj.key === selectedElement).key].includes(key) ? 'text-gray-800' : 'text-gray-400',
                            'grid justify-center grid-flow-col items-center capitalize hover:text-gray-600']">
                        {{ typeof value == 'string' ? value : `${value[0]} (${value[1]})` }}
                    </div>
                </div>
            </div>

            <!-- Button -->
            <Menu as="div" class="relative inline-block text-left" v-slot="{ open }">
                <!-- Initial button -->
                <div v-if="props.elements.length > 1" class="w-24 min-w-min">
                    <MenuButton class=" inline-flex relative w-full justify-end items-center pr-6 py-2 font-medium text-gray-800 capitalize focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-opacity-75">
                        {{ selectedElement }}
                        <FontAwesomeIcon icon="far fa-chevron-down" class="absolute left-2.5 transition-all duration-200 ease-in-out" :class="[open ? 'rotate-180' : '']" aria-hidden="true" />
                    </MenuButton>
                </div>
                <!-- List of button -->
                <transition enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0">
                    <MenuItems
                        class="absolute right-0 mt-2 w-40 min-w-min origin-top-right divide-y overflow-hidden divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="">
                            <MenuItem v-for="element in props.elements" v-slot="{ active }" @click="selectedElement = element.key"
                                :class="[selectedElement == element.key ? 'bg-gray-100' : '']"
                                :disabled="selectedElement == element.key"
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
        </div>


        <!-- <div v-s -->
    </div>
</template>
