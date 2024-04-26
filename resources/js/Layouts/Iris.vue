<script setup lang="ts">
import Notification from '@/Components/Utils/Notification.vue'
import Header from '@/Layouts/Iris/Header.vue'
import NavigationMenu from '@/Layouts/Iris/NavigationMenu.vue'
import Footer from '@/Layouts/Iris/Footer.vue'

const props = defineProps<{
    data: {
        components: [
            {
                type: string,
                content: {
                    imgLogo: string
                    title: string
                    description: string
                }
            }
        ]
    }
}>()

// console.log('props', props.data)

const componentList = (componentName: string) => {
    const components: any = {
        'header': Header
    }

    return components[componentName]
}


</script>

<template>
    <div class="relative">
        <div class="container max-w-7xl mx-auto shadow-xl">
            <!-- Section: Top header -->
            <!-- <Header /> -->
            <component v-for="component in data.components" :is="componentList(component.type)" :data="component.content" />

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