<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import { ref, reactive } from 'vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faChevronDown, faCheckSquare, faSquare } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { onMounted } from 'vue'
import { useLocaleStore } from '@/Stores/locale'
import Button from '../Elements/Buttons/Button.vue'
library.add(faChevronDown, faCheckSquare, faSquare)


const props = defineProps<{
    elements: {
        [key: string]: {
            [key: string]: number[] | string[]
        }
    }
    title: {
        title: string
        leftIcon: any
    }
    name: string
}>()

const emits = defineEmits<{
    (e: 'checkboxChanged', value: SelectedElement): void
}>()

interface SelectedElement {
    [key: string]: string[]
}

const selectedGroup = ref(Object.keys(props.elements)[0]) || ref('')
const selectedElement: SelectedElement = reactive({
    [selectedGroup.value]: props.elements[selectedGroup.value]?.elements ? Object.keys(props.elements[selectedGroup.value].elements) : []
})

let timeout: any = null

// Method: Click the box
const onClickCheckbox = (elementName: string, scope: string) => {
    // Set timeout to prevent single on running twice on doubleclick
    clearTimeout(timeout)
    timeout = setTimeout(() => {
        // If scope is not exist
        if(!selectedElement[scope]) {
            selectedElement[scope] = []  // Then initialise to empty array 
        }

        if (selectedElement[scope].includes(elementName)) {
            // If current active is more than 1, then can deselect
            if(selectedElement[scope].length > 1) {
                console.log('qq', selectedElement[scope])
                selectedElement[scope] = selectedElement[scope].filter((item: string) => item !== elementName)
                console.log('qq', selectedElement[scope])
            }
        } else {
            selectedElement[scope].push(elementName)
        }

        console.log('end of onClick', selectedElement[scope])
        emits('checkboxChanged', selectedElement)
    }, 200)
}

// Method: Double click the box
const onDoubleClickCheckbox = (elementName: string, scope: string) => {
    clearTimeout(timeout)

    // If scope is not exist, then set to empty array
    if(!selectedElement[scope]) {
        selectedElement[scope] = []
    }

    // // If already checked, then check all
    // if (selectedElement[scope].includes(elementName)) {
    //     selectedElement[scope] = [...Object.keys(props.elements[selectedGroup.value].elements)]
    // }
    // // If not checked yet, then only select it
    // else {
    //     selectedElement[scope] = [elementName]
    // }

    selectedElement[scope] = [elementName]

    emits('checkboxChanged', selectedElement)
}

onMounted(() => {
    // console.log('fff', props.elements)
    // To handle selected checkbox on hard-refresh
    const prefix = props.name === 'default' ? 'elements' : props.name + '_' + 'elements'  // To handle banners_elements, users_elements, etc
    const searchParams = new URLSearchParams(window.location.search)
    const stateParam = searchParams.get(`${prefix}[${selectedGroup.value}]`)
    stateParam ? selectedElement[selectedGroup.value] = stateParam.split(",") : false

    // const asdfzxc = Object.keys(props.elements)
    // console.log('rr', asdfzxc.some(elementName => window.location.search.includes(`elements[${elementName}]`)))
})

</script>

<template>
    <!-- <pre>{{ elements }}</pre> -->

    <Popover class="relative">
        <!-- Button: Filter table -->
        <PopoverButton :as="Button" type="tertiary" label="Filter table" icon="fal fa-filter" />

        <Transition>
            <PopoverPanel class="z-20 absolute right-0 mt-2 min-w-80 origin-top-right rounded-lg overflow-hidden bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                <div v-for="(element, elementScope, idxElement) in elements"
                    :key="`${elementScope}${idxElement}}`"
                    class="w-full"
                    :class="idxElement === 0 ? '' : 'mt-4'"
                >
                    <div class="text-center py-1 bg-slate-300 text-gray-600 capitalize">
                        {{ elementScope }}
                    </div>
                    <!-- List of element (checkbox) -->
                    <div class="max-w-96 grid grid-cols-2 rounded overflow-hidden w-full flex-wrap justify-end gap-0.5 ">
                        <div v-for="(value, elementKey) of element.elements"
                            :key="elementKey + elementKey"
                            class="hover:bg-gray-100 flex items-center gap-x-1 px-3 py-2.5 cursor-pointer select-none"
                            :class="[selectedElement[elementScope]?.includes(value) ? 'bg-gray-50' : 'bg-white']"
                            @click="onClickCheckbox(elementKey, elementScope)"
                            @dblclick="onDoubleClickCheckbox(elementKey, elementScope)" role="filter"
                        >
                            <FontAwesomeIcon v-if="selectedElement[elementScope]?.includes(elementKey)" icon="fal fa-check-square" aria-hidden="true" />
                            <FontAwesomeIcon v-else icon="fal fa-square" aria-hidden="true" />
                            <div :class="[selectedElement[elementScope]?.includes(value) ? 'text-gray-600' : 'text-gray-600']"
                                class="capitalize space-x-1">
                                <span class="font-normal">{{ value[0] }}</span>
                                <span :class="[value[1] ? 'font-semibold' : 'text-gray-400']" class="">
                                    ({{ useLocaleStore().number(value[1]) }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </PopoverPanel>
        </Transition>
    </Popover>

    <!-- <div v-if="selectedGroup" class="relative flex items-center text-xs justify-between w-fit">
        <div class=""
        >
            
        </div>
    </div> -->
</template>
