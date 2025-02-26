<script setup lang="ts">
import { ref, onMounted, watch } from "vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import SliderLandscape from "@/Components/Banners/Slider/SliderLandscape.vue"
import SliderSquare from "@/Components/Banners/Slider/SliderSquare.vue"
import { Link, router } from "@inertiajs/vue3"

import { faPresentation, faLink, faExternalLink } from "@fal"
import { faSpinnerThird } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import { useFormatTime } from "@/Composables/useFormatTime"
import { getStyles } from "@/Composables/styles"

library.add(faPresentation, faLink, faExternalLink, faSpinnerThird)

const props = defineProps<{
    fieldValue: {
        banner_slug?: string
    }
}>()

const data = ref(null)
const isLoading = ref(false)

const emits = defineEmits<{
    (e: "update:fieldValue", value: string | number): void
    (e: "autoSave"): void
}>()

const getDataBanner = async () => {
    if (!props.fieldValue.banner_slug) return

    isLoading.value = true
    router.get(
        route("iris.banner", { slug: props.fieldValue.banner_slug }),
        {
            preserveScroll: true,
            onSuccess: (response) => {
                data.value = response.data
            },
            onError: () => {
                notify({
                    title: "Something went wrong in Banner.",
                    text: "Failed to save",
                    type: "error",
                })
            },
            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}

onMounted(getDataBanner)

// Watch jika fieldValue berubah
watch(() => props.fieldValue.banner_slug, getDataBanner)

</script>

<template>
    <!-- <div class="relative"
        :style="getStyles(properties)">
        {{ data }}

       <SliderLandscape v-if="data.type == 'landscape'" :data="data.compiled_layout" :production="true" />
        <SliderSquare v-else :data="data.compiled_layout" :production="true" /> 
    </div> -->

    <div v-if="isLoading" class="flex justify-center h-36 items-center">
        <LoadingIcon class="text-4xl" />
    </div>

    <div v-else class="relative" :style="getStyles(properties)">
        <SliderLandscape v-if="data?.type === 'landscape'" :data="data?.compiled_layout" :production="true" />
        <SliderSquare v-else :data="data?.compiled_layout" :production="true" />
    </div>

    {{ data }}

</template>
