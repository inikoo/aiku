<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'

import Button from '@/Components/Elements/Buttons/Button.vue';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"


import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faShieldAlt } from "@fas"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons";

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt )

const props = defineProps<{
    modelValue: object,
    loginMode: Boolean
    keyTemplate: String
}>();

const message = ref('Hello Vue!')
const dataProduct = ref()
const toogle = ['bold', 'italic', 'underline', 'link', 'undo', 'redo']


console.log(props)

</script>

<template>
    <div id="app" class="py-24 bg-gray-900 text-gray-100 md:px-7">
        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 md:gap-8">
                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <!-- Help -->
                    <section v-for="item in modelValue.column['column_1']['data']">
                        <div class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                            <h2 class="text-xl font-semibold w-fit">
                                <Editor v-model="item.name" :toogle="toogle" />
                            </h2>
                            <ul v-for="sub in item['data']" class="hidden md:block space-y-1">
                                <li><a href="#" class="text-sm block">
                                        <Editor v-model="sub.name" :toogle="toogle" />
                                    </a></li>
                            </ul>
                        </div>
                    </section>
                </div>

                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <section v-for="item in modelValue.column['column_2']['data']">
                        <div class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                            <h2 class="text-xl font-semibold w-fit">
                                <Editor v-model="item.name" :toogle="toogle" />
                            </h2>
                            <ul v-for="sub in item['data']" class="hidden md:block space-y-1">
                                <li><a href="#" class="text-sm block">
                                        <Editor v-model="sub.name" :toogle="toogle" />
                                    </a></li>
                            </ul>
                        </div>
                    </section>
                </div>

                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <section v-for="item in modelValue.column['column_3']['data']">
                        <div class="cursor-pointer md:cursor-default space-y-1 border-b pb-2 md:border-none">
                            <h2 class="text-xl font-semibold w-fit">
                                <Editor v-model="item.name" :toogle="toogle" />
                            </h2>
                            <ul v-for="sub in item['data']" class="hidden md:block space-y-1">
                                <li><a href="#" class="text-sm block">
                                        <Editor v-model="sub.name" :toogle="toogle" />
                                    </a></li>
                            </ul>
                        </div>
                    </section>
                </div>


                <!-- Section: Get Social With Us -->
                <div
                    class="md:hidden mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                    <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                    <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                        <a href="#"><font-awesome-icon :icon="['fab', 'facebook-f']" class="text-2xl" /></a>
                        <a href="#"><font-awesome-icon icon="fab fa-instagram" class="text-2xl"></font-awesome-icon></a>
                        <a href="#"><font-awesome-icon icon="fab fa-tiktok" class="text-2xl"></font-awesome-icon></a>
                        <a href="#"><font-awesome-icon icon="fab fa-pinterest" class="text-2xl"></font-awesome-icon></a>
                        <a href="#"><font-awesome-icon icon="fab fa-youtube" class="text-2xl"></font-awesome-icon></a>
                        <a href="#"><font-awesome-icon icon="fab fa-linkedin-in"
                                class="text-2xl"></font-awesome-icon></a>
                    </div>
                </div>

                <div class="flex flex-col flex-col-reverse gap-y-6 md:block">
                    <div>
                       <!--  <div class="text-center space-y-2 md:space-y-0">
                            <FontAwesomeIcon icon="fas fa-shield-alt" class="text-3xl"></FontAwesomeIcon>
                            <div class="flex justify-center items-center gap-x-3 md:inline-block">
                                <div class="text-sm md:text-base">Secure Payments</div>
                                <img src="https://www.linqto.com/wp-content/uploads/2023/04/logo_2021-11-05_19-04-11.530.png"
                                    alt="" class="h-5 md:mb-4">
                            </div>
                        </div> -->
                            <div class="flex flex-wrap -mx-4">
                                <div v-for="payment in modelValue.PaymentData.data" :key="payment.key" class="w-full md:w-1/3 px-4 mb-8">
                                    <div class="flex items-center justify-center md:justify-start space-x-4">
                                        <img :src="payment.image" :alt="payment.key" class="px-1 h-4">
                                    </div>
                                </div>
                            </div>
                        

                        <address class="mt-10 md:mt-0 not-italic mb-4 text-center md:text-left text-xs md:text-sm text-gray-300">
                            <Editor v-model="modelValue.column.column_4.data.textBox1" toggle="toggle" />
                        </address>

                        <div class="flex justify-center gap-x-8 text-gray-300 md:block">
                            <Editor v-model="modelValue.column.column_4.data.textBox2" toggle="toggle" />
                        </div>
                        <!-- Section: Get Social With Us -->
                        <div
                            class="hidden md:block mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                            <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                            <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                                <a href="#"><font-awesome-icon :icon="['fab', 'facebook-f']" class="text-2xl" /></a>
                                <a href="#"><font-awesome-icon icon="fab fa-instagram"
                                        class="text-2xl"></font-awesome-icon></a>
                                <a href="#"><font-awesome-icon icon="fab fa-tiktok"
                                        class="text-2xl"></font-awesome-icon></a>
                                <a href="#"><font-awesome-icon icon="fab fa-pinterest"
                                        class="text-2xl"></font-awesome-icon></a>
                                <a href="#"><font-awesome-icon icon="fab fa-youtube"
                                        class="text-2xl"></font-awesome-icon></a>
                                <a href="#"><font-awesome-icon icon="fab fa-linkedin-in"
                                        class="text-2xl"></font-awesome-icon></a>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-b border-gray-500 md:border-none flex items-center space-x-2 px-5 pb-4 md:pb-0 md:px-0">
                        <i class="text-4xl md:text-3xl fab fa-whatsapp text-green-500"></i>
                        <span class="w-10/12 md:w-full md:text-sm"> <Editor v-model="modelValue.column.column_4.data.textBox3" toggle="toggle" /></span>
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
