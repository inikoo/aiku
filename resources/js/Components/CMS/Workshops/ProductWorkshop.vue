<script setup lang="ts">
import { faCube, faLink, faStar, faCircle, faChevronDown, faChevronLeft, faChevronRight, faHeart, faSeedling, faHandPaper, faFish, faMedal, faSquare } from "@fortawesome/free-solid-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import { ref } from "vue"

library.add(faCube, faLink, faStar, faCircle, faChevronDown, faChevronLeft, faChevronRight, faHeart, faSeedling, faHandPaper, faFish, faMedal, faSquare)

const selectedProduct = ref(0)
const emits = defineEmits(['update:modelValue', 'autoSave'])



const dataProduct = ref({
    images: [
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-01.jpg",
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-02.jpg",
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-03.jpg",
        "https://tailwindui.com/img/ecommerce-images/product-page-03-product-04.jpg",
    ],
})

const v = ref(1)
const option = [
    { label: "halo", value: 1 },
    { label: "halo2", value: 2 },
    { label: "halo3", value: 3 },
]
</script>

<template>
    <div class="p-5 mx-auto max-w-5xl m-4">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <PureMultiselect v-model="v" :options="option" label="label" valueProp="value" mode="single"
                    :object="true" />
            </div>
            <!-- ... -->
            <div></div>
        </div>

        <div class="border-2 border-gray-400 border-dashed my-2 p-4">
            <div id="app" class="text-gray-600">
                <div class="grid grid-cols-5 gap-x-10 mb-12">
                    <div class="col-span-3">
                        <div class="font-bold text-2xl">
                            8x Monkey Bath Bomb 90g - Guava & Strawberry
                        </div>

                        <div class="mb-1 flex gap-x-10">
                            <div class="text-sm">
                                Product code: BKB-07
                            </div>
                            <div class="flex gap-x-[1px] items-center">
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <span class="ml-1 text-xs">41</span>
                            </div>
                        </div>

                        <div class="mb-1 flex justify-between">
                            <div>
                                <i class="fas fa-circle text-sm text-green-600"></i>
                                <span class="ml-1 text-sm">(41)</span>
                            </div>
                            <div>
                                RRP: £3.95/Piece
                            </div>
                        </div>
                        <!-- Images product -->
                        <div class="grid grid-cols-5 mb-10 gap-x-2">
                            <div class="flex flex-col gap-y-1.5">
                                <div v-for="(product, idxProduct) in dataProduct.images"
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

                        <!-- Section: Description -->
                        <div class="space-y-4 mb-6">
                            <div class="text-xs text-gray-500">
                                Introducing our <span class="font-bold">Wholesale Monkey Shaped Bath Bomb</span>
                                weighing 90g
                                and infused with the delightful fusion of
                                Guava & Strawberry. This bulk package is perfect for retailers, spas, or anyone looking
                                to add a
                                touch of fun and tropical fragrance to their bath and body products.
                                <br>
                                Each bath bomb is meticulously crafted
                                into an adorable monkey shape, bringing a sense of whimsy and playfulness to any bathing
                                routine. The attention to detail and vibrant colours make these bath bombs visually
                                appealing and
                                irresistible to customers of all ages.
                            </div>
                            <div class="font-bold text-xs underline">Read More</div>
                        </div>

                        <!-- Section: Label -->
                        <div class="flex gap-x-10 text-gray-400 mb-6">
                            <div class="flex items-center gap-x-1">
                                <i class="fas fa-seedling text-sm"></i>
                                <div class="text-xs">Vegan</div>
                            </div>
                            <div class="flex items-center gap-x-1">
                                <i class="fas fa-hand-paper text-sm"></i>
                                <div class="text-xs">Handmade</div>
                            </div>
                            <div class="flex items-center gap-x-1">
                                <i class="fas fa-fish text-sm"></i>
                                <div class="text-xs">Cruelty Free</div>
                            </div>
                            <div class="flex items-center gap-x-2">
                                <i class="fas fa-square fa-rotate-by text-sm" style="--fa-rotate-angle: 45deg;"></i>
                                <div class="text-xs">Plastic Free</div>
                            </div>
                        </div>

                        <div class="mb-2 flex gap-x-4 items-center w-fit cursor-pointer">
                            <div class="font-bold">Product Specification & Documentation</div>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>

                        <div class="flex items-center gap-x-4 font-bold cursor-pointer">
                            <div>Customer Reviews </div>
                            <div class="flex gap-x-[1px] items-center">
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <i class="fas fa-star text-[9px] text-gray-600"></i>
                                <span class="ml-1 font-normal text-xs">41</span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Column: Right -->
                    <div class="col-span-2">
                        <div class="mb-2 font-semibold text-2xl">
                            £9.60 (£1.20/Piece)
                        </div>
                        <div class="mb-2 font-semibold text-2xl text-orange-500">
                            £8.00 (£1.00/Piece)
                        </div>

                        <div class="mb-2 space-x-2">
                            <i class="fas fa-medal text-orange-500"></i>
                            <span class="bg-orange-500 text-white py-0.5 px-1 rounded-md">Member Price</span>
                            <span class="text-xs underline cursor-pointer">Membership Info</span>
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
                                <i class="fas fa-chevron-right text-sm text-gray-500"></i>
                            </div>
                            <div
                                class="flex items-center gap-x-4 border-t border-gray-400 pl-3 font-bold text-gray-600 py-1">
                                Delivery Info
                                <i class="fas fa-chevron-right text-sm text-gray-500"></i>
                            </div>
                            <div
                                class="flex items-center gap-x-4 border-t border-gray-400 pl-3 font-bold text-gray-600 py-1">
                                Return Policy
                                <i class="fas fa-chevron-right text-sm text-gray-500"></i>
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
                                    <i class="fas fa-chevron-down text-sm text-gray-500"></i>
                                </summary>
                                <p class="mt-1 text-sm">Details about packaging.</p>
                            </details>

                            <details class="cursor-pointer mt-1 border-b-2 border-gray-300 py-2 pl-1.5 ">
                                <summary class="flex justify-between font-bold text-sm">
                                    <span>Do the bath bombs Fizz or Foam?</span>
                                    <i class="fas fa-chevron-down text-sm text-gray-500"></i>
                                </summary>
                                <p class="mt-1 text-sm">Details about Fizz or Foam.</p>
                            </details>

                            <details class="cursor-pointer mt-1 py-2 pl-1.5 ">
                                <summary class="flex justify-between font-bold text-sm">
                                    <span>Are they safe for children?</span>
                                    <i class="fas fa-chevron-down text-sm text-gray-500"></i>
                                </summary>
                                <p class="mt-1 text-sm">Details about safety for children.</p>
                            </details>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
