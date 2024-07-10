<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 08 Feb 2024 16:53:19 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faPlus, faMinus } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import LayoutIris from '@/Layouts/Iris.vue'
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'

// import "https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap"

const props = defineProps<{
    data: any,
    blocks: any,
}>()

console.log('blocks from webpage', props.blocks.web_blocks)
console.log('data', props.data)

defineOptions({ layout: LayoutIris })
library.add(faCheck, faPlus, faMinus)

// const features = [
//     {
//         name: 'Flexible Storage Options',
//         description: 'Choose from a variety of warehouse spaces tailored to your needs. Lorem ipsum dolor sit amet consectetur adipisicing elit. Possimus, laboriosam?'
//     },
//     {
//         name: 'Secure Facilities',
//         description: 'Rest assured with our state-of-the-art security systems and personnel. Amet consectetur adipisicing elit. Adipisci, consectetur dolorem.'
//     },
//     {
//         name: '24/7 Access',
//         description: 'Access your stored items whenever you need them, day or night. Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusantium, quisquam.'
//     },
//     {
//         name: 'Climate Control',
//         description: 'Ensure the optimal condition of your goods with our climate-controlled warehouses. Consectetur adipisicing elit. Ad, explicabo labore.'
//     },
//     {
//         name: 'Inventory Management',
//         description: 'Streamline your operations with our advanced inventory tracking and management solutions. Dolor sit amet consectetur adipisicing elit. Aperiam, molestiae.'
//     },
//     {
//         name: 'Loading Docks',
//         description: 'Efficiently load and unload your shipments with our convenient loading docks. Lorem ipsum dolor sit amet consectetur adipisicing elit. Mollitia, odit.'
//     }
// ]

// const pricing = [
//     {
//         name: 'Basic',
//         id: 'tier-basic',
//         href: '#',
//         priceMonthly: '$99',
//         description: "Ideal for small businesses or startups with moderate storage needs.",
//         features: ['100 sq. ft. storage space', '24/7 access', 'Basic security features'],
//         featured: false,
//     },
//     {
//         name: 'Pro',
//         id: 'tier-pro',
//         href: '#',
//         priceMonthly: '$249',
//         description: 'Perfect for growing businesses with increased storage demands.',
//         features: [
//             '250 sq. ft. storage space',
//             'Climate-controlled environment',
//             'Advanced security system',
//             'Inventory management software',
//         ],
//         featured: true,
//     },
// ]

// const faqs = [
//     {
//         question: "What sizes of storage spaces do you offer?",
//         answer:
//             "We offer a range of storage space sizes to accommodate various needs, from small businesses to large-scale operations. Our options include spaces starting from 100 square feet up to customizable areas exceeding 500 square feet.",
//     },
//     {
//         question: "How secure are your warehouse facilities?",
//         answer:
//             "Security is a top priority for us. Our warehouse facilities are equipped with state-of-the-art security systems, including surveillance cameras, access control measures, and on-site personnel. Additionally, we implement customized security protocols to ensure the safety and integrity of your stored items.",
//     },
// ]

