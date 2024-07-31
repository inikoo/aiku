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

import { faPresentation, faLink, faExternalLink } from "@fal"
import { faSpinnerThird } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { layoutStructure } from '@/Composables/useLayoutStructure'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import { Link } from "@inertiajs/vue3"

library.add(faPresentation, faLink, faExternalLink, faSpinnerThird)

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
const isLoading = ref(false)
const isLoadingFetching = ref(false)

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()

const onPickBanner = (banner) => {
    emits('update:modelValue', { ...props.modelValue, emptyState: false, banner_id: banner.id, banner_slug: banner.slug })
    emits('autoSave')
    isModalOpen.value = false
}


const getRouteIndex = () => {
    const currentRoute = route().current()
    if (currentRoute.includes('fulfilments')) {
        return route('grp.org.fulfilments.show.web.banners.index', {
            organisation: layout.currentParams.organisation,
            fulfilment: layout.currentParams.fulfilment,
            website: layout.currentParams.website
        });
    } else {
        return route('grp.org.shops.show.web.banners.index', {
            organisation: layout.currentParams.organisation,
            shop: layout.currentParams.shop,
            website: layout.currentParams.website
        });
    }
}

const getRouteShow = () => {
    const currentRoute = route().current()
    if (currentRoute.includes('fulfilments')) {
        return route('grp.org.fulfilments.show.web.banners.show', {
            organisation: layout.currentParams.organisation,
            fulfilment: layout.currentParams.fulfilment,
            website: layout.currentParams.website,
            banner: props.modelValue.banner_slug
        });
    } else {
        return route('grp.org.shops.show.web.banners.show', {
            organisation: layout.currentParams.organisation,
            shop: layout.currentParams.shop,
            website: layout.currentParams.website,
            banner: props.modelValue.banner_slug
        });
    }
}

const getBannersList = async (): Promise<void> => {
    try {
        isLoadingFetching.value = true
        const url = getRouteIndex()
        const response = await axios.get(url, {
            params: {
                'filter[state]': 'live'
            }
        });
        isLoadingFetching.value = false
        bannersList.value = response.data.data;
    } catch (error) {
        console.error(error);
        isLoadingFetching.value = false
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
            isLoading.value = true
            const url = getRouteShow()

            const response = await axios.get(url);
            data.value = response.data
            isLoading.value = false
        } catch (error) {
            console.error(error);
            isLoading.value = false
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

    
    <div v-if="isLoading" class="flex justify-center h-36 items-center">
        <LoadingIcon class="text-4xl"/>
    </div>
    <div v-else-if="!props.modelValue.banner_id && !props.modelValue.banner_slug">
        <div class="flex justify-center border border-dashed border-gray-300 rounded-md py-8">
            <Button label="Select banner" type="tertiary" @click="isModalOpen = true"></Button>
        </div>
    </div>



    <div v-else-if="props.modelValue.banner_id && props.modelValue.banner_slug && data" class="relative">

        <SliderLandscape v-if="data.type == 'landscape'" :data="data.compiled_layout" :production="true" />
        <SliderSquare v-else :data="data.compiled_layout" :production="true" />

        <!-- Icon: Edit -->
        <div class="absolute top-2 right-2 flex space-x-2 z-10">
            <Button :icon="['far', 'fa-pencil']" type="tertiary" size="xs" @click="() => { isModalOpen = true, getBannersList() }" />
        </div>
    </div>





    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
        <div class="h-96">
            <div class="text-center font-semibold text-2xl mb-4">
                {{ trans('Select banners') }}
            </div>
            
            <div v-if="!isLoadingFetching" class="">
                <ul v-if="bannersList.length" role="list" class="flex flex-wrap gap-x-4 gap-y-2.5">
                    <li
                        v-for="banner in bannersList" :key="banner.slug"
                        @click="() => onPickBanner(banner)"
                        class="relative overflow-hidden rounded-lg bg-white shadow cursor-pointer ring-1 ring-gray-300 hover:ring-2 hover:ring-gray-600"
                    >
                        <div class="aspect-[16/9] overflow-hidden h-28 aspect-w-1 w-full" >
                            <img v-if="banner.image_thumbnail" :src="banner.image_thumbnail" class="w-full object-cover object-center group-hover:opacity-75" />
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
                        <div class="py-1">
                            <div class="font-bold text-xs px-2">{{ banner.name }}</div>
                            <div class="text-xxs px-2 text-gray-400 italic">{{ useFormatTime(banner.date) }}</div>
                        </div>
                    </li>
                </ul>

                <div v-else class="mt-24 text-center text-gray-500 text-lg italic">
                    <div class="mb-2">{{ trans('You have no banner yet.') }}</div>
                    <a target="_blank" :href="route('grp.org.shops.show.web.banners.index', [layout.currentParams.organisation, layout.currentParams.shop, layout.currentParams.website])">
                        <Button label="Create banner" iconRight="fal fa-external-link" />
                    </a>
                </div>
            </div>

            <div v-else class="flex justify-center pt-32 items-center">
                <LoadingIcon class="text-6xl" />
            </div>
        </div>
    </Modal>
</template>
