<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"

library.add(faCube, faLink)

const props = defineProps<{
    modelValue: any
}>()


const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()


</script>

<template>
    <div class="w-full bg-gray-600 py-4 px-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="col-span-2 relative">
                <img src="https://tailwindui.com/img/ecommerce-images/home-page-01-hero-full-width.jpg"
                    alt="Informative Image" class="w-full h-full object-cover rounded-md">
            </div>

            <div class="flex flex-col px-14 pt-14">
                <Editor v-model="modelValue.headline"  @update:modelValue="()=>emits('autoSave')"/>
                <Editor v-model="modelValue.description"  @update:modelValue="()=>emits('autoSave')"/>
                <button class="place-self-center bg-white text-gray-800 py-2 px-8 w-max"><Editor v-model="modelValue.button"  @update:modelValue="()=>emits('autoSave')" :toogle="[]"/></button>
            </div>
        </div>
    </div>

</template>