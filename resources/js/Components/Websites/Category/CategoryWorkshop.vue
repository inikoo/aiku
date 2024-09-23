<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { faStar, faCircle } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ref } from "vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import { getComponent } from "@/Components/Websites/Category/Content"
import FamilyList from "@/Components/Websites/Category/FamilyList"
import Modal from '@/Components/Utils/Modal.vue'

library.add(faCube, faLink, faStar, faCircle)

const props = defineProps<{
    modelValue: any
}>()

const isModalOpen = ref(false)
const selectedProduct = ref(0)
const usedTemplates = ref({ key : "template1"})
const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()


const onPickTemplate = (family) => {
    isModalOpen.value = false
    usedTemplates.value = { key: family.key }
}


</script>

<template>
    <div id="app" class="mx-10 my-10  text-gray-600">
        <div class="py-3">
            <Button label="Templates" @click="isModalOpen = true"></Button>
        </div>
        <div class="grid grid-cols-4 gap-x-10">
            <div class="col-span-1 border-2 p-4 rounded-lg">          
                <component :is="getComponent(usedTemplates.key)"  />
            </div>


            <div class="col-span-3 border-2 p-4 rounded-lg">
                <form>
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-4">
                                <label for="website"
                                    class="block text-sm font-medium leading-6 text-gray-900">Website</label>
                                <div class="mt-2">
                                    <div
                                        class="flex rounded-md shadow-sm  focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <span
                                            class="flex select-none items-center pl-3 text-gray-500 sm:text-sm">http://</span>
                                        <input type="text" name="website" id="website"
                                            class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="www.example.com" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-full">
                                <label for="about"
                                    class="block text-sm font-medium leading-6 text-gray-900">About</label>
                                <div class="mt-2">
                                    <textarea id="about" name="about" rows="3"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                </div>
                                <p class="mt-3 text-sm leading-6 text-gray-600">Write a few sentences about
                                    yourself.</p>
                            </div>

                            <div class="col-span-full">
                                <label for="photo"
                                    class="block text-sm font-medium leading-6 text-gray-900">Photo</label>
                                <div class="mt-2 flex items-center gap-x-3">

                                    <button type="button"
                                        class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Change</button>
                                </div>
                            </div>

                            <div class="col-span-full">
                                <label for="cover-photo" class="block text-sm font-medium leading-6 text-gray-900">Cover
                                    photo</label>
                                <div
                                    class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                    <div class="text-center">

                                        <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                            <label for="file-upload"
                                                class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                                <span>Upload a file</span>
                                                <input id="file-upload" name="file-upload" type="file"
                                                    class="sr-only" />
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-2/5">
        <div tag="div"
            class="relative grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">
            <div v-for="family in FamilyList.listTemplate" :key="family.key" @click="() => onPickTemplate(family)"
                class="group flex items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                <div class="flex items-center justify-center">
                    <FontAwesomeIcon :icon='family.icon' class='' fixed-width aria-hidden='true' />
                </div>
                <h3 class="text-sm font-medium">
                    {{ family.name }}
                </h3>
            </div>
        </div>
    </Modal>

</template>