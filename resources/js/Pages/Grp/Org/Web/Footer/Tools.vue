<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from "@headlessui/vue"
import { faHandPointer, faHandRock, faPlus } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import { ref, watch } from "vue"

library.add(faHandPointer, faHandRock, faPlus)

const props = defineProps<{
    toolsBluprint: Array
    modelValue : Object
}>()
const emits = defineEmits()
</script>

<template>
    <div class="flex">
        <RadioGroup class="px-4 py-1" v-model="modelValue.hand">
            <RadioGroupLabel class="sr-only">Choose a tool</RadioGroupLabel>
            <div class="flex items-center space-x-3">
                <RadioGroupOption v-for="tool in toolsBluprint.hand" :key="tool.name" :value="tool.value"
                    v-slot="{ active, checked }">
                    <div :class="[
                        tool.tools,
                        active && checked ? 'ring ring-offset-1' : '',
                        !active && checked ? 'ring-2' : '',
                        'relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none',
                    ]">
                        <RadioGroupLabel as="span" class="sr-only">{{ tool.name }}</RadioGroupLabel>
                        <span aria-hidden="true" class="flex items-center justify-center">
                            <span
                                class="h-6 w-6 rounded-full border border-black border-opacity-10 flex items-center justify-center">
                                <span style="line-height: 1" class="text-xs">
                                    <FontAwesomeIcon :icon="tool.icon" aria-hidden="true" />
                                </span>
                            </span>
                        </span>
                    </div>
                </RadioGroupOption>
            </div>
        </RadioGroup>

        <RadioGroup class="px-4" v-model="modelValue.theme">
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-3">
                <RadioGroupOption 
                    as="template" 
                    v-for="theme in toolsBluprint.theme" :key="theme.name" :value="theme.value"
                    v-slot="{ active, checked }">
                    <div
                        :class="[
                        checked ? 'border-transparent bg-indigo-600 text-white hover:bg-indigo-700' : 'border-gray-200 bg-white text-gray-900 hover:bg-gray-50', 
                        'flex items-center justify-center rounded-md border py-1 px-1 text-sm font-medium uppercase sm:flex-1 cursor-pointer focus:outline-none'
                        ]">
                        <RadioGroupLabel as="span">{{ theme.name }}</RadioGroupLabel>
                    </div>
                </RadioGroupOption>
            </div>
        </RadioGroup>


        <RadioGroup class="px-4" v-model="modelValue.columnType"  @update:modelValue="(e)=>emits('changeColumnType',e)" >
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-3">
                <RadioGroupOption 
                    as="template" 
                    v-for="option in toolsBluprint.columnsType" 
                    :key="option.value"
                    
                    :value="option.value" v-slot="{ active, checked }">
                    <div :class="{
                        'border-transparent bg-indigo-600 text-white hover:bg-indigo-700': checked,
                        'border-gray-200 bg-white text-gray-900 hover:bg-gray-50': !checked,
                        'flex items-center justify-center rounded-md border py-1 px-1 text-sm font-medium uppercase sm:flex-1': true
                    }">
                        <RadioGroupLabel as="span">{{ option.name }}</RadioGroupLabel>
                    </div>
                </RadioGroupOption>
            </div>
        </RadioGroup>

    </div>
</template>
