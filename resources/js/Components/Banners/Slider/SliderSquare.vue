<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 23 Jul 2023 22:01:23 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, watch, computed, toRef, onMounted } from 'vue'
import { get } from 'lodash'
/* import SlideCorner from "@/Components/Slider/SlideCorner.vue" */
import Image from "@/Components/Image.vue"
/* import CentralStage from "@/Components/Slider/CentralStage.vue" */
import { breakpointType } from '@/Composables/useWindowSize'
import { useRemoveHttps } from '@/Composables/useRemoveHttps'
import { useWindowSize } from '@vueuse/core'
import { BannerWorkshop, CornersData } from '@/types/BannerWorkshop'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faEyeSlash } from '@fas'
import { faExternalLink, faExclamationTriangle } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExternalLink, faEyeSlash, faExclamationTriangle)

import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Pagination, Navigation } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
/* import SlideControls from '@/Components/Slider/Corners/SlideControls.vue' */



const props = defineProps<{
    production?: boolean
    jumpToIndex?: string  // ulid
    data: BannerWorkshop
    view?: string
}>()

const swiperRef = ref(null)
const intSwiperKey = ref(0)
const { width: screenWidth, height: screenHeight }: any = useWindowSize()  // To detect responsive

const filteredNulls = (corners: CornersData) => {
    if (corners) {
        return Object.fromEntries(Object.entries(corners).filter(([_, v]) => v != null))
    }

    return ''
}

// const componentEdited = toRef(() => props.data.components.filter(component => component.ulid == props.jumpToIndex))  // make jumpToIndex to reactive to watch() it
const compIndexCurrentComponent = computed(() => {
    return props.data.components.findIndex(component => component.ulid == props.jumpToIndex)
})

// Jump view to slide (banner) on click slide (SlidesWorkshop)
watch(() => props.data.components.filter(component => component.ulid == props.jumpToIndex), (newVal) => {
    swiperRef.value?.$el.swiper.slideToLoop(compIndexCurrentComponent.value, 200, false)
})

const screenBreakpoint = computed(() => {
    return breakpointType(screenWidth.value)
})

// SlidesPerView depends on the screen
const compSlidesPerView = computed(() => {
    return !props.view
        ? screenBreakpoint.value == 'sm' || screenBreakpoint.value == 'xs'
            ? 1  // If below md: 1 slide per view
            : screenBreakpoint.value == 'md'
                ? actualSlides.value.length < 3 ? actualSlides.value.length : 3   // If md: 3 slide per view
                : actualSlides.value.length < 4 ? actualSlides.value.length : 4  // If lg and larger: 4 slide per view
        : props.view == 'mobile'
            ? 1  // slidePerview is 1 if responsive button clicked on Mobile
            : props.view == 'tablet'
                ? actualSlides.value.length < 3 ? actualSlides.value.length : 3
                : actualSlides.value.length < 4 ? actualSlides.value.length : 4  // if actualSlides length is less than 4 then slidePerview = actualSlide length
})

// The actual Slides (filter slide that don't have ulid)
const actualSlides = computed(() => {
    return props.data.components.filter((item) => item.ulid)
})

// Square: Double the actualSlides length to avoid Swiper bugs (Slides must 2x length from slidesPerView)
const compHandleBannerLessSlide = computed(() => {
    return actualSlides.value.length <= 4
        ? screenBreakpoint.value == 'sm' || screenBreakpoint.value == 'xs'
            ? actualSlides.value.length == 1 ? actualSlides.value : [...actualSlides.value, ...actualSlides.value]
            : screenBreakpoint.value == 'md'
                ? actualSlides.value.length <= 3 ? actualSlides.value : [...actualSlides.value, ...actualSlides.value]
                : actualSlides.value.length <= 4 ? actualSlides.value : [...actualSlides.value, ...actualSlides.value]
        : screenBreakpoint.value == 'sm' || screenBreakpoint.value == 'xs'
            ? actualSlides.value
            : screenBreakpoint.value == 'md'
                ? actualSlides.value.length >= 6 ? actualSlides.value : [...actualSlides.value, ...actualSlides.value]
                : actualSlides.value.length >= 8 ? actualSlides.value : [...actualSlides.value, ...actualSlides.value]
})

onMounted(() => {
    setTimeout(() => {
        intSwiperKey.value++  // To handle bug on Browser back navigation (Agnest & Cat)
    }, 600)
})

// Handle color of arrow navigation (default is blue)
const compColorNav = computed(() => {
    return get(props.data, ['navigation', 'colorNav'], 'blue')
})


// Handle width of banner on first load
const compWidthBanner = computed(() => {
    return compSlidesPerView.value > compHandleBannerLessSlide.value.length ? compHandleBannerLessSlide.value.length : compSlidesPerView.value
})

</script>

<template>
   
   sdfsdf
</template>

<style lang="scss" scoped>
:deep(.swiper) {
    @apply w-full h-full;
}

:deep(.swiper-slide) {
    @apply bg-gray-200;
    text-align: center;
    font-size: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
}

:deep(.swiper-slide img) {
    @apply w-full h-full;
    object-fit: cover;
}

// Pagination: Bullet
:deep(.swiper-pagination-bullet) {
    @apply h-3 w-3 text-slate-700 text-center;
    background-color: v-bind(compColorNav) !important;
    opacity: 0.4 !important;
}

// Pagination: Bullet (active)
:deep(.swiper-pagination-bullet-active) {
    @apply text-white scale-110;
    background-color: v-bind(compColorNav) !important;
    opacity: 1 !important;
}

// Navigation: Arrow
:deep(.swiper-button-prev), :deep(.swiper-button-next) {
    color: v-bind(compColorNav) !important;
}

</style>
