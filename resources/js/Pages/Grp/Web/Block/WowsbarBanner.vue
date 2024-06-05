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

library.add(faPresentation, faLink)

const props = defineProps<{
    modelValue: Object
    emptyState: Boolean
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()


const onEnter = (e) => {
    emits('update:modelValue', { ...props.modelValue, emptyState: false })
}

</script>

<template>
    <div type="button" v-if="modelValue.emptyState"
        class="relative block w-full p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <font-awesome-icon :icon="['fal', 'presentation']" class="mx-auto h-12 w-12 text-gray-400" />
        <span class="mt-2 block text-sm font-semibold text-gray-900">Wowsbar Banner</span>
        <div class="flex justify-center m-2">
            <PureInput v-model="modelValue.link" :placeholder="'Banner Link'" :suffix="true" @onEnter="(e) => onEnter(e)">
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
        <iframe :src="modelValue.link" :style="{ width: modelValue.width, height: modelValue.height }"
            title="W3Schools Free Online Web Tutorials">
        </iframe>
        <!-- Buttons -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <Popover width="w-full" class="relative h-full">
                <template #button>
                    <Button :icon="['far', 'fa-pencil']" size="xs"/>
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                       <div class="mb-1">
                            <span class="text-xs text-gray-500 pb-3">Height</span>
                            <PureInput v-model="modelValue.height"></PureInput>
                       </div>
                       <div class="mb-1">
                            <span class="text-xs text-gray-500 pb-3">Width</span>
                            <PureInput v-model="modelValue.width"></PureInput>
                       </div>
                       <div class="mb-1">
                            <span class="text-xs text-gray-500 pb-3">Link</span>
                            <PureInput v-model="modelValue.link"></PureInput>
                       </div>
                    </div>
                </template>
            </Popover>
        </div>
    </div>
</template>

