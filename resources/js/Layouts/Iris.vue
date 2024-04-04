<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import Header from '@/Layouts/Iris/Header.vue'
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import Footer from '@/Layouts/Iris/Footer.vue'

</script>

<template>
    <div class="relative">
        <div class="container max-w-7xl mx-auto shadow-xl">
            <!-- Section: Top header -->
            <Header />

            <!-- Section: Navigation Tab -->
            <NavigationMenu />
            
            <!-- Main Content -->
            <main
                class="text-gray-700">
                <slot />
            </main>

            <Footer />
        </div>
    </div>

    <!-- Global declaration: Notification -->
    <notifications dangerously-set-inner-html :max="3" width="500" classes="custom-style-notification" :pauseOnHover="true">
        <template #body="props">
            <Notification :notification="props" />
        </template>
    </notifications>
</template>