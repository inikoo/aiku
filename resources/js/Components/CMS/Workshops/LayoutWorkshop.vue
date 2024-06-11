<script setup lang='ts'>
import { Link } from '@inertiajs/vue3'
import { routeType } from '@/types/route'
const props = defineProps<{
    data: {
        footerRoute: routeType,
        headerRoute: routeType,
    
    }
}>()

console.log(props.data)

</script>

<template>
    <div class="max-w-2xl mx-auto mt-8 shadow-2xl">
        <Link as="div" href="" class="bg-orange-100 hover:bg-orange-400 cursor-pointer w-full h-8 text-center">
            Notification
        </Link>

        <Link as="div" :href="data?.headerRoute?.name ? route(data.headerRoute?.name, data.headerRoute?.parameters) : '#'"
            class="bg-lime-100 hover:bg-lime-400 cursor-pointer w-full h-40 text-4xl flex items-center justify-center">
            Header
        </Link>

        <div class="bg-slate-100 w-full h-[400px] grid grid-cols-9">
            <div
                class="bg-indigo-100 hover:bg-indigo-400 cursor-pointer col-span-2 flex justify-center items-center text-2xl">
                <span class="-rotate-90">Menu left</span>
            </div>

            <div class="col-span-5 flex flex-col cursor-not-allowed">
                <div class="bg-slate-100 h-16 flex justify-center items-center text-2xl">
                    Content Header
                </div>

                <div class="bg-slate-300 flex flex-col justify-center items-center text-4xl h-full">
                    Content
                </div>

                <div class="bg-slate-100 h-24 flex justify-center items-center text-2xl">
                    Content Footer
                </div>
            </div>

            <div
                class="bg-violet-100 hover:bg-violet-400 cursor-pointer col-span-2 flex justify-center items-center text-2xl">
                <span class="-rotate-90">Menu right</span>
            </div>
        </div>

        <Link :href="data?.data?.footerRoute?.name ? route(data.footerRoute?.name, data.footerRoute?.parameters) : '#'"
            class="bg-blue-100 hover:bg-blue-400 cursor-pointer w-full h-24 flex justify-center items-center text-2xl">
            Footer
        </Link>
    </div>
</template>