<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { faStar, faCircle } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

library.add(faCube, faLink, faStar, faCircle)

const props = defineProps<{
    modelValue: any
}>()



</script>

<template>
    <div class="border-2 p-4 rounded-lg">
    <img  src="https://tailwindui.com/plus/img/ecommerce-images/product-page-01-product-shot-02.jpg"  alt="" class="h-auto w-full mb-3">
    <div class="my-0.5 font-bold leading-5">
        8x Monkey Bath Bomb 90g - Guava & Strawberry
    </div>

    <div class="mb-0.5 flex justify-between items-end">
        <div class="leading-none">SKB-01</div>
        <div class="text-xs">RRP: £3.95/Piece</div>
    </div>

    <div class="mb-2 flex justify-between">
        <div class="space-x-2">
            <FontAwesomeIcon icon="fas fa-circle" class="text-sm text-green-600"></FontAwesomeIcon>
            <span class="text-xs">(41)</span>
        </div>

        <div class="flex gap-x-[1px] items-center">
            <FontAwesomeIcon icon="fas fa-star" class=" text-xs text-gray-600"></FontAwesomeIcon>
            <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
            <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
            <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
            <FontAwesomeIcon icon="fas fa-star" class="text-xs text-gray-600"></FontAwesomeIcon>
            <span class="ml-1 text-xs">41</span>
        </div>
    </div>

    <div class="mb-2 bg-red-500 text-white px-2 py-0 rounded w-fit font-bold">
        10% OFF
    </div>

    <div class="mb-2 space-y-2">
        <div class="flex justify-between items-end">
            <div class="text-sm font-bold leading-none whitespace-nowrap">
                £9.60 (1.20/Piece)
            </div>
            <div class="text-gray-400 text-[10px] whitespace-nowrap line-through">
                £9.60 (1.20/Piece)
            </div>
        </div>
        <div class="flex justify-between items-end">
            <div class="text-sm text-orange-500 font-bold leading-none whitespace-nowrap">
                £8.00 (1.00/Piece)
            </div>
            <div class="text-gray-400 text-[10px] whitespace-nowrap line-through">
                £8.00 (1.00/Piece)
            </div>
        </div>
    </div>

    <div class="mb-4 bg-orange-500 px-2 py-0.5 rounded w-fit text-xs text-white">
        Member Price
    </div>

    <div class="mx-auto w-fit flex gap-x-2 mb-4">
        <div class="flex items-start gap-x-1">
            <div class="font-bold text-3xl leading-none cursor-pointer">-</div>
            <div
                class="h-8 aspect-square border border-gray-400 flex items-center justify-center tabular-nums text-xl font-bold">
                1</div>
            <div class="font-bold text-3xl leading-none cursor-pointer">+</div>
        </div>
        <div class="bg-gray-600 text-white rounded px-3 py-1 h-fit w-fit">Order Now</div>
    </div>
</div>
</template>