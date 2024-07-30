<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle, faPlus, faRedoAlt, faPuzzlePiece, faStar as fasStar } from '@fas'
import { faEllipsisV, faStar, faGlobe } from '@fal'
import { faTimes, faArrowLeft, faArrowRight } from '@far'
import { faChrome } from '@fortawesome/free-brands-svg-icons'
import { library } from '@fortawesome/fontawesome-svg-core'
import { ref } from 'vue'
import { trans } from 'laravel-vue-i18n'
library.add(faEllipsisV, faStar, faGlobe, faCircle, faChrome, faTimes, faRedoAlt, faArrowLeft, faArrowRight, faPlus, faPuzzlePiece, fasStar)    

const props = defineProps<{
    tab?: {
        icon?: string | string[]
        label?: string
    }
    url?: {
        domain?: string
        page?: string
    }
}>()

const isStar = ref(false)
const isLoadingRefreshPage = ref(false)
const keyIconPlus = ref(0)
const keyIconTimes = ref(0)

const onRefreshPage = () => {
    isLoadingRefreshPage.value = true
    setTimeout(() => {
        isLoadingRefreshPage.value = false
    }, 1200)
}
</script>

<template>
    <div class="w-full max-w-4xl mx-auto mt-4 rounded-2xl overflow-hidden shadow-xl h-fit">
        <div class="bg-gray-200 pt-1 flex">
            <div class="bg-white">
                <div class="bg-gray-200 space-x-2 rounded-br-lg w-fit px-4 py-2">
                    <FontAwesomeIcon icon='fas fa-circle' class='text-sm text-red-400 hover:text-red-500 cursor-pointer' fixed-width aria-hidden='true' />
                    <FontAwesomeIcon icon='fas fa-circle' class='text-sm text-amber-500 hover:text-amber-600 cursor-pointer' fixed-width aria-hidden='true' />
                    <FontAwesomeIcon icon='fas fa-circle' class='text-sm text-green-400 hover:text-green-500 cursor-pointer' fixed-width aria-hidden='true' />
                </div>
            </div>

            <div class="w-full max-w-64 bg-white rounded-t-lg px-3 flex justify-between items-center">
                <div class="flex gap-x-2 items-center ">
                    <FontAwesomeIcon :icon='tab?.icon || "fab fa-chrome"' class='' fixed-width aria-hidden='true' />
                    <div>{{ tab?.label || trans('New Tab')}}</div>
                </div>
                <div @click="() => keyIconTimes++" class="h-5 w-5 flex items-center rounded-full hover:bg-gray-200 cursor-pointer">
                    <Transition name="spin-to-right">
                        <FontAwesomeIcon :key="keyIconTimes" icon='far fa-times' class='text-gray-500' fixed-width aria-hidden='true' />
                    </Transition>
                </div>
            </div>
            
            <div class="bg-white">
                <div class="bg-gray-200 h-full rounded-bl-lg flex items-center px-2 py-1">
                    <div @click="() => keyIconPlus++" class="relative h-full px-2 flex items-center rounded-md hover:bg-black/10 cursor-pointer">
                        <Transition name="spin-to-right">
                            <FontAwesomeIcon :key="keyIconPlus" icon='fas fa-plus' class='' fixed-width aria-hidden='true' />
                        </Transition>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex pt-1 pb-1 px-3 gap-x-3">
            <div class="py-1 gap-x-4 flex items-center">
                <FontAwesomeIcon icon='far fa-arrow-left' class='text-sm text-gray-400 hover:text-gray-600 cursor-pointer' fixed-width aria-hidden='true' />
                <FontAwesomeIcon icon='far fa-arrow-right' class='text-sm text-gray-400 hover:text-gray-600 cursor-pointer' fixed-width aria-hidden='true' />
                <div class="relative">
                    <Transition name="spin-to-right">
                        <FontAwesomeIcon v-if="!isLoadingRefreshPage" @click="() => onRefreshPage()" icon='fas fa-redo-alt' class='text-sm text-gray-500 hover:text-gray-700 cursor-pointer block' fixed-width aria-hidden='true' />
                        <FontAwesomeIcon v-else @click="() => isLoadingRefreshPage = false" icon='far fa-times' size="sm" class='text-gray-500 hover:text-gray-700 cursor-pointer block' fixed-width aria-hidden='true' />
                    </Transition>
                </div>
            </div>

            <div class="px-3 py-1 text-sm bg-gray-100 rounded-full w-full flex items-center justify-between">
                <div class="flex items-center gap-x-2">
                    <div class="rounded-full bg-gray-300 h-5 w-5 flex justify-center items-center">
                        <FontAwesomeIcon icon='fal fa-globe' class='text-xs' fixed-width aria-hidden='true' />
                    </div>
                    <div class="font-medium truncate">{{ url?.domain || 'www.website.com' }}<span class="font-normal text-gray-400">/{{ url?.page }}</span></div>
                </div>
                <FontAwesomeIcon v-if="isStar" @click="() => isStar = !isStar" icon='fas fa-star' class='text-xs self cursor-pointer' fixed-width aria-hidden='true' />
                <FontAwesomeIcon v-else @click="() => isStar = !isStar" icon='fal fa-star' class='text-gray-500 hover:text-gray-700 text-xs self cursor-pointer' fixed-width aria-hidden='true' />
            </div>

            <div class="flex gap-x-2 items-center">
                <FontAwesomeIcon icon='fas fa-puzzle-piece' class='' fixed-width aria-hidden='true' />
                <FontAwesomeIcon icon='fal fa-ellipsis-v' class='' fixed-width aria-hidden='true' />
            </div>
        </div>


        <div class="w-full h-auto aspect-[16/9] overflow-hidden overflow-y-auto">
            <slot name="page">
                <div class="bg-indigo-500 w-full h-full"></div>
            </slot>
        </div>
    </div>
</template>