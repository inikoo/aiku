<script setup>
import YourAdvantagePenImage from "@/../../public/Images/YourAdvantagePenImage.png";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps(['data'])

</script>

<template>

    <div class="relative bg-[#dbdbdb] h-screen">
        <div class="relative h-56 bg-indigo-600 sm:h-72 md:absolute md:left-0 md:h-full md:w-1/2">
            <img class="h-full w-full object-cover"
                 :src="YourAdvantagePenImage"
                 alt=""/>
        </div>
        <div class="relative mx-auto max-w-md py-12 px-6 sm:max-w-7xl sm:py-20 md:py-28 lg:px-8 lg:py-32">
            <div class="md:ml-auto md:w-1/2 md:pl-10 ">
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-black sm:text-4xl">{{data.main.title}}</h2>
                <p class="mt-3 text-lg text-black">{{data.main.paragraph}}</p>
                <div class="mt-8">
                    <div class="inline-flex rounded-md">
                        <a href="#"
                           class="px-5 py-3 text-base">
                            <PrimaryButton>{{data.main.button}}</PrimaryButton>
                            <ArrowTopRightOnSquareIcon class="-mr-1 ml-3 h-5 w-5 text-gray-400" aria-hidden="true"/>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
