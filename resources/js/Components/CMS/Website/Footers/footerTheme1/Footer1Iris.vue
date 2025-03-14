<script setup lang="ts">
import { getStyles } from '@/Composables/styles'
import { FieldValue } from '@/types/Website/Website/footer1'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { isObject } from 'lodash-es';

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faShieldAlt, faPlus, faTrash, faArrowSquareLeft, faTriangle } from "@fas"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faFacebook, faWhatsapp } from "@fortawesome/free-brands-svg-icons"
import { faBars } from '@fal'
import Image from '@/Components/Image.vue'

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faBars, faPlus, faTrash, faArrowSquareLeft, faFacebook, faWhatsapp)

const props = defineProps<{
    fieldValue?: FieldValue,
    modelValue: FieldValue
}>();

</script>

<template>
    <div id="app" class="md:mx-0 pb-12 lg:pb-24 pt-4 md:pt-8 md:px-16 text-white"
        :style="getStyles(modelValue?.container?.properties)">
        <div
            class="w-full flex flex-col md:flex-row gap-4 md:gap-8 pt-2 pb-4 md:pb-6 mb-4 md:mb-10 border-0 border-b border-solid border-gray-700">
            <div class="overflow-hidden flex-1 flex items-center justify-center md:justify-start ">
                <Image v-if="modelValue?.logo?.source" :src="modelValue?.logo?.source" :imageCover="true" :alt="modelValue?.logo?.alt"
                    :imgAttributes="modelValue?.logo?.attributes" :style="getStyles(modelValue?.logo?.properties)" />
            </div>

            <div v-if="modelValue?.email"
                class="relative group flex-1 flex justify-center md:justify-start items-center">
                <div style="font-size: 17px">{{ modelValue?.email }}</div>
            </div>

            <div v-if="modelValue?.whatsapp?.number"
                class="relative group flex-1 flex gap-x-1.5 justify-center md:justify-start items-center">
                <a :href="`https://wa.me/${modelValue?.whatsapp?.number.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(modelValue?.whatsapp?.message || '')}`"
                    class="flex gap-x-2 items-center">
                    <FontAwesomeIcon class="text-[#00EE52]" icon="fab fa-whatsapp" style="font-size: 22px" />
                    <span style="font-size: 17px">{{ modelValue?.whatsapp?.number }}</span>
                </a>
            </div>

            <div class="group relative flex-1 flex flex-col items-center md:items-end justify-center">
                <a v-for="phone of modelValue?.phone?.numbers" :href="`tel:${phone}`" style="font-size: 17px">
                    {{ phone }}
                </a>

                <span class="" style="font-size: 15px">{{ modelValue?.phone?.caption }}</span>
            </div>
        </div>


        <div class=" grid grid-cols-1 md:grid-cols-4 gap-3 md:gap-8">
            <!--  column 1 -->
            <div class="md:px-0 grid gap-y-3 md:gap-y-6 h-fit">
                <div class="md:px-0 grid gap-y-3 md:gap-y-6 h-fit">
                    <div class="md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <!-- Desktop -->
                        <section v-for="item in modelValue?.columns?.column_1?.data">
                            <div
                                class="hidden md:block grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                <div class="flex text-xl font-semibold w-fit leading-6">
                                    <div v-html="item.name" />
                                </div>

                                <div>
                                    <ul class="hidden md:block space-y-3">
                                        <li v-for="link in item.data" class="flex w-full items-center gap-2">
                                            <div class="text-sm block">
                                                <div v-html="link.name" />
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Mobile  -->
                            <div class="block md:hidden">
                                <Disclosure v-slot="{ open }" class="m-2">
                                    <div :class="open ? 'bg-[rgba(240,240,240,0.15)] rounded' : ''">
                                        <DisclosureButton
                                            class="p-3 pb-0 md:p-0 transition-all flex justify-between cursor-default  w-full">
                                            <div class="flex justify-between w-full">
                                                <span class="mb-0 pl-0 md:pl-[2.2rem] text-xl font-semibold leading-6">
                                                    <div v-html="item.name"></div>
                                                </span>
                                                <div>
                                                    <FontAwesomeIcon :icon="faTriangle"
                                                        :class="['w-2 h-2 transition-transform', open ? 'rotate-180' : '']" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel class="p-3 md:p-0 transition-all cursor-default w-full">
                                            <ul class="mt-0 block space-y-4 pl-4 md:pl-[2.2rem]"
                                                style="margin-top: 0px">
                                                <li v-for="menu of item.data" :key="menu.name"
                                                    class="flex items-center text-sm">
                                                    <div v-html="menu.name"></div>
                                                </li>
                                            </ul>
                                        </DisclosurePanel>
                                    </div>
                                </Disclosure>
                            </div>
                        </section>
                    </div>
                </div>

            </div>

            <!--    column 2 -->
            <div class="md:px-0 grid gap-y-3 md:gap-y-6 h-fit">
                <div class="md:px-0 grid gap-y-3 md:gap-y-6 h-fit">
                    <div class="md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <!-- Desktop -->
                        <section v-for="item in modelValue?.columns?.column_2?.data">
                            <div
                                class="hidden md:block grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                <div class="flex text-xl font-semibold w-fit leading-6">
                                    <div v-html="item.name" />
                                </div>

                                <div>
                                    <ul class="hidden md:block space-y-3">
                                        <li v-for="link in item.data" class="flex w-full items-center gap-2">
                                            <div class="text-sm block">
                                                <div v-html="link.name" />
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Mobile  -->
                            <div class="block md:hidden">
                                <Disclosure v-slot="{ open }" class="m-2">
                                    <div :class="open ? 'bg-[rgba(240,240,240,0.15)] rounded' : ''">
                                        <DisclosureButton
                                            class="p-3 pb-0 md:p-0 transition-all flex justify-between cursor-default  w-full">
                                            <div class="flex justify-between w-full">
                                                <span class="mb-0 pl-0 md:pl-[2.2rem] text-xl font-semibold leading-6">
                                                    <div v-html="item.name"></div>
                                                </span>
                                                <div>
                                                    <FontAwesomeIcon :icon="faTriangle"
                                                        :class="['w-2 h-2 transition-transform', open ? 'rotate-180' : '']" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel class="p-3 md:p-0 transition-all cursor-default w-full">
                                            <ul class="mt-0 block space-y-4 pl-4 md:pl-[2.2rem]"
                                                style="margin-top: 0px">
                                                <li v-for="menu of item.data" :key="menu.name"
                                                    class="flex items-center text-sm">
                                                    <div v-html="menu.name"></div>
                                                </li>
                                            </ul>
                                        </DisclosurePanel>
                                    </div>
                                </Disclosure>
                            </div>
                        </section>
                    </div>
                </div>

            </div>

            <!--    column 3 -->
            <div class="md:px-0 grid gap-y-3 md:gap-y-6 h-fit">
                <div class="md:px-0 grid gap-y-3 md:gap-y-6 h-fit">
                    <div class="md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <!-- Desktop -->
                        <section v-for="item in modelValue?.columns?.column_3?.data">
                            <div
                                class="hidden md:block grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                <div class="flex text-xl font-semibold w-fit leading-6">
                                    <div v-html="item.name" />
                                </div>

                                <div>
                                    <ul class="hidden md:block space-y-3">
                                        <li v-for="link in item.data" class="flex w-full items-center gap-2">
                                            <div class="text-sm block">
                                                <div v-html="link.name" />
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Mobile  -->
                            <div class="block md:hidden">
                                <Disclosure v-slot="{ open }" class="m-2">
                                    <div :class="open ? 'bg-[rgba(240,240,240,0.15)] rounded' : ''">
                                        <DisclosureButton
                                            class="p-3 pb-0 md:p-0 transition-all flex justify-between cursor-default  w-full">
                                            <div class="flex justify-between w-full">
                                                <span class="mb-0 pl-0 md:pl-[2.2rem] text-xl font-semibold leading-6">
                                                    <div v-html="item.name"></div>
                                                </span>
                                                <div>
                                                    <FontAwesomeIcon :icon="faTriangle"
                                                        :class="['w-2 h-2 transition-transform', open ? 'rotate-180' : '']" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel class="p-3 md:p-0 transition-all cursor-default w-full">
                                            <ul class="mt-0 block space-y-4 pl-4 md:pl-[2.2rem]"
                                                style="margin-top: 0px">
                                                <li v-for="menu of item.data" :key="menu.name"
                                                    class="flex items-center text-sm">
                                                    <div v-html="menu.name"></div>
                                                </li>
                                            </ul>
                                        </DisclosurePanel>
                                    </div>
                                </Disclosure>
                            </div>
                        </section>
                    </div>
                </div>

            </div>

            <!--  column 4 -->
            <div class="flex flex-col flex-col-reverse gap-y-6 md:block">
                <div>
                    <address
                        class="mt-10 md:mt-0 not-italic mb-4 text-center md:text-left text-xs md:text-sm text-gray-300">
                        <div v-html="modelValue?.columns.column_4.data.textBox1"></div>
                    </address>

                    <div class="flex justify-center gap-x-8 text-gray-300 md:block">
                        <div v-html="modelValue?.columns.column_4.data.textBox2"></div>
                    </div>

                    <div class="w-full mt-8">
                        <div v-html="modelValue?.paymentData.label"></div>
                    </div>

                    <div class="flex flex-col items-center gap-y-6 mt-4">
                        <img v-for="item of modelValue?.paymentData.data" :src="item.value" :alt="item.name"
                            class="h-auto max-h-6 md:max-h-8 max-w-full w-full object-contain">
                    </div>
                </div>
            </div>

        </div>
        <div
            class="mt-8 w-full border-0 border-t border-solid border-gray-700 flex flex-col md:flex-row-reverse justify-between pt-6 items-center gap-y-8">
            <div class="grid gap-y-2 text-center md:text-left">
                <h2 style="margin-bottom: 0px; font-size: inherit; font-weight: inherit"
                    class="hidden text-center tracking-wider">
                    <div v-html="modelValue?.columns.column_4.data.textBox4"></div>
                </h2>

                <div v-if="modelValue?.socialMedia?.length" class="flex gap-x-6 justify-center">
                    <a v-for="socmed of modelValue?.socialMedia" target="_blank" :href="socmed.link">
                        {{ socmed.icon }} =====
                        <FontAwesomeIcon :icon="socmed.icon" class="text-4xl md:text-2xl"></FontAwesomeIcon>
                    </a>
                </div>
            </div>

            <div id="footer_copyright"
                class="text-[13px] leading-5 md:text-[12px] text-center md:w-fit mx-auto md:mx-0">
                <div v-html="modelValue?.copyright"></div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
.editor-class ul {
    margin-left: 0rem;
    margin-top: 0.5rem;
    list-style-position: outside;
}
</style>