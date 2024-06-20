<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import PureInput from "@/Components/Pure/PureInput.vue"
import { ref } from "vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import Button from '@/Components/Elements/Buttons/Button.vue';

library.add(faCube, faLink)

const props = defineProps<{
    modelValue: any
}>()

const selectedProduct = ref(0)
const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()


</script>

<template>
    <div id="app" class="mx-auto max-w-5xl text-gray-600">
        <div class="grid grid-cols-4 gap-x-10">
            <div class="relative">
                <i class="absolute top-2 right-2 text-2xl far fa-heart text-gray-500"></i>
                <img src="https://tailwindui.com/img/ecommerce-images/product-page-03-related-product-02.jpg" alt=""
                    class="">
                <div class="mb-0.5 font-bold leading-5">
                    8x Monkey Bath Bomb 90g - Guava & Strawberry
                </div>

                <div class="mb-0.5 flex justify-between items-end">
                    <div class="leading-none">SKB-01</div>
                    <div class="text-xs">RRP: £3.95/Piece</div>
                </div>

                <div class="mb-2 flex justify-between">
                    <div class="space-x-2">
                        <i class="fas fa-circle text-sm text-green-600"></i>
                        <span class="text-xs">(41)</span>
                    </div>

                    <div class="flex gap-x-[1px] items-center">
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
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
            <div class="relative">
                <i class="absolute top-2 right-2 text-2xl far fa-heart text-gray-500"></i>
                <img src="https://tailwindui.com/img/ecommerce-images/product-page-03-related-product-01.jpg" alt=""
                    class="">
                <div class="mb-0.5 font-bold leading-5">
                    8x Monkey Bath Bomb 90g - Guava & Strawberry
                </div>

                <div class="mb-0.5 flex justify-between items-end">
                    <div class="leading-none">SKB-01</div>
                    <div class="text-xs">RRP: £3.95/Piece</div>
                </div>

                <div class="mb-2 flex justify-between">
                    <div class="space-x-2">
                        <i class="fas fa-circle text-sm text-green-600"></i>
                        <span class="text-xs">(41)</span>
                    </div>

                    <div class="flex gap-x-[1px] items-center">
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
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
            <div class="relative">
                <i class="absolute top-2 right-2 text-2xl far fa-heart text-gray-500"></i>
                <img src="https://tailwindui.com/img/ecommerce-images/product-page-03-related-product-03.jpg" alt=""
                    class="">
                <div class="mb-0.5 font-bold leading-5">
                    8x Monkey Bath Bomb 90g - Guava & Strawberry
                </div>

                <div class="mb-0.5 flex justify-between items-end">
                    <div class="leading-none">SKB-01</div>
                    <div class="text-xs">RRP: £3.95/Piece</div>
                </div>

                <div class="mb-2 flex justify-between">
                    <div class="space-x-2">
                        <i class="fas fa-circle text-sm text-green-600"></i>
                        <span class="text-xs">(41)</span>
                    </div>

                    <div class="flex gap-x-[1px] items-center">
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
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
            <div class="relative">
                <i class="absolute top-2 right-2 text-2xl far fa-heart text-gray-500"></i>
                <img src="https://tailwindui.com/img/ecommerce-images/product-page-03-related-product-04.jpg" alt="aaa"
                    class="">
                <div class="mb-0.5 font-bold leading-5">
                    8x Monkey Bath Bomb 90g - Guava & Strawberry
                </div>

                <div class="mb-0.5 flex justify-between items-end">
                    <div class="leading-none">SKB-01</div>
                    <div class="text-xs">RRP: £3.95/Piece</div>
                </div>

                <div class="mb-2 flex justify-between">
                    <div class="space-x-2">
                        <i class="fas fa-circle text-sm text-green-600"></i>
                        <span class="text-xs">(41)</span>
                    </div>

                    <div class="flex gap-x-[1px] items-center">
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
                        <i class="fas fa-star text-xs text-gray-600"></i>
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
        </div>
    </div>
</template>