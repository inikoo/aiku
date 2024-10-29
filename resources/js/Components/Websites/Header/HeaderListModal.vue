<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { faPresentation, faCube, faText, faImage, faImages, faPaperclip, faShoppingBasket, faStar, faHandHoldingBox, faBoxFull, faBars, faBorderAll, faLocationArrow } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

import Image from '@/Components/Image.vue'
import { Image as ImageTS } from '@/types/Image'

library.add(faPresentation, faCube, faText, faImage, faImages, faPaperclip, faShoppingBasket, faStar, faHandHoldingBox, faBoxFull, faBars, faBorderAll, faLocationArrow)

const props = defineProps<{
    onSelectBlock: Function
    webBlockTypes: {
        code: string
        screenshot: ImageTS
        icon: string | string[]
        name: string
    }[]
    currentTopbar?: {}
}>()

const currentTopbarCode = props.currentTopbar?.code
</script>

<template>
    <div class="flex border rounded-xl overflow-hidden">
        <div class="flex-1 p-4">
            <section aria-labelledby="products-heading" class="h-full mx-auto w-full sm:px-6 lg:px-8 overflow-y-auto">
                <div class="relative grid  gap-y-8 gap-x-4 overflow-y-auto overflow-x-hidden">
                    <div v-for="block in webBlockTypes" :key="block.code"
                        class="overflow-hidden h-fit group flex flex-col gap-x-2 relative ">
                        <div class="mb-1 w-fit"
                            :class="block.code === currentTopbarCode ? 'text-indigo-500 font-semibold shadow-xl' : 'bg-white'">
                            <div v-if="block?.icon" class="flex items-center justify-center">
                                <FontAwesomeIcon :icon='block?.icon' class='' fixed-width aria-hidden='true' />
                            </div>

                            <h3 class="text-sm">
                                {{ block.name }} <span v-if="block.code === currentTopbarCode">(current)</span>
                            </h3>
                        </div>

                        <slot name="image" :block="block">
                            <div @click="() => onSelectBlock(block)"
                                class="min-h-16 max-h-20 w-full aspect-[4/1] overflow-hidden flex items-center bg-gray-100 justify-center border border-gray-300 hover:border-indigo-500 rounded cursor-pointer">
                                <div class="w-auto shadow-md">
                                    <Image :src="block.screenshot" class="object-contain" />
                                </div>
                            </div>
                        </slot>

                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
