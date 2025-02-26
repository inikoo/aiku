<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 24 Aug 2023 09:33:07 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import SliderLandscape from "@/Components/Banners/Slider/SliderLandscape.vue"
import SliderSquare from "@/Components/Banners/Slider/SliderSquare.vue"

const props = defineProps<{
    data: {
        layout: {
            parameters: {}
            data: {
                delay: number,
                slides: [
                    {
                        imageSrc: string,
                        imageAlt: string,
                        link?: {
                            label: string,
                            target: string
                        }
                    }
                ]
            }
        }
        type: string
    }
    tab?: string
}>()
</script>

<template >
    <div class="w-full">
        <SliderLandscape v-if="data.type == 'landscape'" :data="data" class="h-full" :production="true"/>
        <SliderSquare v-if="data.type == 'square'" :data="data" class="h-full" :production="true"/>
    </div>
</template>