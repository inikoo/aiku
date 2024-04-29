<script setup>
// import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
// import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
// import { faCheck, faPlus, faMinus } from '@fal'
// import { faGithub } from "@fortawesome/free-brands-svg-icons"
// import { library } from '@fortawesome/fontawesome-svg-core'
// import { Head } from '@inertiajs/vue3'
// import Button from '@/Components/Elements/Buttons/Button.vue'
// library.add(faCheck, faPlus, faMinus, faGithub)

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

// <!-- Hero section -->
//         <div class="relative isolate overflow-hidden bg-gray-900 pb-16 pt-14 sm:pb-20">
//             <img src="https://wallpapers.com/images/hd/warehouse-background-pzkv8y7kvdmmnyej.jpg" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover"
//                 style="filter: brightness(0.4) blur(5px)" />
//             <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
//                 aria-hidden="true">
//                 <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
//                     style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
//             </div>
//             <div class="mx-auto max-w-7xl px-6 lg:px-8">
//                 <div class="mx-auto max-w-2xl py-32 sm:py-44">
//                     <div class="hidden sm:mb-8 sm:flex sm:justify-center">
//                         <div
//                             class="relative rounded-full px-3 py-1 text-sm leading-6 text-gray-400 ring-1 ring-white/10 hover:ring-white/20">
//                             Learn more about our Aiku. <a href="#" class="font-semibold text-white"><span
//                                     class="absolute inset-0" aria-hidden="true" />Read more <span
//                                     aria-hidden="true">&rarr;</span></a>
//                         </div>
//                     </div>
//                     <div class="text-center">
//                         <h1 class="text-4xl font-medium tracking-tight text-white sm:text-6xl">
//                             <span class="font-black">Take Control of Your Warehouse</span>
//                         </h1>
//                         <p class="mt-6 leading-8 text-gray-300">
//                             We are building an open-source 100% free ERP online software. Aiku Warehouse Management ERP provides the comprehensive solution you need to take control.
//                             Streamline operations, boost productivity, and gain real-time inventory insights with our powerful features designed to optimize every aspect of your warehouse.
//                             Reduce costs, minimize errors, and enhance customer satisfaction with our user-friendly software.
//                         </p>
//                         <a href="https://github.com/inikoo/aiku" target="_blank" class="mt-10 flex items-center justify-center gap-x-6">
//                             <Button type="white" label="Github" icon="fab fa-github" />
//                         </a>
//                     </div>
//                 </div>
//             </div>
//             <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
//                 aria-hidden="true">
//                 <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
//                     style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
//             </div>
//         </div>

//         <!-- Feature section -->
//         <div class="mt-24">
//             <div class="mx-auto max-w-7xl px-6 lg:px-8">
//                 <div class="mx-auto max-w-2xl sm:text-center">
//                     <h2 class="text-base font-semibold leading-7 text-indigo-600">All-Inclusive Solutions</h2>
//                     <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
//                         Your Warehousing Woes, Solved.
//                     </p>
//                     <p class="mt-6 text-lg leading-8 text-gray-600">
//                         Say goodbye to logistical headaches.
//                         With our comprehensive warehouse services, we've got you covered from storage to logistics, ensuring seamless operations for your business.
//                     </p>

//                 </div>
//             </div>
//             <div class="relative overflow-hidden pt-16">
//                 <div class="mx-auto max-w-7xl px-6 lg:px-8">
//                     <img src="art/AikuPublic.png"
//                         alt="App screenshot" class="mb-[-7%] rounded-xl shadow-2xl ring-1 ring-gray-900/10"
//                         width="100%" height="100%" />
//                     <div class="relative" aria-hidden="true">
//                         <div class="absolute -inset-x-20 bottom-0 bg-gradient-to-t from-white pt-[7%]" />
//                     </div>
//                 </div>
//             </div>
//             <div class="mx-auto mt-16 max-w-7xl px-6 sm:mt-20 md:mt-24 lg:px-8">
//                 <dl
//                     class="mx-auto grid max-w-2xl grid-cols-1 gap-x-6 gap-y-10 text-base leading-7 text-gray-600 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3 lg:gap-x-8 lg:gap-y-16">
//                     <div v-for="feature in features" :key="feature.name" class="relative pl-9">
//                         <dt class="inline font-semibold text-gray-900">
//                             <component :is="feature.icon" class="absolute left-1 top-1 h-5 w-5 text-indigo-600"
//                                 aria-hidden="true" />
//                             {{ feature.name }}
//                         </dt>
//                         {{ ' ' }}
//                         <dd class="inline">{{ feature.description }}</dd>
//                     </div>
//                 </dl>
//             </div>
//         </div>

