<script setup lang='ts'>
import { ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/navigation'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCalendarAlt, faSparkles } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
/* import { useFormatTime } from '@/Composables/useFormatTime'; */
library.add(faCalendarAlt, faSparkles)

const props = defineProps<{
    options: {
        label: string
        icon?: string | string[]
        timestamp: string
    }[]
}>()

const _swiperRef = ref()

const isTodayHours = (date: string | Date) => {
    const currentDate = new Date()
    const dateValue = new Date(date)

    // Check if the dates are equal
    return currentDate.getFullYear() === dateValue.getFullYear() &&
        currentDate.getMonth() === dateValue.getMonth() &&
        currentDate.getDate() === dateValue.getDate() &&
        currentDate.getHours() === dateValue.getHours()
}


</script>

<template>
    <div class="w-full py-6 flex flex-col isolate">
        <Swiper ref="_swiperRef"
            :slideToClickedSlide="false"
            :slidesPerView="4"
            :centerInsufficientSlides="true"
            :pagination="{ clickable: true, }"
            class="w-full h-fit mx-12 px-12"
        >
            <template v-for="(step, stepIndex) in options">
                <SwiperSlide>
                    <div class="w-fit mx-auto capitalize text-sm md:text-base text-center"
                        :class="step.timestamp ? 'text-gray-500 font-semibold' : 'text-gray-300'" 
                    >
                        {{ step.label }}
                    </div>

                    <div class="relative mb-2 py-1.5 flex items-center h-10">
                        <!-- Step: Tail -->
                        <div v-if="stepIndex != 0"
                            class="px-2 w-full absolute flex align-center items-center align-middle content-center -translate-x-1/2 top-1/2 -translate-y-1/2">
                            <div class="w-full rounded items-center align-middle align-center flex-1">
                                <div class="w-full py-[2px] rounded" :class="step.timestamp ? 'bg-lime-500' : 'bg-gray-300'" />
                            </div>
                        </div>
                    
                        <!-- Step: Head -->
                        <div v-tooltip="step.label" class="border aspect-square mx-auto rounded-full text-lg flex justify-center items-center"
                            :class="[
                                step.timestamp ? 'border-lime-500 text-lime-600 bg-lime-300' : 'border-gray-300 text-gray-400 bg-white',
                                step.icon ? 'h-9' : 'h-4'
                            ]"
                        >
                            <FontAwesomeIcon v-if="step.icon" :icon='step.icon' class='text-sm' fixed-width aria-hidden='true' />
                        </div>
                    </div>

                    <!-- Step: Description -->
                    <div class="text-xs md:text-xs font-thin text-gray-500 text-center">
                    halo
                      <!--   {{ useFormatTime(step.timestamp, { formatTime: 'hms' }) }} -->
                    </div>
                </SwiperSlide>
            </template>
            
           <!--  <SwiperSlide v-if="false">
                <div v-if="!isTodayHours(Object.keys(options).slice(-1))" class="w-full">
                  
                    <div class="relative mb-2">
                      
                        <div class="w-full px-7 absolute flex align-center items-center align-middle content-center -translate-x-1/2 top-1/2 -translate-y-1/2">
                            <div class="w-full rounded items-center align-middle align-center flex-1">
                                <div class="w-full py-1 rounded bg-gray-200 shimmer" />
                            </div>
                        </div>

                 
                        <div class="h-10 aspect-square mx-auto rounded-full text-lg flex items-center border-2 border-gray-400 text-gray-400">
                            <span class="text-center w-full">
                                <FontAwesomeIcon icon='fal fa-calendar-alt' class='' aria-hidden='true' />
                            </span>
                        </div>
                    </div>
             
                    <div class="">
                        <div class="text-xs text-center md:text-base font-semibold text-gray-600">
                            Now
                        </div>
                    </div>
                </div>
            </SwiperSlide> -->
        </Swiper>
    </div>
</template>