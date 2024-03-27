<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import { ref, reactive } from 'vue'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown, faCheckSquare, faSquare } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { onMounted } from 'vue'
import { useLocaleStore } from '@/Stores/locale'
library.add(faChevronDown, faCheckSquare, faSquare)


const props = defineProps<{
    elements: {
        [key: string]: number[] | string[]
    }
    title: {
        title: string
        leftIcon: any
    }
    name: string
}>()
// console.log(props.elements)

const emits = defineEmits(['checkboxChanged'])
const selectedGroup = ref(Object.keys(props.elements)[0]) ?? ref('')
const selectedElement: any = reactive({
    [selectedGroup.value]: props.elements[selectedGroup.value]?.elements ? Object.keys(props.elements[selectedGroup.value].elements) : []
})

let timeout: any = null

// Method: Click the box
const onClickCheckbox = (element: any, group: string) => {
    // Set timeout to prevent single on running twice on doubleclick
    clearTimeout(timeout)
    timeout = setTimeout(() => {
        if(!selectedElement[group]) selectedElement[group] = []

        if (selectedElement[group].includes(element)) {
            if(selectedElement[group].length > 1) {
                // Can't deselect if current active is one
                selectedElement[group] = selectedElement[group].filter((item: string) => item !== element)
            }
        } else {
            selectedElement[group].push(element);
        }

        emits('checkboxChanged', selectedElement)
    }, 200)
}

// Method: Double click the box
const onDoubleClickCheckbox = (element: any, group: string) => {
    clearTimeout(timeout)
    if(!selectedElement[group]) selectedElement[group] = []

    if (selectedElement[group].includes(element)) {
        selectedElement[group] = [...Object.keys(props.elements[selectedGroup.value].elements)]
    } else {
        selectedElement[group] = [`${element}`]
    }
    emits('checkboxChanged', selectedElement)
}

onMounted(() => {
    // To handle selected checkbox on hard-refresh
    const prefix = props.name === 'default' ? 'elements' : props.name + '_' + 'elements'  // To handle banners_elements, users_elements, etc
    const searchParams = new URLSearchParams(window.location.search)
    const stateParam = searchParams.get(`${prefix}[${selectedGroup.value}]`)
    stateParam ? selectedElement[selectedGroup.value] = stateParam.split(",") : false
})

</script>

<template>
    <!-- If props.element not empty -->
    <div v-if="!!selectedGroup" class="flex items-center text-xs justify-between w-fit">

        <div class="w-fit flex gap-x-1 lg:gap-x-0 items-center justify-end border border-gray-200 divide-x divide-gray-200 rounded">
            <!-- List of element (checkbox) -->
            <div class="rounded overflow-hidden grid grid-rows-2 xl:grid-rows-1 grid-flow-col w-fit flex-wrap justify-end gap-0.5 ">
                <div v-for="(value, element, index) of elements[selectedGroup]?.elements" :key="element"
                    class="hover:bg-gray-100/60 flex items-center gap-x-1 px-3 py-2.5 cursor-pointer select-none"
                    :class="[selectedElement[selectedGroup]?.includes(element) ? 'bg-gray-50' : 'bg-white']"
                    @click="onClickCheckbox(element, selectedGroup)"
                    @dblclick="onDoubleClickCheckbox(element, selectedGroup)"
                    role="filter"
                    :id="value[0].replace(' ','-')"
                >
                    <FontAwesomeIcon v-if="selectedElement[selectedGroup]?.includes(element)" icon="fal fa-check-square" aria-hidden="true" />
                    <FontAwesomeIcon v-else icon="fal fa-square" aria-hidden="true" />
                    <div :class="[ selectedElement[selectedGroup]?.includes(element) ? 'text-gray-600' : 'text-gray-600',
                        'capitalize space-x-1']">
                        <span class="font-normal">{{ value[0] }}</span>
                        <span :class="[value[1] ? 'font-semibold' : 'text-gray-400']" class="">({{ useLocaleStore().number(value[1]) }})</span>
                    </div>
                </div>
            </div>

            <!-- Button: Select state -->
            <Menu as="div" class="relative inline-block text-left" v-slot="{ open }">
                <!-- Initial button -->
                <div v-if="props.elements" class="w-min bg-gray-200 rounded-r ring-1 ring-gray-300">
                    <MenuButton class="inline-flex relative w-full justify-start items-center py-2 px-1 xl:py-2.5 font-medium text-gray-600 capitalize focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-opacity-75"
                        :class="[Object.keys(props.elements).length > 1 ? '' : 'cursor-default']"
                    >
                        <span v-if="Object.keys(props.elements).length > 1" class="pl-2 flex items-center justify-center">
                            <FontAwesomeIcon icon="fal fa-chevron-down" class="transition-all duration-200 ease-in-out" :class="[open ? 'rotate-180' : '']" aria-hidden="true" />
                        </span>
                        <span class="px-4">{{ selectedGroup }}</span>
                    </MenuButton>
                </div>

                <!-- List of button -->
                <transition v-if="Object.keys(props.elements).length > 1" enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0">
                    <MenuItems
                        class="absolute right-0 mt-2 w-40 min-w-min origin-top-right divide-y overflow-hidden divide-gray-100 rounded bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="">
                            <MenuItem v-for="element in props.elements" v-slot="{ active }" @click="selectedGroup = element.key"
                                :class="[selectedGroup == element.key ? 'bg-gray-200' : '']"
                                :disabled="selectedGroup == element.key"
                            >
                                <button :class="[
                                    active ? 'bg-gray-100' : 'text-gray-600',
                                    'group flex w-full items-center pl-4 py-2 capitalize',
                                ]">
                                    <!-- <EditIcon :active="active" class="mr-2 h-5 w-5 text-orange-400" aria-hidden="true" /> -->
                                    {{ element.key }}
                                </button>
                            </MenuItem>
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
        </div>
    </div>
</template>
