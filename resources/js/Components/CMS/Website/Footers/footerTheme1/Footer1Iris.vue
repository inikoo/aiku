<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { getStyles } from '@/Composables/styles'

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faShieldAlt, faPlus, faTrash, faAngleUp, faAngleDown } from "@fas"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons"
import { faBars } from '@fal'

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faBars, faPlus, faTrash)

const props = defineProps<{
    modelValue: {
        container: {
            properties: {}
        }
        column: {
            column_1: {
                data: {
                    data: {
                        name: string
                    }[]
                    name: string
                }[]
            }
            column_2: {
                data: {
                    data: {
                        name: string
                    }[]
                    name: string
                }[]
            }
            column_3: {
                data: {
                    data: {
                        name: string
                    }[]
                    name: string
                }[]
            }
            column_4: {
                data: {
                    data: {
                        name: string
                    }[]
                    name: string
                    textBox1: string
                    textBox2: string
                    textBox3: string
                }
            }
        }
        socialData: {
            link: string
            icon: string
        }[]
        copyRight: string
        PaymentData: {
            data: {
                image: string
                key: string
                name: string
            }[]
        }
    }
}>()

</script>

<template>
    <div id="app" class="py-24 md:px-7" :style="getStyles(modelValue.container.properties)">
        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 md:gap-8">
                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <div class="px-4 md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <div v-for="(item, index) in modelValue.column['column_1']['data']">
                            <div class="">
                                <div
                                    class="hidden md:block grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                    <div class="flex">
                                        <div class="w-fit">
                                            <span class="text-xl font-semibold w-fit leading-6">
                                                <div v-html="item.name" />
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div v-for="(sub, subIndex) in item.data">
                                            <ul class="hidden md:block space-y-1">
                                                <li>
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-full" >
                                                            <span class="text-sm block">
                                                                <div v-html="sub.name" />
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="block md:hidden">
                                    <Disclosure v-slot="{ open }">
                                        <DisclosureButton
                                            class="grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none w-full">
                                            <div class="flex justify-between">
                                                <div class="flex ">
                                                    <div class="w-fit">
                                                        <span class="text-xl font-semibold leading-6">
                                                            <div v-html="item.name" />
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <FontAwesomeIcon :icon="open ? faAngleDown : faAngleUp" class="w-3 h-3" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel>
                                            <div>
                                                <div>
                                                    <div v-for="(sub, subIndex) in item.data">
                                                        <ul class="block space-y-1">
                                                            <li>
                                                                <div class="flex items-center">
                                                                    <div class="w-full">
                                                                        <span class="text-sm block">
                                                                            <div v-html="sub.name" />
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </DisclosurePanel>
                                    </Disclosure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <div class="px-4 md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <div v-for="(item, index) in modelValue.column['column_2']['data']">
                            <div>
                                <div class="hidden md:block grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                    <div class="flex">
                                        <div class="w-full">
                                            <span class="text-xl font-semibold w-fit leading-6">
                                                <div v-html="item.name" />
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div v-for="(sub, subIndex) in item.data">
                                            <ul class="hidden md:block space-y-1">
                                                <li>
                                                    <div class="flex items-center">
                                                        <div class="w-full">
                                                            <span class="text-sm block">
                                                                <div v-html="sub.name" />
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="block md:hidden">
                                    <Disclosure v-slot="{ open }">
                                        <DisclosureButton
                                            class="grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none w-full">
                                            <div class="flex justify-between">
                                                <div class="flex ">
                                                    <div class="w-fit">
                                                        <span class="text-xl font-semibold leading-6">
                                                            <div v-html="item.name" />
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <FontAwesomeIcon :icon="open ? faAngleDown : faAngleUp" class="w-3 h-3" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel>
                                            <div>
                                                <div>
                                                    <div v-for="(sub, subIndex) in item.data">
                                                        <ul class="block space-y-1">
                                                            <li>
                                                                <div class="flex items-center">
                                                                    <div class="w-full">
                                                                        <span class="text-sm block">
                                                                            <div v-html="sub.name"
                                                                                :toogle="['link']" />
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </DisclosurePanel>
                                    </Disclosure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <div class="hidden md:block px-4 md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <div v-for="(item, index) in modelValue.column['column_3']['data']">
                            <div>
                                <div class="grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                    <div class="flex">
                                        <div class="w-full">
                                            <span class="text-xl font-semibold w-fit leading-6">
                                                <div v-html="item.name" />
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div v-for="(sub, subIndex) in item.data">
                                            <ul class="hidden md:block space-y-1">
                                                <li>
                                                    <div class="flex items-center">
                                                        <div class="w-full">
                                                            <span class="text-sm block">
                                                                <div v-html="sub.name" />
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="block md:hidden">
                                    <Disclosure v-slot="{ open }">
                                        <DisclosureButton
                                            class="asdzxc grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none w-full">
                                            <div class="flex justify-between">
                                                <div class="flex ">
                                                    <div class="w-fit">
                                                        <span class="text-xl font-semibold leading-6">
                                                            <div v-html="item.name" />
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <FontAwesomeIcon :icon="open ? faAngleDown : faAngleUp" class="w-3 h-3" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel>
                                            <div>
                                                <div>
                                                    <div v-for="(sub, subIndex) in item.data">
                                                        <ul class="block space-y-1">
                                                            <li>
                                                                <div class="flex items-center">
                                                                    <div class="w-full">
                                                                        <span class="text-sm block">
                                                                            <div v-html="sub.name" />
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </DisclosurePanel>
                                    </Disclosure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="md:hidden mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                    <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                    <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                        <a v-for="item of modelValue.socialData" target="_blank" :key="item.icon"
                            :href="item.link">
                            <font-awesome-icon :icon="item.icon" class="text-2xl" />
                        </a>
                    </div>
                </div>

                <div class="flex flex-col flex-col-reverse gap-y-6 md:block">
                    <div>
                        <div class="flex flex-wrap -mx-4">
                            <div v-for="payment in modelValue.PaymentData.data" :key="payment.key"
                                class="w-full md:w-1/3 px-4 mb-8">
                                <div class="flex items-center justify-center md:justify-start space-x-4">
                                    <img :src="payment.image" :alt="payment.name" class="px-1 h-4">
                                </div>
                            </div>
                        </div>
                        <address
                            class="mt-10 md:mt-0 not-italic mb-4 text-center md:text-left text-xs md:text-sm text-gray-300">
                            <div v-html="modelValue.column.column_4.data.textBox1" />
                        </address>

                        <div class="flex justify-center gap-x-8 text-gray-300 md:block">
                            <div v-html="modelValue.column.column_4.data.textBox2" />
                        </div>
                        <div
                            class="hidden md:block mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                            <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                            <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                                <a v-for="item of modelValue.socialData" :key="item.icon" target="_blank"
                                    :href="item.link"><font-awesome-icon :icon="item.icon" class="text-2xl" /></a>
                            </div>
                        </div>
                    </div>
                    <div
                        class="border-b border-gray-500 md:border-none flex items-center space-x-2 px-5 pb-4 md:pb-0 md:px-0">
                        <i class="text-4xl md:text-3xl fab fa-whatsapp text-green-500"></i>
                        <span class="w-10/12 md:w-full md:text-sm">
                            <div v-html="modelValue.column.column_4.data.textBox3" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-[10px] md:text-base border-t mt-8 pb-2 pt-2 md:pb-0 md:pt-4 text-center">
                <div v-html="modelValue.copyRight" />
            </div>
        </div>
    </div>
</template>