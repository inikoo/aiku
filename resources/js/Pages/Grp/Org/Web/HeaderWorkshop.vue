<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'

import Button from '@/Components/Elements/Buttons/Button.vue';

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { v4 as uuidv4 } from "uuid"
import { Switch } from '@headlessui/vue'
import Modal from '@/Components/Utils/Modal.vue'
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
import { faHeart } from '@far';

library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)

const previewMode = ref(false)
const isModalOpen = ref(false)

const listTemplate = [
    {
        icon: ["fal", "cube"],
        name: 'header 1',
        key: 'header1',
    }
]

</script>

<template>
    <div class="grid grid-flow-row-dense grid-cols-4">
        <div class="col-span-1 h-screen bg-slate-200 px-3 py-2 relative">
            <div class="flex justify-between">
                <div class="font-bold text-sm">Header :</div>
                <Button type="Primary" label="Pick Template" size="xs" @click="isModalOpen = true"></Button>
            </div>
            <div class="p-4">
                <div class="font-medium text-sm mb-2">Logo</div>
                <div type="button"
                    class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <font-awesome-icon :icon="['fas', 'image']" class="mx-auto h-12 w-12 text-gray-400" />
                    <span class="mt-2 block text-sm font-semibold text-gray-900">Logo Image</span>
                </div>
            </div>

            <!-- New bottom div with red background and absolute positioning -->
            <div class="absolute inset-x-0 bottom-0 bg-gray-300 p-4 text-white text-center">
                <div class="flex items-center gap-x-2">
                    <Switch @click="previewMode = !previewMode"
                        class="pr-1 relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                        <span aria-hidden="true"
                            :class="previewMode ? 'translate-x-6 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                            class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out" />
                    </Switch>
                    <div class="text-xs leading-none font-medium cursor-pointer select-none"
                        :class="previewMode ? 'text-indigo-500' : ' text-gray-400'">
                        Preview Mode
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-3">
            <!-- Top Bar -->
            <div class="bg-gray-800 grid grid-cols-3 text-white  justify-between items-center p-2 text-xs ">
                <div></div>
                <div class="font-bold text-center">FAIRLY TRADING WHOLESALE GIFTS SINCE 1995</div>

                <!-- Section: Logout, Cart, profile -->
                <div class="place-self-end flex items-center space-x-4 mr-4">
                    <a href="#" class="flex items-center">
                        <FontAwesomeIcon icon="fas fa-sign-out-alt" class=" mr-1"></FontAwesomeIcon> Log Out
                    </a>
                    <a href="#">
                        <FontAwesomeIcon icon="far fa-heart"></FontAwesomeIcon>
                    </a>
                    <a href="#" class="flex items-center gap-x-1">
                        <FontAwesomeIcon icon="fas fa-shopping-cart" class="relative mr-1">
                            <div
                                class="absolute -top-1 -right-1 bg-white border border-gray-800 h-2.5 aspect-square rounded-full text-gray-600 text-[6px] flex items-center justify-center">
                                5
                            </div>
                        </FontAwesomeIcon> £568.20
                    </a>
                    <a href="#" class="flex items-center">
                        <FontAwesomeIcon icon="fas fa-user-circle" class="mr-1"></FontAwesomeIcon> Hello Sandra
                    </a>
                </div>
            </div>


            <!-- Main Nav -->
            <div class="bg-white shadow-md border-b-2 border-gray-700">
                <div class="container mx-auto flex flex-col justify-between items-center">
                    <div class="w-full grid grid-cols-3 items-center justify-between space-x-4 ">
                        <img src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                            alt="Ancient Wisdom Logo" class="h-24">
                        <div class="relative w-fit justify-self-center">
                            <input type="text" placeholder="Search Products"
                                class="border border-gray-400 py-1 px-4 text-sm w-80">
                            <FontAwesomeIcon icon="fas fa-search"
                                class=" absolute top-1/2 -translate-y-1/2 right-4 text-gray-400"></FontAwesomeIcon>
                        </div>
                        <button
                            class="justify-self-end w-fit bg-stone-500 hover:bg-stone-600 text-white text-sm py-1 px-4 rounded-md">Become
                            a Gold Reward Member <FontAwesomeIcon icon="fas fa-chevron-right" class="ml-1 text-xs">
                            </FontAwesomeIcon> </button>
                    </div>


                </div>
            </div>
        </div>
    </div>


    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-2/5">
        <div tag="div" name="zzz"
            class="relative grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">

            <div v-for="block in listTemplate" :key="block.code"
                class="group flex items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                <div class="flex items-center justify-center">
                    <FontAwesomeIcon :icon='block.icon' class='' fixed-width aria-hidden='true' />
                </div>
                <h3 class="text-sm font-medium">
                    {{ block.name }}
                </h3>
            </div>

        </div>
    </Modal>

</template>


<style scss></style>
