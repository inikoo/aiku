<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, onMounted, inject, watch } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { trans } from "laravel-vue-i18n"
import SliderLandscape from "@/Components/Banners/Slider/SliderLandscape.vue"
import SliderSquare from "@/Components/Banners/Slider/SliderSquare.vue"
import Popover from '@/Components/Popover.vue'
import PureInput from "@/Components/Pure/PureInput.vue"
import InputUseOption from "@/Components/Pure/InputUseOption.vue"

import { faPresentation, faLink } from "@fal"
import { faSpinnerThird } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { layoutStructure } from '@/Composables/useLayoutStructure'

library.add(faPresentation, faLink, faSpinnerThird)

const props = defineProps<{
    modelValue: any
    webpageData: any
    web_block: Object
    id: Number,
    type: String
}>()

const layout = inject('layout', layoutStructure)
const bannersList = ref([])
const isModalOpen = ref(false)
const data = ref(null)
const loading = ref(false)

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()

const onPickBanner = (banner) => {
    emits('update:modelValue', { ...props.modelValue, emptyState: false, banner_id: banner.id, banner_slug: banner.slug })
    emits('autoSave')
    isModalOpen.value = false
}

const getBannersList = async (): Promise<void> => {
    try {
        loading.value = true
        const url = route('grp.org.shops.show.web.banners.index', {
            organisation: layout.currentParams.organisation,
            shop: layout.currentParams.shop,
            website: layout.currentParams.website
        });

        const response = await axios.get(url, {
            params: {
                'filter[state]': 'live'
            }
        });
        loading.value = false
        bannersList.value = response.data.data;
    } catch (error) {
        console.error(error);
        loading.value = false
        notify({
            title: "Failed to fetch banners data",
            text: error.message || 'An error occurred',
            type: "error",
        });
    }
};

const getDataBanner = async (): Promise<void> => {
    if (props.modelValue.banner_slug) {
        try {
            loading.value = true
            const url = route('grp.org.shops.show.web.banners.show', {
                organisation: layout.currentParams.organisation,
                shop: layout.currentParams.shop,
                website: layout.currentParams.website,
                banner: props.modelValue.banner_slug
            });

            const response = await axios.get(url);
            data.value = response.data
            loading.value = false
        } catch (error) {
            console.error(error);
            loading.value = false
            notify({
                title: "Failed to fetch banners data",
                text: error.message || 'An error occurred',
                type: "error",
            });
        }
    }
};

// Menggunakan watch untuk memanggil getDataBanner saat modelValue berubah
watch(() => props.modelValue, (newValue, oldValue) => {
    if (newValue.banner_slug !== oldValue.banner_slug) {
        getDataBanner();
    }
});

onMounted(() => {
    if (props.modelValue.banner_slug && props.modelValue.banner_id) getDataBanner()
    else getBannersList()
})
</script>

<template>
    <div v-if="!props.modelValue.banner_id && !props.modelValue.banner_slug && !loading">
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 p-5">
            <li v-for="banner in bannersList.slice(0, 6)" :key="banner.slug"
                class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                <div class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow aspect-h-1 h-28 aspect-w-1 w-full bg-gray-200"
                    @click="() => onPickBanner(banner)">
                    <img v-if="banner['image_thumbnail']" :src="banner['image_thumbnail']"
                        class="w-full object-cover object-center group-hover:opacity-75" />
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                        <defs>
                            <pattern id="pattern_mQij" patternUnits="userSpaceOnUse" width="13" height="13"
                                patternTransform="rotate(45)">
                                <line x1="0" y="0" x2="0" y2="13" stroke="#CCCCCC" stroke-width="12" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#pattern_mQij)" opacity="0.4" />
                    </svg>
                </div>
                <span class="font-bold text-xs">{{ banner.name }}</span>
            </li>
        </ul>
        <div class="flex justify-center">
            <Button label="Load More" type="secondary" @click="isModalOpen = true"></Button>
        </div>
    </div>

    <div v-if="props.modelValue.banner_id && props.modelValue.banner_slug && !loading && data" class="relative">

        <SliderLandscape v-if="data.type == 'landscape'" :data="data.compiled_layout" :production="true" />
        <SliderSquare v-else :data="data.compiled_layout" :production="true" />

        <div class="absolute top-2 right-2 flex space-x-2 z-10">
            <Button :icon="['far', 'fa-pencil']" size="xs" @click="() => { isModalOpen = true, getBannersList() }" />
            <!-- <Popover width="w-full" class="relative h-full">
        <template #button>
            <Button :icon="['far', 'fa-pencil']" size="xs" />
        </template>
<template #content="{ close: closed }">
                    <div class="w-[350px]">
                    <div class="mx-auto grid grid-cols-2 gap-4">
                        <div class="mb-1 ">
                            <span class="text-xs text-gray-500 pb-3">Height</span>
                            <InputUseOption
                                 v-model="modelValue.height" 
                                 :option="optionWidthHeight"
                                 @update:model-value="onEnter('b')"
                                 :MultiSelectProps="{
                                    label : 'label',
                                    valueProp : 'value', 
                                    placeholder : ''   
                                 }"
                                 />
                       </div>
                       <div class="mb-1">
                            <span class="text-xs text-gray-500 pb-3">Width</span>
                            <InputUseOption
                                 v-model="modelValue.width" 
                                 :option="optionWidthHeight"
                                 @update:model-value="onEnter('c')"
                                 :MultiSelectProps="{
                                    label : 'label',
                                    valueProp : 'value', 
                                    placeholder : ''   
                                 }"
                                 />
                       </div>
                    </div>
                    
                       <div class="mb-1">
                            <span class="text-xs text-gray-500 pb-3">Link</span>
                            <PureInput v-model="modelValue.link"></PureInput>
                       </div>
                       
                    </div>
                </template>
</Popover> -->
        </div>
    </div>


    <div v-if="loading" class="flex justify-center p-24">
        <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin' fixed-width aria-hidden="true" />
    </div>



    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
        <div class="text-center font-semibold text-2xl mb-4">
            {{ trans('Banners') }}
        </div>
        <div v-if="!loading">
            <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 p-5">
                <li v-for="banner in bannersList" :key="banner.slug"
                    class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
                    <div class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow aspect-h-1 h-28 aspect-w-1 w-full bg-gray-200"
                        @click="() => onPickBanner(banner)">
                        <img v-if="banner['image_thumbnail']" :src="banner['image_thumbnail']"
                            class="w-full object-cover object-center group-hover:opacity-75" />
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            <defs>
                                <pattern id="pattern_mQij" patternUnits="userSpaceOnUse" width="13" height="13"
                                    patternTransform="rotate(45)">
                                    <line x1="0" y="0" x2="0" y2="13" stroke="#CCCCCC" stroke-width="12" />
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#pattern_mQij)" opacity="0.4" />
                        </svg>
                    </div>
                    <span class="font-bold text-xs">{{ banner.name }}</span>
                </li>
            </ul>
        </div>
        <div v-else class="flex justify-center p-24">
            <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin' fixed-width aria-hidden="true" />
        </div>
    </Modal>
</template>