//         <!-- Testimonial section -->
//         <!-- <div class="relative z-10 mt-32 bg-gray-900 pb-20 sm:mt-56 sm:pb-24 xl:pb-0">
//             <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
//                 <div class="absolute left-[calc(50%-19rem)] top-[calc(50%-36rem)] transform-gpu blur-3xl">
//                     <div class="aspect-[1097/1023] w-[68.5625rem] bg-gradient-to-r from-[#ff4694] to-[#776fff] opacity-25"
//                         style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
//                 </div>
//             </div>
//             <div
//                 class="mx-auto flex max-w-7xl flex-col items-center gap-x-8 gap-y-10 px-6 sm:gap-y-8 lg:px-8 xl:flex-row xl:items-stretch">
//                 <div class="-mt-8 w-full max-w-2xl xl:-mb-8 xl:w-96 xl:flex-none">
//                     <div class="relative aspect-[2/1] h-full md:-mx-8 xl:mx-0 xl:aspect-auto">
//                         <img class="absolute inset-0 h-full w-full rounded-2xl bg-gray-800 object-cover shadow-2xl"
//                             src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=2102&q=80"
//                             alt="" />
//                     </div>
//                 </div>
//                 <div class="w-full max-w-2xl xl:max-w-none xl:flex-auto xl:px-16 xl:py-24">
//                     <figure class="relative isolate pt-6 sm:pt-12">
//                         <svg viewBox="0 0 162 128" fill="none" aria-hidden="true"
//                             class="absolute left-0 top-0 -z-10 h-32 stroke-white/20">
//                             <path id="b56e9dab-6ccb-4d32-ad02-6b4bb5d9bbeb"
//                                 d="M65.5697 118.507L65.8918 118.89C68.9503 116.314 71.367 113.253 73.1386 109.71C74.9162 106.155 75.8027 102.28 75.8027 98.0919C75.8027 94.237 75.16 90.6155 73.8708 87.2314C72.5851 83.8565 70.8137 80.9533 68.553 78.5292C66.4529 76.1079 63.9476 74.2482 61.0407 72.9536C58.2795 71.4949 55.276 70.767 52.0386 70.767C48.9935 70.767 46.4686 71.1668 44.4872 71.9924L44.4799 71.9955L44.4726 71.9988C42.7101 72.7999 41.1035 73.6831 39.6544 74.6492C38.2407 75.5916 36.8279 76.455 35.4159 77.2394L35.4047 77.2457L35.3938 77.2525C34.2318 77.9787 32.6713 78.3634 30.6736 78.3634C29.0405 78.3634 27.5131 77.2868 26.1274 74.8257C24.7483 72.2185 24.0519 69.2166 24.0519 65.8071C24.0519 60.0311 25.3782 54.4081 28.0373 48.9335C30.703 43.4454 34.3114 38.345 38.8667 33.6325C43.5812 28.761 49.0045 24.5159 55.1389 20.8979C60.1667 18.0071 65.4966 15.6179 71.1291 13.7305C73.8626 12.8145 75.8027 10.2968 75.8027 7.38572C75.8027 3.6497 72.6341 0.62247 68.8814 1.1527C61.1635 2.2432 53.7398 4.41426 46.6119 7.66522C37.5369 11.6459 29.5729 17.0612 22.7236 23.9105C16.0322 30.6019 10.618 38.4859 6.47981 47.558L6.47976 47.558L6.47682 47.5647C2.4901 56.6544 0.5 66.6148 0.5 77.4391C0.5 84.2996 1.61702 90.7679 3.85425 96.8404L3.8558 96.8445C6.08991 102.749 9.12394 108.02 12.959 112.654L12.959 112.654L12.9646 112.661C16.8027 117.138 21.2829 120.739 26.4034 123.459L26.4033 123.459L26.4144 123.465C31.5505 126.033 37.0873 127.316 43.0178 127.316C47.5035 127.316 51.6783 126.595 55.5376 125.148L55.5376 125.148L55.5477 125.144C59.5516 123.542 63.0052 121.456 65.9019 118.881L65.5697 118.507Z" />
//                             <use href="#b56e9dab-6ccb-4d32-ad02-6b4bb5d9bbeb" x="86" />
//                         </svg>
//                         <blockquote class="text-xl font-semibold leading-8 text-white sm:text-2xl sm:leading-9">
//                             <p>
//                                 Aiku surpassed my expectations with their exceptional warehouse service. Their flexible storage options accommodated my varying needs effortlessly.
//                                 Security measures were robust, providing peace of mind for my stored items.
//                                 The convenience of 24/7 access was invaluable for managing my inventory efficiently.
//                                 Overall, Aiku offers a top-tier solution for anyone seeking reliable warehouse services.
//                             </p>
//                         </blockquote>
//                         <figcaption class="mt-8 text-base">
//                             <div class="font-semibold text-white">Judith Black</div>
//                             <div class="mt-1 text-gray-400">CEO of Tuple</div>
//                         </figcaption>
//                     </figure>
//                 </div>
//             </div>
//         </div> -->

//         <!-- FAQ section -->
//         <div class="mx-auto mt-32 max-w-7xl px-6 sm:mt-32 lg:px-8 pb-8">
//             <div class="mx-auto max-w-4xl divide-y divide-gray-900/10">
//                 <h2 class="text-2xl font-bold leading-10 tracking-tight text-gray-900">Frequently asked questions</h2>
//                 <dl class="mt-10 space-y-6 divide-y divide-gray-900/10">
//                     <Disclosure as="div" v-for="faq in faqs" :key="faq.question" class="pt-6" v-slot="{ open }">
//                         <dt>
//                             <DisclosureButton class="flex w-full items-start justify-between text-left text-gray-900">
//                                 <span class="text-base font-semibold leading-7">{{ faq.question }}</span>
//                                 <span class="ml-6 flex h-7 items-center">
//                                     <!-- <FontAwesomeIcon v-if="!open" icon='fal fa-plus' class='h-6 aspect-square' fixed-width aria-hidden='true' />
//                                     <FontAwesomeIcon v-else icon='fal fa-minus' class='h-6 aspect-square' fixed-width aria-hidden='true' /> -->
//                                 </span>
//                             </DisclosureButton>
//                         </dt>
//                         <DisclosurePanel as="dd" class="mt-2 pr-12">
//                             <p class="text-base leading-7 text-gray-600">{{ faq.answer }}</p>
//                         </DisclosurePanel>
//                     </Disclosure>
//                 </dl>
//             </div>
//         </div>
</script>

<template>
    <!-- <Head title="Warehouse Solution" /> -->

    <div class="bg-white pb-20">
        Hellooooo
    </div>
</template>
