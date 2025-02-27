<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { notify } from "@kyvg/vue3-notification";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faPresentation, faLink, faExternalLink } from "@fal";
import { faSpinnerThird } from "@fad";
import axios from "axios";

import SliderLandscape from "@/Components/Banners/Slider/SliderLandscape.vue";
import SliderSquare from "@/Components/Banners/Slider/SliderSquare.vue";
import EmptyState from "@/Components/Utils/EmptyState.vue";
import { getStyles } from "@/Composables/styles";
import Skeleton from 'primevue/skeleton';

library.add(faPresentation, faLink, faExternalLink, faSpinnerThird);

const props = defineProps<{ fieldValue: { banner_slug?: string } }>();

const data = ref(null);
const isLoading = ref(false);

const getRouteShow = () => {
    const params = route().params;
    console.log(params);
    if (params.isInWorkshop) {
        if (params.fulfilment) {
            return route("grp.org.fulfilments.show.web.banners.show", {
                organisation: params?.organisation,
                fulfilment: params?.fulfilment,
                website: params?.website,
                banner: props.fieldValue.banner_slug,
            });
        } else if (params.shop) {
            return route("grp.org.shops.show.web.banners.show", {
                organisation: params?.organisation,
                shop: params?.shop,
                website: params?.website,
                banner: props.fieldValue.banner_slug,
            });
        }
    } else {
        return route('iris.banners.deliver', { banner: props.fieldValue.banner_slug });
    }
};

const getDataBanner = async () => {
    if (props.fieldValue.banner_slug) {
        try {
            isLoading.value = true;
            const url = getRouteShow();

            const response = await axios.get(url);
            data.value = response.data;
        } catch (error) {
            console.error(error);
            notify({
                title: "Failed to fetch banners data",
                text: error.message || 'An error occurred',
                type: "error",
            });
        } finally {
            isLoading.value = false;
        }
    }
};

onMounted(getDataBanner);
watch(() => props.fieldValue.banner_slug, getDataBanner);
</script>

<template>
  <div v-if="isLoading" class="h-36 flex flex-col space-y-2">
    <Skeleton width="100%" height="335px" />
  </div>
  <div v-else-if="data?.type" class="relative" :style="getStyles(fieldValue?.container?.properties)">
    <SliderLandscape v-if="data.type === 'landscape'" :data="data.compiled_layout" :production="true" />
    <SliderSquare v-else :data="data.compiled_layout" :production="true" />
  </div>
  <div v-else class="relative" :style="getStyles(fieldValue?.container?.properties)">
    <EmptyState />
  </div>
</template>