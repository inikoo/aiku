<script setup lang="ts">
import { ref, reactive, onBeforeMount, watch, onBeforeUnmount, computed } from "vue"
import SlidesWorkshop from "@/Components/Banners/SlidesWorkshop.vue"
import SliderLandscape from "@/Components/Banners/Slider/SliderLandscape.vue"
import SliderSquare from "@/Components/Banners/Slider/SliderSquare.vue"
import SlidesWorkshopAddMode from "@/Components/Banners/SlidesWorkshopAddMode.vue"
import ScreenView from "@/Components/ScreenView.vue"


const props = defineProps<{
    data: any
    imagesUploadRoute: any
    user: any
    banner: any
    galleryRoute:{
        stock_images : routeType,
        uploaded_images : routeType
    }
}>()

const jumpToIndex = ref('')
const screenView = ref("")

</script>

<template>
    <div v-if="data.components.filter((item: any) => item.ulid != null).length > 0" class="w-full">
        <!-- Button: Screen -->
        <div class="flex justify-end pr-2">
            <ScreenView @screenView="(val) => (screenView = val)" />
        </div>

        <!-- Banner: Square or Landscape -->
        <div class="flex pr-0.5" :class="[data.type === 'square' ? 'justify-start 2xl:justify-center' : 'justify-center']">
            <SliderSquare v-if="data.type === 'square'" :data="data" :jumpToIndex="jumpToIndex" :view="screenView" class="w-full min-h-[250px] max-h-[400px]" />
            <SliderLandscape v-else :data="data" :jumpToIndex="jumpToIndex" :view="screenView" />
        </div>
        
        <!-- Editor -->
        <SlidesWorkshop 
            :bannerType="banner.type" 
            class="clear-both mt-2 p-2.5" 
            :data="data" 
            @jumpToIndex="(UlidOfSlide) => jumpToIndex = UlidOfSlide"
            :imagesUploadRoute="imagesUploadRoute" 
            :user="user" 
            :screenView="screenView" 
            :galleryRoute="galleryRoute"
        />
    </div>

    <!-- Section: Add slide if there is not exist -->
    <div v-else>
        <SlidesWorkshopAddMode :data="data" :imagesUploadRoute="imagesUploadRoute" :galleryRoute="galleryRoute"/>
    </div>
</template>