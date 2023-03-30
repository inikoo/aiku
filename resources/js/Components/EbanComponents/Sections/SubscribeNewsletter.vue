<script setup>
import CavinContactPageImage from "@/../../public/Images/CavinContactPageImage.png";
import MountainWaterContactPageImage from "@/../../public/Images/MountainWaterContactPageImage.png";
import MountainFieldContactPageImage from "@/../../public/Images/MountainFieldContactPageImage.png";

defineProps(['data'])
</script>

<template>
        <div class="grid grid-cols-4 gap-4 h-auto text-center bg-gray-50 items-center mt-6 hidden md:grid lg:grid">
            <div class="grid object-center ">
                <img class="  object-cover"
                     :src="CavinContactPageImage"
                     alt=""/>
            </div>
            <div class="grid"></div>
            <div class="grid"></div>
            <div class="grid"></div>
            <div class="grid"></div>
            <div class=" col-span-2 grid ">
                <div class="">
                    <div class="mx-auto max-w-7xl py-12 px-6 lg:py-16 lg:px-8">
                        <h2 class="inline text-3xl font-bold tracking-tight text-gray-900 sm:block sm:text-4xl">
                            {{data.title}}
                        </h2>
                        <form class="mt-8 sm:flex xl:ml-12 2xl:ml-32">
                            <label for="email-address" class="sr-only">Email address</label>
                            <input id="email-address" name="email" type="email" autocomplete="email" required=""
                                   class="w-full rounded-md border-gray-300 px-5 py-3 placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs"
                                   placeholder="Enter your email"/>
                            <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3 sm:flex-shrink-0">
                                <button type="submit"
                                        class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-5 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    {{data.button}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="grid">
                <img class="  object-cover"
                     :src="MountainWaterContactPageImage"
                     alt=""/>
            </div>
            <div class="grid"></div>
            <div class="grid">
                <img class="  object-cover"
                     :src="MountainFieldContactPageImage"
                     alt=""/></div>
            <div class="grid"></div>
            <div class="grid"></div>
        </div>
</template>
