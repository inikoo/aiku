<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 08 Feb 2024 16:53:19 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faPlus, faMinus } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import Layout from '@/Layouts/Iris.vue'

defineOptions({ layout: Layout })
library.add(faCheck, faPlus, faMinus)

const features = [
    {
        name: 'Flexible Storage Options',
        description: 'Choose from a variety of warehouse spaces tailored to your needs. Lorem ipsum dolor sit amet consectetur adipisicing elit. Possimus, laboriosam?'
    },
    {
        name: 'Secure Facilities',
        description: 'Rest assured with our state-of-the-art security systems and personnel. Amet consectetur adipisicing elit. Adipisci, consectetur dolorem.'
    },
    {
        name: '24/7 Access',
        description: 'Access your stored items whenever you need them, day or night. Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusantium, quisquam.'
    },
    {
        name: 'Climate Control',
        description: 'Ensure the optimal condition of your goods with our climate-controlled warehouses. Consectetur adipisicing elit. Ad, explicabo labore.'
    },
    {
        name: 'Inventory Management',
        description: 'Streamline your operations with our advanced inventory tracking and management solutions. Dolor sit amet consectetur adipisicing elit. Aperiam, molestiae.'
    },
    {
        name: 'Loading Docks',
        description: 'Efficiently load and unload your shipments with our convenient loading docks. Lorem ipsum dolor sit amet consectetur adipisicing elit. Mollitia, odit.'
    }
];

const pricing = [
    {
        name: 'Basic',
        id: 'tier-basic',
        href: '#',
        priceMonthly: '$99',
        description: "Ideal for small businesses or startups with moderate storage needs.",
        features: ['100 sq. ft. storage space', '24/7 access', 'Basic security features'],
        featured: false,
    },
    {
        name: 'Pro',
        id: 'tier-pro',
        href: '#',
        priceMonthly: '$249',
        description: 'Perfect for growing businesses with increased storage demands.',
        features: [
            '250 sq. ft. storage space',
            'Climate-controlled environment',
            'Advanced security system',
            'Inventory management software',
        ],
        featured: true,
    },
]

const faqs = [
    {
        question: "What sizes of storage spaces do you offer?",
        answer:
            "We offer a range of storage space sizes to accommodate various needs, from small businesses to large-scale operations. Our options include spaces starting from 100 square feet up to customizable areas exceeding 500 square feet.",
    },
    {
        question: "How secure are your warehouse facilities?",
        answer:
            "Security is a top priority for us. Our warehouse facilities are equipped with state-of-the-art security systems, including surveillance cameras, access control measures, and on-site personnel. Additionally, we implement customized security protocols to ensure the safety and integrity of your stored items.",
    },
]

</script>

<template>
    <Head title="Warehouse Solution" />
    

    <div class="bg-white pb-20">
        <!-- Hero section -->
        <div class="relative isolate overflow-hidden bg-gray-900 pb-16 pt-14 sm:pb-20">
            <img src="https://wallpapers.com/images/hd/warehouse-background-pzkv8y7kvdmmnyej.jpg" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover"
                style="filter: brightness(0.4) blur(5px)" />
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
                            Discover our warehouse facilities designed to safeguard your inventory with top-notch security and customizable storage options.
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

        <!-- Pricing section -->
        <div class="relative isolate mt-32 bg-white px-6 sm:mt-56 lg:px-8">
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

            <div class="mx-auto mt-16 grid max-w-lg grid-cols-1 items-center gap-y-6 sm:mt-20 sm:gap-y-0 lg:max-w-4xl lg:grid-cols-2">
                <div v-for="(tier, tierIdx) in pricing" :key="tier.id"
                    :class="[tier.featured ? 'relative bg-gray-900 shadow-2xl' : 'bg-white/60 sm:mx-8 lg:mx-0', tier.featured ? '' : tierIdx === 0 ? 'rounded-t-3xl sm:rounded-b-none lg:rounded-tr-none lg:rounded-bl-3xl' : 'sm:rounded-t-none lg:rounded-tr-3xl lg:rounded-bl-none', 'rounded-3xl p-8 ring-1 ring-gray-900/10 sm:p-10']">
                    <h3 :id="tier.id"
                        :class="[tier.featured ? 'text-indigo-400' : 'text-indigo-600', 'text-base font-semibold leading-7']">
                        {{ tier.name }}</h3>
                    <p class="mt-4 flex items-baseline gap-x-2">
                        <span
                            :class="[tier.featured ? 'text-white' : '', 'text-5xl font-bold tracking-tight']">{{
                                tier.priceMonthly }}</span>
                        <span :class="[tier.featured ? 'text-gray-400' : 'text-gray-500', 'text-base']">/month</span>
                    </p>
                    <p :class="[tier.featured ? 'text-gray-300' : 'text-gray-600', 'mt-6 text-base leading-7']">{{
                        tier.description }}</p>
                    <ul role="list"
                        :class="[tier.featured ? 'text-gray-300' : 'text-gray-600', 'mt-8 space-y-3 text-sm leading-6 sm:mt-10']">
                        <li v-for="feature in tier.features" :key="feature" class="flex gap-x-3">
                            <FontAwesomeIcon icon='fal fa-check' class='h-6 aspect-square' :class="[tier.featured ? 'text-indigo-400' : 'text-indigo-600']" fixed-width aria-hidden='true' />
                            {{ feature }}
                        </li>
                    </ul>
                    <a :href="tier.href" :aria-describedby="tier.id"
                        :class="[tier.featured ? 'bg-indigo-500 text-white shadow-sm hover:bg-indigo-400 focus-visible:outline-indigo-500' : 'text-indigo-600 ring-1 ring-inset ring-indigo-200 hover:ring-indigo-300 focus-visible:outline-indigo-600', 'mt-8 block rounded-md py-2.5 px-3.5 text-center text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 sm:mt-10']">Get
                        started today</a>
                </div>
            </div>
        </div>
    </div>
</template>
