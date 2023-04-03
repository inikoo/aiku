<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps(['data'])

</script>

<template>

    <div
        class="bg-white bg-[url('@/../../public/art/HomeHeroPlantImage.jpeg')]  w-full h-[50rem] bg-auto bg-repeat-round">
        <header class="">
            <div class="-mt-48">
                <div class="mx-auto max-w-7xl py-24 text-black sm:px-6 sm:py-32 lg:px-8">
                    <div class="px-6 py-24 text-center sm:rounded-3xl sm:px-16">
                        <h2
                            class="mx-auto max-w-2xl text-6xl font-bold tracking-widest font-light capitalize">
                                {{data.main.title}}
                        </h2>
                        <p class="mx-auto mt-6 max-w-xl text-lg leading-6 font-light tracking-wider antialiased">
                            {{data.main.paragraph}}
                        </p>
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            <a href="#">
                                <PrimaryButton>{{data.main.button}}</PrimaryButton>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </header>
    </div>

</template>
