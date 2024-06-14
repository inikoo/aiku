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
import PureInput from "@/Components/Pure/PureInput.vue"
import { ref } from "vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import Button from '@/Components/Elements/Buttons/Button.vue';

library.add(faCube, faLink, faStar, faCircle)

const props = defineProps<{
    modelValue: any
}>()

const selectedProduct = ref(0)
const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()


</script>

<template>
    <div id="app" class="ml-10 my-10  text-gray-600">
        <div class="grid grid-cols-4 gap-x-10">
            <div class="col-span-1 border-2 p-4 rounded-lg">
                <FontAwesomeIcon class="absolute top-2 right-2 text-2xl far fa-heart text-gray-500"></FontAwesomeIcon>
                <img src="https://tailwindui.com/img/ecommerce-images/product-page-03-related-product-02.jpg" alt=""
                    class="">
                <div class="mb-0.5 font-bold leading-5">
                    8x Monkey Bath Bomb 90g - Guava & Strawberry
                </div>

                <div class="mb-0.5 flex justify-between items-end">
                    <div class="leading-none">SKB-01</div>
                    <div class="text-xs">RRP: £3.95/Piece</div>
                </div>

                <div class="mb-2 flex justify-between">
                    <div class="space-x-2">
                        <FontAwesomeIcon icon="fas fa-circle" class="text-sm text-green-600"></FontAwesomeIcon>
                        <span class="text-xs">(41)</span>
                    </div>

                    <div class="flex gap-x-[1px] items-center">
                        <FontAwesomeIcon icon="fas fa-star" class=" text-xs text-gray-600"></FontAwesomeIcon>
                        <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
                        <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
                        <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
                        <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
                        <span class="ml-1 text-xs">41</span>
                    </div>
                </div>

                <div class="mb-2 bg-red-500 text-white px-2 py-0 rounded w-fit font-bold">
                    10% OFF
                </div>

                <div class="mb-2 space-y-2">
                    <div class="flex justify-between items-end">
                        <div class="text-sm font-bold leading-none whitespace-nowrap">
                            £9.60 (1.20/Piece)
                        </div>
                        <div class="text-gray-400 text-[10px] whitespace-nowrap line-through">
                            £9.60 (1.20/Piece)
                        </div>
                    </div>
                    <div class="flex justify-between items-end">
                        <div class="text-sm text-orange-500 font-bold leading-none whitespace-nowrap">
                            £8.00 (1.00/Piece)
                        </div>
                        <div class="text-gray-400 text-[10px] whitespace-nowrap line-through">
                            £8.00 (1.00/Piece)
                        </div>
                    </div>
                </div>

                <div class="mb-4 bg-orange-500 px-2 py-0.5 rounded w-fit text-xs text-white">
                    Member Price
                </div>

                <div class="mx-auto w-fit flex gap-x-2 mb-4">
                    <div class="flex items-start gap-x-1">
                        <div class="font-bold text-3xl leading-none cursor-pointer">-</div>
                        <div
                            class="h-8 aspect-square border border-gray-400 flex items-center justify-center tabular-nums text-xl font-bold">
                            1</div>
                        <div class="font-bold text-3xl leading-none cursor-pointer">+</div>
                    </div>
                    <div class="bg-gray-600 text-white rounded px-3 py-1 h-fit w-fit">Order Now</div>
                </div>
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
</template>