<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Tue, 20 Sept 2022 15:08:44 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faPhone as fasPhone } from '@fas'
library.add(fasPhone)

const contentImages = [
    {
        label: 'South Yorkshire - Heart of the UK',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1830486',
        description: `Centrally located close to the M1 and with daily collections by all the UK's main courier companies Affinity Park is a perfect location for efficient UK wide distribution.`
    },
    {
        label: '100.000 Square Foot',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1830487',
        description: `Affinity Park is the main UK distribution hub for Ancient Wisdom (Europe's favourite giftware wholesaler for 20 years). With 10 container docking bays and over 6000 pallet locations at our fulfilment centre uk we can handle any project big or small. `
    },
    {
        label: 'Pallet & Parcel Delivery across UK',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1830488',
        description: `Use your own couriers or piggy back on our existing highly competitive contracts. Pallets and parcel deliveries plugged directly into the best UK carriers. Plus you can utilise the AW Dropshipping system,  with API's available for Shopify etc. Explore our order fulfilment services.`
    },
    {
        label: 'Storage Solutions',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1849917',
        description: `Store pallets & parcels hassle-free. Boost efficiency, reduce costs. Maximize space. We understand that every business is unique, and that's why we offer flexible storage options tailored to your specific needs.`
    },
    {
        label: 'Pick & Pack',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1849918',
        description: `Our pick and pack service delivers speed and precision, ensuring flawless order fulfilment uk. From warehouse to your customer's doorstep without errors and delays.`
    },
    {
        label: 'Rework & Repack',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1849919',
        description: `Your stock is mislabelled, packed incorrectly or missing barcodes? Elevate your brand with carefully curated products ensuring your peace of mind.`
    },
]

const textRandom = `<div id="blackboard_1" style="position: relative; height: 460.99px; width: 1240px" > <div id="blackboard_text_1694661242360" class="blackboard_text _au_vw_" style=" position: absolute; top: 0px; left: 100.98958587646px; width: 1062.99px; height: 192.986px; " ><h1><br /></h1 ><h3 style="text-align: center" >AW Fulfilment ensure seamless operations.&nbsp;</h3 ><h2 style="text-align: center" >Boost efficiency, reduce costs, and leave your customers amazed.</h2 ><p><br /></p ><p style="text-align: justify" >As a leading UK fulfilment center, we are committed to streamlining your supply chain and ensuring seamless operations. &nbsp;Our state-of-the-art fulfilment warehouse UK in Sheffield's Affinity Park is strategically positioned to cater to businesses across the United Kingdom. Whether you're a budding e-commerce startup or an established brand, our fulfilment services are tailored to meet your unique needs. Our comprehensive range of services encompasses everything you need for successful order fulfilment in the UK. We offer simple tariffs, &nbsp;transparent systems without hidden costs.</p ><p style="text-align: justify"><br /></p ><p style="text-align: justify" ><strong >At AW Fulfilment we offer an affordable, flexible and efficient way to store your goods. &nbsp;Warehouse spaces available for immediate use. Your goods are stored directly within our 100 000 sq foot warehouse facility. Our heavy duty racking, trained staff and onsite security setup ensure your inventory is in safe hands.&nbsp;</strong ></p ><h4 style="text-align: center" ><a href="https://www.aw-fulfilment.co.uk/pricing" rel="noopener noreferrer" target="_blank" >Pay only for the space and services you use. Expand as needed.</a ></h4 ><p >Our Fulfilment as a service offers competitive prices without compromising on excellence. Whether you need simple short term storage or a full service pack to store, pick &amp; pack and despatch - at AW Fulfilment Sheffield we can offer cost effective solutions. With rental rates from £2.00 &nbsp;/ pallet space per week and picking, packing and despatch services to hand so we can solve your fulfilment issues.&nbsp;</p ><p ><strong >For European companies wishing to solve the Brexit distribution problem, we can help you open up the UK market. Multilingual staff available, we speak your language: French, Spanish, Italian, Czech, Slovak, German, Polish &amp; Dutch.</strong ></p ></div > </div>`

</script>

