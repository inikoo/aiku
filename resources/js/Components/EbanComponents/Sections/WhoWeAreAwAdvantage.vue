<script setup>
import YourAdvantagePenImage from "@/../../public/Images/YourAdvantagePenImage.png";

defineProps(['data'])

</script>

<template>
    <div class=" bg-white  h-auto  xl:h-screen md:flex lg:flex">
        <div class="relative h-56 bg-indigo-600 sm:h-72 md:h-auto md:left-0  md:w-1/2 lg:w-1/2 xl:w-1/2">
            <img class="h-full w-full object-cover"
                 :src="YourAdvantagePenImage"
                 alt=""/>
        </div>
        <div class="text-center mt-10 sm:w-full md:w-1/2 lg:w-1/2 xl:w-1/2 pl-20 pr-20">
            <h2 class="font-bold text-6xl">
                {{ data.title }}
            </h2>
            <br>
            <p>
                   {{data.paragraph}}
            </p>

        </div>

    </div>
</template>