const people = [
  {
    name: 'South Yorkshire - Heart of the UK',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1830486',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: '100 000 Square Foot',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1830487',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Pallet & Parcel Delivery across UK',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1830488',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Storage Solutions',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1849917',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Pick & Pack',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1849918',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  {
    name: 'Rework & Repack  ',
    role: 'Senior Developer',
    imageUrl:
      'https://www.aw-fulfilment.co.uk/wi.php?id=1849919',
    bio: 'Praesentium iure error aliquam voluptas ut libero. Commodi placeat sit iure nulla officiis. Ut ex sit repellat tempora. Qui est accusamus exercitationem natus ut voluptas. Officiis velit eos ducimus.',
    xUrl: '#',
    linkedinUrl: '#',
  },
  // More people...
]


</script>

<template>

    <Head title="Warehouse Solution" />
<!-- <pre>{{ blocks.web_blocks[0].web_block.layout.data.component }}</pre> -->

    <div class="bg-white pb-20">
        <template v-if="props.blocks?.web_blocks?.length">
            <div v-for="(activityItem, activityItemIdx) in props.blocks.web_blocks"
                :key="'block' + activityItem.id"
                class="w-full">
                <!-- ==== {{ activityItem['type'] }} -->
                <component
                    :is="getComponent(activityItem.web_block.layout.data.component)"
                    :key="activityItemIdx"
                    v-model="activityItem.fieldValue"
                    :isEditable="false"
                    v-bind="activityItem.web_block.layout.data.fieldValue"
                />
                <!-- <pre>{{ activityItem.web_block.layout.data.fieldValue }}</pre> -->
            </div>
        </template>

        <div class="text-center text-2xl sm:text-4xl font-bold text-gray-400 mt-16">
            This page have no data
        </div>
    
        <!-- <div class="">
            <iframe title="AW Fulfilment" style="width:100%;height: 100%; border: 0px;overflow:hidden;"
                src="https://delivery.wowsbar.com/banners/01HDRV5VBPRY0DMEED42JGFGX4" ></iframe>
        </div> -->

        <!-- <div class="px-12">
            <h1 class="text-4xl font-bold text-center pt-8"
                :style="{
                    fontFamily: `'Space Mono', 'monospace'`,
                    fontWeight: 700,
                    fontStyle: 'normal',
                }"
            >
                AW Fulfilment ensure seamless operations.

            </h1>
            <h2 class="text-xl font-medium text-center pb-8">Boost efficiency, reduce costs, and leave your customers
                amazed.</h2>

            <div>
                <p>As a leading UK fulfilment center, we are committed to streamlining your supply chain and ensuring
                    seamless operations. Our state-of-the-art fulfilment warehouse UK in Sheffield's Affinity Park is
                    strategically positioned to cater to businesses across the United Kingdom. Whether you're a budding
                    e-commerce startup or an established brand, our fulfilment services are tailored to meet your unique
                    needs. Our comprehensive range of services encompasses everything you need for successful order
                    fulfilment in the UK. We offer simple tariffs, transparent systems without hidden costs.</p>
                <br>
                <p>
                    At AW Fulfilment we offer an affordable, flexible and efficient way to store your goods. Warehouse
                    spaces available for immediate use. Your goods are stored directly within our 100 000 sq foot
                    warehouse
                    facility. Our heavy duty racking, trained staff and onsite security setup ensure your inventory is
                    in
                    safe hands.
                    Pay only for the space and services you use. Expand as needed.
                </p>
                <br>
                <p>Our Fulfillment as a service offers competitive prices without compromising on excellence. Whether
                    you
                    need simple short term storage or a full service pack to store, pick & pack and despatch - at AW
                    Fulfilment Sheffield we can offer cost effective solutions. With rental rates from £2.00 / pallet
                    space
                    per week and picking, packing and despatch services to hand so we can solve your fulfilment issues.
                </p>
                <br>
                <p>For European companies wishing to solve the Brexit distribution problem, we can help you open up the
                    UK
                    market. Multilingual staff available, we speak your language: French, Spanish, Italian, Czech,
                    Slovak,
                    German, Polish & Dutch.</p>
                <br>
            </div>
        </div> -->

        <!-- Hero section -->
        <div v-if="false" class="relative isolate overflow-hidden bg-gray-900 pb-16 pt-14 sm:pb-20">
            <img src="https://wallpapers.com/images/hd/warehouse-background-pzkv8y7kvdmmnyej.jpg" alt=""
                class="absolute inset-0 -z-10 h-full w-full object-cover" style="filter: brightness(0.4) blur(5px)" />
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
                aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
                    style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl py-32 sm:py-44">
                    <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                        <div
                            class="relative rounded-full px-3 py-1 text-sm leading-6 text-gray-400 ring-1 ring-white/10 hover:ring-white/20">
                            Learm more about our warehouse. <a href="#" class="font-semibold text-white"><span
                                    class="absolute inset-0" aria-hidden="true" />Read more <span
                                    aria-hidden="true">&rarr;</span></a>
                        </div>
                    </div>
                    <div class="text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">
                            Secure Storage Solutions for Your Products.
                        </h1>
                        <p class="mt-6 leading-8 text-gray-300">
                            Discover our warehouse facilities designed to safeguard your inventory with top-notch
                            security and customizable storage options.
                            Streamline your operations with ease, whether you're a small business or a large enterprise.
                        </p>
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            <a href="#"
                                class="rounded-md bg-indigo-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-400">Get
                                started</a>
                            <a href="#" class="text-sm font-semibold leading-6 text-white">Live demo <span
                                    aria-hidden="true">→</span></a>
                        </div>
                    </div>
                </div>

                <!-- Logo cloud -->
                <div
                    class="mx-auto grid max-w-lg grid-cols-4 items-center gap-x-8 gap-y-10 sm:max-w-xl sm:grid-cols-6 sm:gap-x-10 lg:mx-0 lg:max-w-none lg:grid-cols-5">
                    <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/transistor-logo-white.svg" alt="Transistor"
                        width="158" height="48" />
                    <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/reform-logo-white.svg" alt="Reform" width="158"
                        height="48" />
                    <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/tuple-logo-white.svg" alt="Tuple" width="158"
                        height="48" />
                    <img class="col-span-2 max-h-12 w-full object-contain sm:col-start-2 lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/savvycal-logo-white.svg" alt="SavvyCal" width="158"
                        height="48" />
                    <img class="col-span-2 col-start-2 max-h-12 w-full object-contain sm:col-start-auto lg:col-span-1"
                        src="https://tailwindui.com/img/logos/158x48/statamic-logo-white.svg" alt="Statamic" width="158"
                        height="48" />
                </div>
            </div>
            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
                aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
                    style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>
        </div>

        <div v-if="false" class="bg-white py-24 md:py-32 lg:py-40">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-x-8 gap-y-20 px-6 lg:px-8 xl:grid-cols-3">
                <ul role="list"
                    class="mx-auto grid max-w-2xl grid-cols-1 gap-x-6 gap-y-20 sm:grid-cols-3 lg:mx-0 lg:max-w-none lg:gap-x-8 xl:col-span-3">
                    <li v-for="person in people" :key="person.name">
                        <img class="aspect-[3/2] w-full rounded-2xl object-cover" :src="person.imageUrl" alt="" />
                        <h3 class="mt-6 text-lg font-semibold leading-8 text-gray-900">{{ person.name }}</h3>
                        <!-- <p class="text-base leading-7 text-gray-600">{{ person.role }}</p> -->
                        <p class="mt-4 text-base leading-7 text-gray-600">{{ person.bio }}</p>
                        <!-- <ul role="list" class="mt-6 flex gap-x-6">
                            <li>
                                <a :href="person.xUrl" class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">X</span>
                                    <svg class="h-5 w-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M11.4678 8.77491L17.2961 2H15.915L10.8543 7.88256L6.81232 2H2.15039L8.26263 10.8955L2.15039 18H3.53159L8.87581 11.7878L13.1444 18H17.8063L11.4675 8.77491H11.4678ZM9.57608 10.9738L8.95678 10.0881L4.02925 3.03974H6.15068L10.1273 8.72795L10.7466 9.61374L15.9156 17.0075H13.7942L9.57608 10.9742V10.9738Z" />
                                    </svg>
                                </a>
                            </li>
                            <li>
                                <a :href="person.linkedinUrl" class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">LinkedIn</span>
                                    <svg class="h-5 w-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </li>
                        </ul> -->
                    </li>
                </ul>
            </div>
        </div>

        <!-- Pricing section -->
        <!-- <div v-if="false" class="relative isolate mt-32 bg-white px-6 sm:mt-56 lg:px-8">
            <div class="absolute inset-x-0 -top-3 -z-10 transform-gpu overflow-hidden px-36 blur-3xl"
                aria-hidden="true">
                <div class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30"
                    style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>

            <div class="mx-auto max-w-2xl text-center lg:max-w-4xl">
                <h2 class="text-base font-semibold leading-7 text-indigo-600">Pricing</h2>
                <p class="mt-2 text-4xl font-bold tracking-tight sm:text-5xl">The right price for you,
                    whoever you are</p>
            </div>

            <p class="mx-auto mt-6 max-w-2xl text-center text-lg leading-8 text-gray-600">Qui iusto aut est earum eos
                quae. Eligendi est at nam aliquid ad quo reprehenderit in aliquid fugiat dolorum voluptatibus.</p>

            <div
                class="mx-auto mt-16 grid max-w-lg grid-cols-1 items-center gap-y-6 sm:mt-20 sm:gap-y-0 lg:max-w-4xl lg:grid-cols-2">
                <div v-for="(tier, tierIdx) in pricing" :key="tier.id"
                    :class="[tier.featured ? 'relative bg-gray-900 shadow-2xl' : 'bg-white/60 sm:mx-8 lg:mx-0', tier.featured ? '' : tierIdx === 0 ? 'rounded-t-3xl sm:rounded-b-none lg:rounded-tr-none lg:rounded-bl-3xl' : 'sm:rounded-t-none lg:rounded-tr-3xl lg:rounded-bl-none', 'rounded-3xl p-8 ring-1 ring-gray-900/10 sm:p-10']">
                    <h3 :id="tier.id"
                        :class="[tier.featured ? 'text-indigo-400' : 'text-indigo-600', 'text-base font-semibold leading-7']">
                        {{ tier.name }}</h3>
                    <p class="mt-4 flex items-baseline gap-x-2">
                        <span :class="[tier.featured ? 'text-white' : '', 'text-5xl font-bold tracking-tight']">{{
                            tier.priceMonthly }}</span>
                        <span :class="[tier.featured ? 'text-gray-400' : 'text-gray-500', 'text-base']">/month</span>
                    </p>
                    <p :class="[tier.featured ? 'text-gray-300' : 'text-gray-600', 'mt-6 text-base leading-7']">{{
                        tier.description }}</p>
                    <ul role="list"
                        :class="[tier.featured ? 'text-gray-300' : 'text-gray-600', 'mt-8 space-y-3 text-sm leading-6 sm:mt-10']">
                        <li v-for="feature in tier.features" :key="feature" class="flex gap-x-3">
                            <FontAwesomeIcon icon='fal fa-check' class='h-6 aspect-square'
                                :class="[tier.featured ? 'text-indigo-400' : 'text-indigo-600']" fixed-width
                                aria-hidden='true' />
                            {{ feature }}
                        </li>
                    </ul>
                    <a :href="tier.href" :aria-describedby="tier.id"
                        :class="[tier.featured ? 'bg-indigo-500 text-white shadow-sm hover:bg-indigo-400 focus-visible:outline-indigo-500' : 'text-indigo-600 ring-1 ring-inset ring-indigo-200 hover:ring-indigo-300 focus-visible:outline-indigo-600', 'mt-8 block rounded-md py-2.5 px-3.5 text-center text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 sm:mt-10']">Get
                        started today</a>
                </div>
            </div>
        </div> -->
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap');
</style>