<template>
    <Head :title="$t('Welcome to aiku')" />
    <div class="bg-white shadow-lg mx-auto max-w-7xl w-full">

        <!-- Section: Hero (banner) -->
        <iframe title="AW Fulfilment" style="width:100%;height: 320px; border: 0px;overflow:hidden;"
            src="http://delivery.wowsbar.test/banners/01HNEDQVYS8M4KXDAF5JJKYZG6" allowfullscreen></iframe>

        <!-- Section: Content -->
        <div>
            <!-- Section: Middle text -->
            <div class="text-justify text-gray-600" v-html="textRandom">
            </div>

            <!-- Section: Our Services -->
            <div class="py-12 sm:py-20">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Our services</h2>
                        <p class="mt-2 text-lg leading-8 text-gray-600">Learn how to grow your business with our expert
                            advice.</p>
                    </div>
                    <div
                        class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                        <article v-for="content, idxContent in contentImages" :key="idxContent"
                            class="flex flex-col items-start justify-between">
                            <div class="relative w-full">
                                <img :src="content.src" alt=""
                                    class="aspect-[16/9] w-full rounded-2xl bg-gray-100 object-cover sm:aspect-[2/1] lg:aspect-[3/2]" />
                                <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-gray-900/10" />
                            </div>
                            <div class="max-w-xl">
                                <div class="group relative">
                                    <h3
                                        class="mt-3 text-lg font-semibold leading-6 text-gray-900 group-hover:text-gray-600">
                                        {{ content.label }}
                                    </h3>
                                    <p class="mt-1 line-clamp-3 text-sm leading-6 text-gray-600">{{ content.description }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <!-- Section: Customer Service -->
            <div class="relative isolate overflow-hidden bg-gray-900 px-6 py-16 sm:py-16 lg:px-8 mb-16">
                <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&crop=focalpoint&fp-y=.8&w=2830&h=1500&q=80&blend=111827&sat=-100&exp=15&blend-mode=multiply"
                    alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" />
                <div class="hidden sm:absolute sm:-top-10 sm:right-1/2 sm:-z-10 sm:mr-10 sm:block sm:transform-gpu sm:blur-3xl"
                    aria-hidden="true">
                    <div class="aspect-[1097/845] w-[68.5625rem] bg-gradient-to-tr from-[#ff4694] to-[#776fff] opacity-20"
                        style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
                </div>
                <div class="absolute -top-52 left-1/2 -z-10 -translate-x-1/2 transform-gpu blur-3xl sm:top-[-28rem] sm:ml-16 sm:translate-x-0 sm:transform-gpu"
                    aria-hidden="true">
                    <div class="aspect-[1097/845] w-[68.5625rem] bg-gradient-to-tr from-[#ff4694] to-[#776fff] opacity-20"
                        style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
                </div>
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">Support Center</h2>
                    <!-- <p class="mt-6 text-lg leading-8 text-gray-300">Click here</p> -->
                    <Button :style="'white'" label="Contact Us" size="xl" class="mt-10" />
                    <div class="w-3/4 flex gap-x-3 items-center mx-auto mt-7">
                        <hr class="border w-full border-gray-100/70 mt-1 rounded-full">
                        <div class="text-gray-100/70 leading-none">or</div>
                        <hr class="border w-full border-gray-100/70 mt-1 rounded-full">
                    </div>
                    <p class="mt-6 text-lg leading-8 text-gray-300">
                        <FontAwesomeIcon icon='fas fa-phone' class='' aria-hidden='true' />
                        Call us on +44 07377 188 459 <span class="font-bold">(James)</span>
                    </p>
                </div>
            </div>

            <!-- Section: Maps -->
            <div class="h-96">
                <iframe class="w-full h-full" _src="#map"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2378.920601721521!2d-1.486865683977093!3d53.39836017851476!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4879788c1041fe81%3A0xd19b7056dbeebdb0!2sAncient+Wisdom+Giftware+Wholesale!5e0!3m2!1sen!2suk!4v1555071386906!5m2!1sen!2suk"
                    framescrolling="no" marginheight="0" marginwidth="0" allowfullscreen></iframe>
            </div>
        </div>

    </div>
</template>


