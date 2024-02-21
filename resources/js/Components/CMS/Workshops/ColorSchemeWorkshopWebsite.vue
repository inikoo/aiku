<script setup lang='ts'>
const props = defineProps<{
    color: string[]
}>()
</script>

<template>
    <div class="pb-6 h-fit rounded-md overflow-hidden border-2 border-gray-400">
        <!-- Section: Topbar  -->
        <div class="flex items-center justify-between h-10 px-4" :style="{backgroundColor: color[0], color: color[1]}">
            <div class="bg-gray-100 text-black border border-gray-400 px-0.5 aspect-square rounded-sm flex items-center text-[8px]">
                Logo
            </div>

            <div class="flex justify-center gap-x-2">
                <div class="relative px-1 flex justify-center">
                    <div class="text-xs">Menu</div>
                    <div class="absolute -bottom-0.5 h-[2px] w-full rounded-full" :style="{backgroundColor: color[2]}"></div>
                </div>
                <div class="relative px-1 flex justify-center">
                    <div class="text-xs">Product</div>
                </div>
                <div class="relative px-1 flex justify-center">
                    <div class="text-xs">Contact Us</div>
                    <div class="absolute -bottom-0.5 h-[2px] w-full rounded-full" :style="{backgroundColor: color[2] + '33'}"></div>
                </div>
            </div>

            <div class="text-xxs flex gap-x-1.5">
                <div>Register</div>
                <div>Login</div>
            </div>
        </div>

        <!-- Section: Headline -->
        <div class="mt-3 font-black mx-auto text-center text-xl whitespace-pre-line px-4" :style="{color: color[0]}">
            Lorem ipsum dolor, sit
            amet consectetur adipisicing elit!
        </div>

        <!-- Section: Card -->
        <div class="mt-6 flex justify-center items-center scale-75">
            <div class="-ml-4 px-3 py-3 rounded-md w-44" :style="{color: color[2], border: `1px solid ${color[2]}`}">
                <div class="text-xs">Basic</div>
                <div class="text-xl font-black">$74<span class="text-xs font-normal">/month</span></div>
                <div class="text-xxs mt-2">Ideal for small businesses or startups with moderate storage needs.</div>
                <div class="mt-3 rounded w-fit px-3 text-xs" :style="{border: `1px solid ${color[2]}`, color: color[2]}">Buy</div>
            </div>
            <div class="scale-125 px-3 py-3 rounded-md w-44" :style="{backgroundColor: color[2], color: color[3]}">
                <div class="text-xs">Pro</div>
                <div class="text-xl font-black">$279<span class="text-xs font-normal">/month</span></div>
                <div class="text-xxs mt-2">Perfect for growing businesses with increased storage demands.</div>
                <div class="mt-3 text-white rounded w-fit px-3 text-xs" :style="{backgroundColor: color[3], color: color[2]}">Buy</div>
            </div>
        </div>

    </div>
</template>