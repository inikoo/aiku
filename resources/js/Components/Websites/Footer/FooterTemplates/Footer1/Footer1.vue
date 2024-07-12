<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'

import Button from '@/Components/Elements/Buttons/Button.vue';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage } from '@fas';
import { faHeart } from '@far';

library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle)

const props = defineProps<{
    modelValue: object,
    loginMode: Boolean
    keyTemplate: String
}>();

const message = ref('Hello Vue!')
const dataProduct = ref()

const toggleChildClass = (element) => {
    element.currentTarget.querySelector('ul').classList.toggle('hidden')
}


</script>

<template>
    <div id="app" class="py-24 bg-gray-900 text-gray-100 md:px-7">
        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 md:gap-8">
                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <!-- Help -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold w-fit">Help</h2>
                        <ul class="hidden md:block space-y-1">
                            <li><a href="#" class="text-sm block">Contact Us</a></li>
                            <li><a href="#" class="text-sm block">Delivery</a></li>
                            <li><a href="#" class="text-sm block">Returns Policy</a></li>
                        </ul>
                    </div>

                    <!-- About AW -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">About AW</h2>
                        <ul class="hidden md:block space-y-1">
                            <li><a href="#" class="text-sm block">AW Beginning</a></li>
                            <li><a href="#" class="text-sm block">Business Ethics</a></li>
                            <li><a href="#" class="text-sm block">Our Brands</a></li>
                            <li><a href="#" class="text-sm block">Subscribe to the Newsletter</a></li>
                        </ul>
                    </div>

                    <!-- Showroom -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">Showroom</h2>
                        <ul class="hidden md:block space-y-1">
                            <li><a href="#" class="text-sm block">Book Showroom Appointment</a></li>
                        </ul>
                    </div>

                    <!-- Customer Service -->
                    <div class="hidden md:block md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">Customer Service</h2>
                        <ul class="block space-y-3 md:space-y-1 text-xs md:text-sm">
                            <li class=""><i class="fas fa-phone-alt mr-2"></i>+44 (0) 1142 729 165</li>
                            <li><i class="fas fa-envelope mr-2"></i>care@ancientwisdom.biz</li>
                            <li class="md:hidden">Monday to Friday: 08:00 - 16.30 GMT</li>
                        </ul>
                    </div>

                    <!-- Reviews & FAQ -->
                    <div class="space-y-2 md:space-y-1">
                        <h2 class="text-xl font-semibold border-b pb-2 md:border-none">Reviews</h2>
                        <h2 class="text-xl font-semibold border-b pb-2 md:border-none">FAQ</h2>
                    </div>
                </div>

                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <!-- Why Choose AW? -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">Why Choose AW?</h2>
                        <ul class="hidden md:block space-y-1">
                            <li><a href="#" class="text-sm block">No Minimum Order</a></li>
                            <li><a href="#" class="text-sm block">First Order Bonus</a></li>
                            <li><a href="#" class="text-sm block">Buy Noy, Pay Later</a></li>
                            <li><a href="#" class="text-sm block">Volume Discounts</a></li>
                        </ul>
                    </div>

                    <!-- Membership -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">Membership</h2>
                        <ul class="hidden md:block space-y-1">
                            <li><a href="#" class="text-sm block">Join The Gold Reward Membership</a></li>
                        </ul>
                    </div>

                    <!-- Discover -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">Discover</h2>
                        <ul class="space-y-1 hidden md:block">
                            <li><a href="#" class="text-sm block">Catalogue - Live Stock Feeds</a></li>
                            <li><a href="#" class="text-sm block">AW History & The Phoenix Effect</a></li>
                            <li><a href="#" class="text-sm block">Distribution Centrein Europe</a></li>
                            <li><a href="#" class="text-sm block">David's Travel Blog</a></li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">Legal</h2>
                        <ul class="space-y-1 hidden md:block">
                            <li><a href="#" class="text-sm block">Terms & Condition</a></li>
                            <li><a href="#" class="text-sm block">Privacy Policy</a></li>
                            <li><a href="#" class="text-sm block">Cookies Policy</a></li>
                        </ul>
                    </div>
                </div>

                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <!-- Our Services -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">Our Services</h2>
                        <ul class="space-y-1 hidden md:block">
                            <li><a href="#" class="text-sm block">DROPSHIPPING</a></li>
                            <li><a href="#" class="text-sm block">FULFILMENT</a></li>
                            <li><a href="#" class="text-sm block">DIGITAL MARKETING</a></li>
                        </ul>
                    </div>

                    <!-- AW Partners -->
                    <div @click="(e) => toggleChildClass(e)"
                        class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                        <h2 class="text-xl font-semibold">AW Partners</h2>
                        <ul class="space-y-1 hidden md:block">
                            <li><a href="#" class="text-sm block">Agnes + Cat</a></li>
                            <li><a href="#" class="text-sm block">AW - Aromatics</a></li>
                            <li><a href="#" class="text-sm block">AW - Portugal</a></li>
                            <li><a href="#" class="text-sm block">AW - Germany</a></li>
                            <li><a href="#" class="text-sm block">AW - Hungary</a></li>
                            <li><a href="#" class="text-sm block">AW - Slovakia</a></li>
                            <li><a href="#" class="text-sm block">AW - España</a></li>
                            <li><a href="#" class="text-sm block">AW - France</a></li>
                            <li><a href="#" class="text-sm block">AW - Poland</a></li>
                            <li><a href="#" class="text-sm block">AW - Austria</a></li>
                            <li><a href="#" class="text-sm block">AW - Europe</a></li>
                            <li><a href="#" class="text-sm block">AW - Romania</a></li>
                            <li><a href="#" class="text-sm block">AW - Czechia</a></li>
                            <li><a href="#" class="text-sm block">AW - Italy</a></li>
                        </ul>
                    </div>

                    <!-- Customer Service -->
                    <div class="md:hidden md:cursor-default space-y-1 pb-2">
                        <h2 class="text-xl font-semibold">Customer Service</h2>
                        <ul class="block space-y-3 md:space-y-1 text-xs md:text-sm">
                            <li class=""><i class="fas fa-phone-alt mr-2"></i>+44 (0) 1142 729 165</li>
                            <li><i class="fas fa-envelope mr-2"></i>care@ancientwisdom.biz</li>
                            <li class="md:hidden">Monday to Friday: 08:00 - 16.30 GMT</li>
                        </ul>
                    </div>
                </div>


                <!-- Section: Get Social With Us -->
                <div
                    class="md:hidden mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                    <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                    <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                        <a href="#"><i class="text-2xl fab fa-facebook-f"></i></a>
                        <a href="#"><i class="text-2xl fab fa-instagram"></i></a>
                        <a href="#"><i class="text-2xl fab fa-tiktok"></i></a>
                        <a href="#"><i class="text-2xl fab fa-pinterest"></i></a>
                        <a href="#"><i class="text-2xl fab fa-youtube"></i></a>
                        <a href="#"><i class="text-2xl fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="flex flex-col flex-col-reverse gap-y-6 md:block">
                    <div>
                        <div class="text-center space-y-2 md:space-y-0">
                            <i class="fas fa-shield-alt text-3xl"></i>
                            <div class="flex justify-center items-center gap-x-3 md:inline-block">
                                <div class="text-sm md:text-base">Secure Payments</div>
                                <img src="https://www.linqto.com/wp-content/uploads/2023/04/logo_2021-11-05_19-04-11.530.png"
                                    alt="" class="h-5 md:mb-4">
                            </div>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-4 mt-6 md:mt-0 mb-3">
                            <img src="https://e7.pngegg.com/pngimages/687/457/png-clipart-visa-credit-card-logo-payment-mastercard-usa-visa-blue-company.png"
                                alt="Visa" class="px-1 h-4">
                            <img src="https://e7.pngegg.com/pngimages/292/77/png-clipart-paypal-logo-illustration-paypal-logo-icons-logos-emojis-tech-companies.png"
                                alt="Paypal" class="px-1 h-4">
                            <img src="https://i.pinimg.com/736x/38/2f/0a/382f0a8cbcec2f9d791702ef4b151443.jpg"
                                alt="Mastercard" class="px-1 h-4">
                            <img src="https://pastpay.com/wp-content/uploads/2023/07/PastPay-logo-dark-edge.png"
                                class="md:hidden h-5" alt="">
                        </div>
                        <img src="https://pastpay.com/wp-content/uploads/2023/07/PastPay-logo-dark-edge.png"
                            class="hidden md:block h-5 mb-5" alt="">
                        <address
                            class="mt-10 md:mt-0 not-italic mb-4 text-center md:text-left text-xs md:text-sm text-gray-300">
                            Ancient Wisdom Marketing Ltd.<br class="hidden md:block">
                            Affinity Park, Europa Drive<br>
                            Sheffield, S9 1XT
                        </address>
                        <div class="flex justify-center gap-x-8 text-gray-300 md:block">
                            <div class="text-xs md:text-sm">Vat No: GB764298589</div>
                            <div class="text-xs md:text-sm mb-4 md:mb-0">Reg. No: 04108870</div>
                        </div>
                        <!-- Section: Get Social With Us -->
                        <div
                            class="hidden md:block mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                            <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                            <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                                <a href="#"><i class="text-2xl fab fa-facebook-f"></i></a>
                                <a href="#"><i class="text-2xl fab fa-instagram"></i></a>
                                <a href="#"><i class="text-2xl fab fa-tiktok"></i></a>
                                <a href="#"><i class="text-2xl fab fa-pinterest"></i></a>
                                <a href="#"><i class="text-2xl fab fa-youtube"></i></a>
                                <a href="#"><i class="text-2xl fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-b border-gray-500 md:border-none flex items-center space-x-2 px-5 pb-4 md:pb-0 md:px-0">
                        <i class="text-4xl md:text-3xl fab fa-whatsapp text-green-500"></i>
                        <span class="w-10/12 md:w-full md:text-sm">Subscribe to the WhatsApp messages and benefit from
                            exclusive discounts.</span>
                    </div>
                </div>
            </div>

            <div
                class="bg-[#9c7c64] md:bg-transparent text-[10px] md:text-base border-t border-gray-700 mt-8 pb-2 pt-2 md:pb-0 md:pt-4 text-center text-gray-800 md:text-[#d1d5db]">
                Copyright © 2024 Aurora. All rights reserved. <a href="#" class="hover:underline">Terms of Use</a> | <a
                    href="#" class="hover:underline">Privacy Policy</a>
            </div>
        </div>
        </div>
</template>


<style scss></style>
