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

import { faPresentation, faLink, faExternalLink } from "@fal"
import { faSpinnerThird } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
import { layoutStructure } from '@/Composables/useLayoutStructure'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import { getStyles } from "@/Composables/styles"

library.add(faPresentation, faLink, faExternalLink, faSpinnerThird)

const props = defineProps<{
    modelValue: any
    webpageData?: any
    web_block: Object
    id: Number,
    type: String
    properties: {}
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

const getRouteIndex = () => {
    const currentRoute = route().current()
    if (currentRoute.includes('fulfilments')) {
        return route('grp.org.fulfilments.show.web.banners.index', {
            organisation: route().params['organisation'],
            fulfilment: route().params['fulfilment'],
            website: route().params['website'],
        });
    } else {
        return route('grp.org.shops.show.web.banners.index', {
            organisation: route().params['organisation'],
            shop: route().params['shop'],
            website: route().params['website'],
        });
    }
}

const getRouteShow = () => {
    const currentRoute = route().current()
    if (currentRoute.includes('fulfilments')) {
        return route('grp.org.fulfilments.show.web.banners.show', {
            organisation: route().params['organisation'],
            fulfilment: route().params['fulfilment'],
            website: route().params['website'],
            banner: props.modelValue.banner_slug
        });
    } else {
        return route('grp.org.shops.show.web.banners.show', {
            organisation: route().params['organisation'],
            shop: route().params['shop'],
            website: route().params['website'],
            banner: props.modelValue.banner_slug
        });
    }
}

const getBannersList = async (): Promise<void> => {
    try {
        isLoadingFetching.value = true
        const url = getRouteIndex()
        console.log(getRouteIndex())
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

console.log(route().params)
</script>

<template>




    <div class="relative"
        :style="getStyles(properties)">

        <SliderLandscape v-if="data.type == 'landscape'" :data="data.compiled_layout" :production="true" />
        <SliderSquare v-else :data="data.compiled_layout" :production="true" />

    </div>

</template>
