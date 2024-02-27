<script setup lang='ts'>
import { ref, defineEmits, watch , onBeforeMount } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/navigation'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCalendarAlt, faSparkles, faSpellCheck, faSeedling,  } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { useFormatTime } from '@/Composables/useFormatTime';
/* import { useFormatTime } from '@/Composables/useFormatTime'; */
library.add(faCalendarAlt, faSparkles, faSpellCheck, faSeedling)

const props = defineProps<{
    options: {
        label: string
        icon?: string | string[]
        timestamp: string
    }[]
    state?:string
    width?:string | Number
    slidesPerView?: number
}>()


const emits = defineEmits();
const _swiperRef = ref()
const finalOptions = ref([])


const stepsWithIndex = (() => {
    finalOptions.value = props.options.map((step, index) => ({ ...step, index }));
});

const computeSetupState = (step: object) => {
    const foundState = finalOptions.value.find((item) => item.key === props.state)
    const set = step.key == props.state || step.index < foundState.index
    return set 
}

watch(() => props.state, (newData) => {
  stepsWithIndex()
})


onBeforeMount(stepsWithIndex)

</script>

<template>
    <div class="w-full py-2 flex flex-col isolate">
        <Swiper ref="_swiperRef"
            :slideToClickedSlide="false"
            :slidesPerView="slidesPerView"
            :centerInsufficientSlides="true"
            :pagination="{ clickable: true, }"
            class="w-full h-fit"
        >
            <template v-for="(step, stepIndex) in finalOptions" :key="stepIndex">
                <SwiperSlide>
                    <div class="w-fit mx-auto capitalize text-sm md:text-xs text-center"
                        :class="step.timestamp || state == step.key ? 'text-gray-500 font-semibold' : 'text-gray-300'" 
                    >
                        {{ step.label }}
                    </div>

                    <div class="relative mb-2 py-1.5 flex items-center h-10">
                        <!-- Step: Tail -->
                        <div v-if="stepIndex !=  0" 
                            class="px-2 w-full absolute flex align-center items-center align-middle content-center -translate-x-1/2 top-1/2 -translate-y-1/2">
                            <div class="w-full rounded items-center align-middle align-center flex-1">
                                <div class="w-full py-[2px] rounded"   :class="computeSetupState(step) ? 'bg-green-500' : 'bg-gray-300'" />
                            </div>
                        </div>
                        
                    
                        <!-- Step: Head -->
                        <div v-tooltip="step.label" class="border aspect-square mx-auto rounded-full text-lg flex justify-center items-center" @click="()=>emits('updateButton',{step : step, options : finalOptions })"
                            :class="[
                                computeSetupState(step) ? 'border-green-500 text-green-600 bg-green-300' : 'border-gray-300 text-gray-400 bg-white',
                                step.icon ? 'h-9' : 'h-4'
                            ]"
                        >
                            <FontAwesomeIcon v-if="step.icon" :icon='step.icon' class='text-sm' fixed-width aria-hidden='true' />
                        </div>
                    </div>

                    <!-- Step: Description -->
                    <div v-tooltip="useFormatTime(step.timestamp, { formatTime: 'hms' })" class="text-xs md:text-xs font-thin text-gray-500 text-center select-none">
                        {{ useFormatTime(step.timestamp) }}
                    </div>
                </SwiperSlide>
            </template>
        </Swiper>
    </div>
</template>