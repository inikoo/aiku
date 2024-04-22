<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import Header from '@/Layouts/Iris/Header.vue'
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import Footer from '@/Layouts/Iris/Footer.vue'

// <div class="relative">
//         <div class="container max-w-7xl mx-auto shadow-xl">
//             <!-- Section: Top header -->
//             <Header />

//             <!-- Section: Navigation Tab -->
//             <NavigationMenu />
            
//             <!-- Main Content -->
//             <main
//                 class="text-gray-700">
//                 <slot />
//             </main>

//             <Footer />
//         </div>
//     </div>
</script>

<template>
    <div class="max-w-7xl mx-auto">
        <div class="bg-orange-400 w-full h-8 text-center">
            Notification
        </div>
        <div class="bg-lime-400 w-full h-40 text-7xl text-center">
            Header
        </div>
        <div class="bg-slate-100 w-full h-[900px] grid grid-cols-9">
            <div class="bg-indigo-400 col-span-2 flex justify-center items-center text-4xl">
                <span class="-rotate-90">Menu</span>
            </div>

            <div class="col-span-5 flex flex-col">
                <div class="bg-slate-100 h-16 flex justify-center items-center text-4xl">
                    Content Header
                </div>

                <div class="bg-slate-300 flex flex-col justify-center items-center text-7xl h-full">
                    Content
                </div>
                
                <div class="bg-slate-100 h-24 flex justify-center items-center text-4xl">
                    Content Footer
                </div>
            </div>

            <div class="bg-violet-400 col-span-2 flex justify-center items-center text-4xl">
                <span class="-rotate-90">Aside</span>
            </div>

        </div>

        <div class="bg-blue-500 w-full h-24 flex justify-center items-center text-4xl">
            Footer
        </div>
        
    </div>


    <!-- Global declaration: Notification -->
    <notifications dangerously-set-inner-html :max="3" width="500" classes="custom-style-notification" :pauseOnHover="true">
        <template #body="props">
            <Notification :notification="props" />
        </template>
    </notifications>
</template>