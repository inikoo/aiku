<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import IrisHeader from '@/Layouts/Iris/Header.vue'
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import Footer from '@/Layouts/Iris/Footer.vue'

const props = defineProps<{
    header: {
        header_name: string
        header_data: {
            name: string
            data: {
                imgLogo?: string
                title?: string
                description?: string
            }
        }[]
    }
}>()

console.log('props', props.header)

const headerData = {
    header_name: 'HeaderTypeA1',
    header_data: [
        {
            name: 'Image1',
            data: {
                imgLogo: 'https://www.aw-fulfilment.co.uk/wi.php?id=1837721'
            }
        },
        {
            name: 'Headline1',
            data: {
                title: "Your UxxxxxxK's Best Fulfilment",
                description: "Stqqqqqqqqorage - Pick & Pack - Distribution"
            }
        },
    ]
}

</script>

<template>
    <div class="relative">
        <div class="container max-w-7xl mx-auto shadow-xl">
            <!-- Section: Top header -->
            <IrisHeader :data="header" />

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