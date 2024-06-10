<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Button from '@/Components/Elements/Buttons/Button.vue';

library.add(faCube, faLink)

const props = defineProps<{
    modelValue: any
}>()


</script>

<template>
    <div class="w-full bg-gray-600 py-4 px-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="col-span-2 relative">
                <img src="https://tailwindui.com/img/ecommerce-images/home-page-01-hero-full-width.jpg"
                    alt="Informative Image" class="w-full h-full object-cover rounded-md">
                <!-- <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                    <i class="fas fa-play text-white/90 text-6xl"></i>
                </div> -->
            </div>

            <div class="flex flex-col px-14 pt-14">
                <h2 class="text-lg font-bold text-white mb-10">What ingredients should be considered when selecting body
                    and hand soaps?</h2>
                <p class="text-[9px] text-white mb-14">Provide your customers with informed advice.</p>
                <button class="place-self-center bg-white text-gray-800 py-2 px-8 w-max">Read The Blog</button>
            </div>
        </div>
    </div>

</template>