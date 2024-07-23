<script setup lang="ts">
import { faCube, faLink, faStar, faCircle, faChevronDown, faChevronLeft, faChevronRight, faHeart, faSeedling, faHandPaper, faFish, faMedal, faSquare } from "@fortawesome/free-solid-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import InlineInput from '@/Components/Websites/Fields/InlineInput.vue'
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import UploadImage from '@/Components/Pure/UploadImage.vue'
import { ref } from "vue"

import DataSet from '@/Components/Websites/Product/DataSet/Product.js'

library.add(faCube, faLink, faStar, faCircle, faChevronDown, faChevronLeft, faChevronRight, faHeart, faSeedling, faHandPaper, faFish, faMedal, faSquare)

const props = defineProps<{
    modelValue: any
    previewMode : boolean
    isEditing?: boolean
    colorThemed?: Object
}>()

console.log(props)

const selectedProduct = ref(0)
const emits = defineEmits(['update:modelValue', 'autoSave'])
console.log(props)

const dataProduct = ref({
    images: [
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-01.jpg",
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-02.jpg",
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-03.jpg",
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-04.jpg",
    ],
})

</script>

<template>
    <div class="p-5 mx-auto max-w-5xl m-4">
        <div class=" border-gray-400  my-2 p-4">
            <div id="app" class="text-gray-600">
                <div class="grid grid-cols-5 gap-x-10 mb-12">
                    <div class="col-span-3">
                        <div class="font-bold text-lg">
                            <InlineInput v-model="modelValue.data.name"></InlineInput>
                        </div>


                        <div class="mb-1 flex gap-x-10">
                            <div class="text-sm">
                                Product code: {{ modelValue.data.code }}
                            </div>
                            <div class="flex gap-x-[1px] items-center">
                                <FontAwesomeIcon icon="fas fa-star text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star text-[9px] text-gray-600"></FontAwesomeIcon>
                                <span class="ml-1 text-xs">41</span>
                            </div>
                        </div>

                        <div class="mb-1 flex justify-between">
                            <div>
                                <FontAwesomeIcon icon="fas fa-circle" class="text-sm text-green-600"></FontAwesomeIcon>
                                <span class="ml-1 text-sm">(41)</span>
                            </div>
                            <div>
                                RRP: £3.95/Piece
                            </div>
                        </div>

                        <!-- Images product -->
                        <div v-if="modelValue.data.images.length" class="grid grid-cols-5 mb-10 gap-x-2">
                            <div class="flex flex-col gap-y-1.5">
                                <div v-for="(product, idxProduct) in modelValue.data.images"
                                    @click="() => selectedProduct = idxProduct" class="aspect-square cursor-pointer"
                                    :class="selectedProduct == idxProduct ? 'ring-2 ring-gray-400' : 'hover:ring-1 hover:ring-gray-300'">
                                    <img :src="product" alt="">
                                </div>
                            </div>

                            <!-- Image large -->
                            <div class="relative col-span-4 aspect-square">
                                <i
                                    class="absolute bottom-2 right-2 text-3xl far fa-heart text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                                <img :src="dataProduct.images[selectedProduct]" alt="">
                                <div v-if="selectedProduct != 0" @click="() => selectedProduct = selectedProduct - 1"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer">
                                    <i class="fas fa-chevron-left text-2xl"></i>
                                </div>
                                <div v-if="selectedProduct != dataProduct.images.length - 1"
                                    @click="() => selectedProduct = selectedProduct + 1"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer">
                                    <i class="fas fa-chevron-right text-2xl"></i>
                                </div>
                            </div>
                        </div>

                        <div v-else class="mb-10 gap-x-2">
                            <UploadImage  v-model="selectedProduct" :uploadRoutes="{name : '', parameters : ''}"/>
                        </div>

                        <!-- Section: Description -->
                        <div class="space-y-4 mb-6">
                            <div class="text-xs text-gray-500">
                                <Editor  v-model="modelValue.data.description" placeholder="write something ...." />
                            </div>
                            <div class="font-bold text-xs underline">Read More</div>
                        </div>

                        <!-- Section: Label -->
                        <div class="flex gap-x-10 text-gray-400 mb-6">
                            <div class="flex items-center gap-x-1">
                                <FontAwesomeIcon icon="fas fa-seedling " class="text-sm"></FontAwesomeIcon>
                                <div class="text-xs">Vegan</div>
                            </div>
                            <div class="flex items-center gap-x-1">
                                <FontAwesomeIcon icon="fas fa-hand-paper" class="text-sm"></FontAwesomeIcon>
                                <div class="text-xs">Handmade</div>
                            </div>
                            <div class="flex items-center gap-x-1">
                                <FontAwesomeIcon icon="fas fa-fish" class="text-sm"></FontAwesomeIcon>
                                <div class="text-xs">Cruelty Free</div>
                            </div>
                            <div class="flex items-center gap-x-2">
                                <FontAwesomeIcon icon="fas fa-square" class="fa-rotate-by text-sm"
                                    style="--fa-rotate-angle: 45deg;"></FontAwesomeIcon>
                                <div class="text-xs">Plastic Free</div>
                            </div>
                        </div>

                        <div class="mb-2 flex gap-x-4 items-center w-fit cursor-pointer">
                            <div class="font-bold">Product Specification & Documentation</div>
                            <FontAwesomeIcon icon="fas fa-chevron-down " class="text-gray-400"></FontAwesomeIcon>
                        </div>

                        <div class="flex items-center gap-x-4 font-bold cursor-pointer">
                            <div>Customer Reviews </div>
                            <div class="flex gap-x-[1px] items-center">
                                <FontAwesomeIcon icon="fas fa-star" class="text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star" class="text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star" class="text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star" class="text-[9px] text-gray-600"></FontAwesomeIcon>
                                <FontAwesomeIcon icon="fas fa-star" class="text-[9px] text-gray-600"></FontAwesomeIcon>
                                <span class="ml-1 font-normal text-xs">41</span>
                            </div>
                            <FontAwesomeIcon icon="fas fa-chevron-do" class="wn text-gray-400"></FontAwesomeIcon>
                        </div>
                    </div>

                    <!-- Column: Right -->
                    <div class="col-span-2">
                        <div class="mb-2">
                          <!--   <Selector v-model="data.price_non_member">
                                <template #content-area>
                                    <div :style="data.price_non_member">£9.60 (£1.20/Piece)</div>
                                </template>
                            </Selector> -->
                        </div>
                        <div class="mb-2">
                          <!--   <Selector v-model="data.price_member">
                                <template #content-area>
                                    <div :style="data.price_member">£8.00 (£1.00/Piece)</div>
                                </template>
                            </Selector> -->
                        </div>

                        <div>  
                           <!--  <Selector v-model="data.member_price">
                                <template #content-area>
                                    <div class="mb-2 space-x-2">
                                        <FontAwesomeIcon icon="fas fa-medal" :style="{ color : data.member_price.backgroundColor}"></FontAwesomeIcon>
                                        <span class="py-0.5 px-1 rounded-md"  :style="{...data.member_price}">Member Price</span>
                                        <span class="text-xs underline cursor-pointer">Membership Info</span>
                                    </div>
                                </template>
                            </Selector> -->
                         </div>


                        <div class="mb-8">
                            <div class="mb-0.5 text-xs text-gray-500">NOT A MEMBER?</div>
                            <div class="mb-2 text-orange-500 text-xs w-8/12">
                                Order 4 or more outers from this product family to benefit from lower price.
                            </div>
                            <div class="text-xs underline cursor-pointer">Browse Product Family</div>
                        </div>

                        <!-- Section: Order now -->
                        <div class="flex gap-x-2 mb-6">
                            <div class="flex items-start gap-x-1">
                                <div class="font-bold text-3xl leading-none cursor-pointer">-</div>
                                <div
                                    class="h-8 aspect-square border border-gray-400 flex items-center justify-center tabular-nums text-xl font-bold">
                                    1</div>
                                <div class="font-bold text-3xl leading-none cursor-pointer">+</div>
                            </div>
                            <div class="bg-gray-600 text-white rounded px-3 py-1 h-fit w-fit">Order Now</div>
                        </div>

                        <!-- Section: Buy now pay leter, delivery info, return policy -->
                        <div class="mb-4">
                            <div
                                class="flex items-center gap-x-4 border-t border-gray-300 pl-3 font-bold text-gray-600 py-1">
                                Buy Now Pay Later
                                <img src="https://pastpay.com/wp-content/uploads/2023/07/PastPay-logo-dark-edge.png"
                                    class="h-3" alt="">
                                <FontAwesomeIcon icon="fas fa-chevron-right" class=" text-sm text-gray-500">
                                </FontAwesomeIcon>
                            </div>
                            <div
                                class="flex items-center gap-x-4 border-t border-gray-400 pl-3 font-bold text-gray-600 py-1">
                                Delivery Info
                                <FontAwesomeIcon icon="fas fa-chevron-right" class=" text-sm text-gray-500">
                                </FontAwesomeIcon>
                            </div>
                            <div
                                class="flex items-center gap-x-4 border-t border-gray-400 pl-3 font-bold text-gray-600 py-1">
                                Return Policy
                                <FontAwesomeIcon icon="fas fa-chevron-right" class=" text-sm text-gray-500">
                                </FontAwesomeIcon>
                            </div>
                        </div>

                        <!-- Secure Payments: Paypalm, Visa, Mastercard -->
                        <div>
                            <div class="pl-3 font-semibold flex items-center gap-x-2">
                                <div class="text-xs">Secure Payments:</div>
                                <img src="https://i.pinimg.com/736x/21/bc/22/21bc22b0ae1013adeec20aeef47b3369.jpg"
                                    alt="" class="h-6">
                            </div>
                            <div class="mx-auto flex divide-x-2 divide-gray-300 w-fit border-x border-gray-300">
                                <img src="https://e7.pngegg.com/pngimages/292/77/png-clipart-paypal-logo-illustration-paypal-logo-icons-logos-emojis-tech-companies.png"
                                    alt="Paypal" class="px-1 h-4">
                                <img src="https://e7.pngegg.com/pngimages/687/457/png-clipart-visa-credit-card-logo-payment-mastercard-usa-visa-blue-company.png"
                                    alt="Visa" class="px-1 h-4">
                                <img src="https://i.pinimg.com/736x/38/2f/0a/382f0a8cbcec2f9d791702ef4b151443.jpg"
                                    alt="Mastercard" class="px-1 h-4">
                            </div>
                        </div>

                        <!-- Section: FAQ -->
                        <div class="mt-4">
                            <h2 class="mb-4 text">Frequently Asked Questions (FAQs):</h2>
                            <details class="cursor-pointer border-b-2 border-gray-300 py-2 pl-1.5 ">
                                <summary class="flex justify-between font-bold text-sm">
                                    <span>How do they come packaged?</span>
                                    <FontAwesomeIcon icon="fas fa-chevron-down" class=" text-sm text-gray-500">
                                    </FontAwesomeIcon>
                                </summary>
                                <p class="mt-1 text-sm">Details about packaging.</p>
                            </details>

                            <details class="cursor-pointer mt-1 border-b-2 border-gray-300 py-2 pl-1.5 ">
                                <summary class="flex justify-between font-bold text-sm">
                                    <span>Do the bath bombs Fizz or Foam?</span>
                                    <FontAwesomeIcon icon="fas fa-chevron-down " class="text-sm text-gray-500">
                                    </FontAwesomeIcon>
                                </summary>
                                <p class="mt-1 text-sm">Details about Fizz or Foam.</p>
                            </details>

                            <details class="cursor-pointer mt-1 py-2 pl-1.5 ">
                                <summary class="flex justify-between font-bold text-sm">
                                    <span>Are they safe for children?</span>
                                    <FontAwesomeIcon icon="fas fa-chevron-down" class=" text-sm text-gray-500">
                                    </FontAwesomeIcon>
                                </summary>+
                                <p class="mt-1 text-sm">Details about safety for children.</p>
                            </details>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
