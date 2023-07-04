
<script setup>
const props = defineProps({
    data: Object
})
import { ref } from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import Footer from './Components/Footer/index.vue'
import { faHandPointer, faHandRock } from '@/../private/pro-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
library.add(faHandPointer)
const Dummy = {
    images: [
        {
            id: 1,
            imageSrc: 'https://tailwindui.com/img/ecommerce-images/product-page-01-featured-product-shot.jpg',
            imageAlt: "Back of women's Basic Tee in black.",
            primary: true,
        },
    ],
    tools: [
        { name: 'Black', bgColor: 'bg-gray-900', selectedColor: 'ring-gray-900', icon: ['fas', 'fa-hand-pointer'], },
        { name: 'Heather Grey', bgColor: 'bg-gray-400', selectedColor: 'ring-gray-400', icon: ['fas', 'fa-hand-pointer'], },
        { name: 'Heather Grey', bgColor: 'bg-gray-400', selectedColor: 'ring-gray-400', icon: ['fas', 'fa-hand-pointer'], },
    ],
    theme: [
        { name: 'Light', value: '1' },
        { name: 'Dark', value: '2' },
        { name: 'Simple', value: '3' },
    ],
}


const selectedTheme = ref(Dummy.theme[2])


</script>

<template>
    <div class="bg-white">
        <div class="pb-16 pt-6 sm:pb-24">
            <div class="mt-8  px-4 sm:px-6 lg:px-8">
                <div class="flex gap-8">
                    <!-- tools -->
                    <div class="w-1/10">
                        <form>
                            <!-- Color picker -->
                            <div>
                                <h2 class="text-sm font-medium text-gray-900">Tools</h2>

                                <RadioGroup v-model="selectedColor" class="mt-2">
                                    <RadioGroupLabel class="sr-only">Choose a color</RadioGroupLabel>
                                    <div class="flex items-center space-x-3">
                                        <RadioGroupOption as="template" v-for="color in Dummy.tools" :key="color.name"
                                            :value="color" v-slot="{ active, checked }">
                                            <div
                                                :class="[color.selectedColor, active && checked ? 'ring ring-offset-1' : '', !active && checked ? 'ring-2' : '', 'relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none']">
                                                <RadioGroupLabel as="span" class="sr-only">{{ color.name }}
                                                </RadioGroupLabel>
                                                <span aria-hidden="true" class="flex items-center justify-center">
                                                    <span
                                                        class=" h-8 w-8 rounded-full border border-black border-opacity-10 flex items-center justify-center">
                                                        <span style="line-height: 1">
                                                            <FontAwesomeIcon :icon="color.icon" aria-hidden="true" />
                                                        </span>
                                                    </span>
                                                </span>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>

                            <!-- Size picker -->
                            <div class="mt-8">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">Theme</h2>
                                </div>

                                <RadioGroup v-model="selectedTheme" class="mt-2" @change="handleThemeChange(selectedTheme)">
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-2">
                                        <RadioGroupOption as="template" v-for="theme in Dummy.theme" :key="theme.name"
                                            :value="theme" v-slot="{ active, checked }">
                                            <div
                                                :class="['cursor-pointer focus:outline-none', active ? 'ring-2 ring-indigo-500 ring-offset-2' : '', checked ? 'border-transparent bg-indigo-600 text-white hover:bg-indigo-700' : 'border-gray-200 bg-white text-gray-900 hover:bg-gray-50', 'flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1']">
                                                <RadioGroupLabel as="span">{{ theme.name }}</RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>

                            </div>
                        </form>

                        <!-- Product details -->
                    </div>
                    <!-- Image gallery -->
                    <div style="width: 90%;">
                        <div style="transform: scale(0.8);">
                            <Footer class="lg:col-span-2 lg:row-span-2 rounded-lg "  :theme="selectedTheme.value" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>