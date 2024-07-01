<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faPresentation, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import dataList from "../data/blogActivity.js"
import PureInput from "@/Components/Pure/PureInput.vue"
import { ref } from "vue"
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue';
import InputUseOption from "@/Components/Pure/InputUseOption.vue"

library.add(faPresentation, faLink)

const props = defineProps<{
    modelValue: Object | String
    emptyState?: Boolean
}>()

const optionWidthHeight = [
    { label: 'px', value: 'px' },
    { label: '%', value: '%' }
]


const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()


const onEnter = (e) => {
    emits('update:modelValue', { ...props.modelValue, emptyState: false })
    emits('autoSave')
}

const people = [
    {
        name: 'Jane Cooper',
        title: 'Regional Paradigm Technician',
        role: 'Admin',
        email: 'janecooper@example.com',
        telephone: '+1-202-555-0170',
        imageUrl:
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    },
    {
        name: 'Jane Cooper',
        title: 'Regional Paradigm Technician',
        role: 'Admin',
        email: 'janecooper@example.com',
        telephone: '+1-202-555-0170',
        imageUrl:
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    },
    {
        name: 'Jane Cooper',
        title: 'Regional Paradigm Technician',
        role: 'Admin',
        email: 'janecooper@example.com',
        telephone: '+1-202-555-0170',
        imageUrl:
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    },
    {
        name: 'Jane Cooper',
        title: 'Regional Paradigm Technician',
        role: 'Admin',
        email: 'janecooper@example.com',
        telephone: '+1-202-555-0170',
        imageUrl:
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    },
    {
        name: 'Jane Cooper',
        title: 'Regional Paradigm Technician',
        role: 'Admin',
        email: 'janecooper@example.com',
        telephone: '+1-202-555-0170',
        imageUrl:
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    },
    {
        name: 'Jane Cooper',
        title: 'Regional Paradigm Technician',
        role: 'Admin',
        email: 'janecooper@example.com',
        telephone: '+1-202-555-0170',
        imageUrl:
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60',
    },
]

</script>

<template>
    <!--   <div type="button" v-if="modelValue.emptyState"
        class="relative block w-full p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <font-awesome-icon :icon="['fal', 'presentation']" class="mx-auto h-12 w-12 text-gray-400" />
        <span class="mt-2 block text-sm font-semibold text-gray-900">Wowsbar Banner</span>
        <div class="flex justify-center m-2">
            <PureInput v-model="modelValue.link" :placeholder="'Banner Link'" :suffix="true" @onEnter="(e) => onEnter('a')">
                <template #suffix>
                    <div
                        class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer">
                        <span> <font-awesome-icon :icon="['fal', 'link']" /></span>
                    </div>
                </template>
</PureInput>
<Button class="ml-2" type="save" label="Save" @click="(e) => onEnter(e)"></Button>
</div>
</div>

<div v-else class="relative">
    <iframe :src="modelValue.link"
        :style="{width: `${modelValue.width.value}${modelValue.width.unit}`, height: `${modelValue.height.value}${modelValue.height.unit}` }"
        title="Wowsbar banner">
    </iframe>

    <div class="absolute top-2 right-2 flex space-x-2">
        <Popover width="w-full" class="relative h-full">
            <template #button>
                    <Button :icon="['far', 'fa-pencil']" size="xs"/>
                </template>

            <template #content="{ close: closed }">
                    <div class="w-[350px]">
                    <div class="mx-auto grid grid-cols-2 gap-4">
                        <div class="mb-1 ">
                            <span class="text-xs text-gray-500 pb-3">Height</span>
                            <InputUseOption
                                 v-model="modelValue.height" 
                                 :option="optionWidthHeight"
                                 @update:model-value="onEnter('b')"
                                 :MultiSelectProps="{
                                    label : 'label',
                                    valueProp : 'value', 
                                    placeholder : ''   
                                 }"
                                 />
                       </div>
                       <div class="mb-1">
                            <span class="text-xs text-gray-500 pb-3">Width</span>
                            <InputUseOption
                                 v-model="modelValue.width" 
                                 :option="optionWidthHeight"
                                 @update:model-value="onEnter('c')"
                                 :MultiSelectProps="{
                                    label : 'label',
                                    valueProp : 'value', 
                                    placeholder : ''   
                                 }"
                                 />
                       </div>
                    </div>
                    
                       <div class="mb-1">
                            <span class="text-xs text-gray-500 pb-3">Link</span>
                            <PureInput v-model="modelValue.link"></PureInput>
                       </div>
                       
                    </div>
                </template>
        </Popover>
    </div>
</div> -->
    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 p-5">
        <li v-for="person in people" :key="person.email"
            class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
            <div class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow aspect-h-1 h-28 aspect-w-1 w-full bg-gray-200"
                @click="() => emits('pick', image)">
                <Image :src="person" class="w-full object-cover object-center group-hover:opacity-75" />
            </div>
            <span class="font-bold text-xs">Dummy</span>
        </li>
    </ul>
    <div class="flex justify-center text-indigo-500 font-medium">Load More ...</div>
</template>
