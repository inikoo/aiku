<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { faPresentation, faCube, faText, faImage, faImages, faPaperclip, faShoppingBasket, faStar, faHandHoldingBox, faBoxFull, faBars, faBorderAll, faLocationArrow } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

import { Root } from "@/types/webBlockTypes"
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
    currentTopbar: {}
}>()

const currentTopbarCode = props.currentTopbar?.code
</script>

<template>
    <div class="flex border rounded-xl overflow-hidden">
        <div class="flex-1 p-4">
            <section aria-labelledby="products-heading" class="h-full mx-auto w-full sm:px-6 lg:px-8 overflow-y-auto">
                <TransitionGroup tag="div" name="zzz"
                    class="relative grid  gap-y-3 gap-x-4 overflow-y-auto overflow-x-hidden">

                    <div
                        v-for="block in webBlockTypes"
                        :key="block.code"
                        @click="() => (console.log('bbbbb', block), onSelectBlock(block))"
                        class="overflow-hidden h-fit group flex flex-col items-center gap-x-2 relative border border-gray-300 hover:border-indigo-500 rounded cursor-pointer"
                    >
                        <div class="h-32 w-full aspect-[4/1] flex items-center bg-gray-100 justify-center">
                            <div class="w-auto">
                                <Image :src="block.screenshot" class="object-contain"/>
                            </div>
                        </div>

                        <div class="py-2 px-1 w-full"
                            :class="block.code === currentTopbarCode ? 'bg-indigo-500 text-white' : 'bg-white'"
                        >
                            <div v-if="block?.icon" class="flex items-center justify-center">
                                <FontAwesomeIcon :icon='block?.icon' class='' fixed-width aria-hidden='true' />
                            </div>

                            <h3 class="text-sm font-medium text-center">
                                {{ block.name }}
                            </h3>
                        </div>
                    </div>
                </TransitionGroup>
            </section>
        </div>
    </div>
</template>
