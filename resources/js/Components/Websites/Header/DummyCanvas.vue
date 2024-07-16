<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import MenuPreviewMode from '@/Components/CMS/Workshops/Menu/PreviewMode.vue';
import { navigation as MenuDescriptor } from '@/Components/Websites/Menu/Descriptor'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const props = defineProps<{
}>()

</script>

<template>
    <div class="opacity-25 cursor-not-allowed">
        <!-- <MenuPreviewMode :navigations="MenuDescriptor" :useHeader="false" /> -->

        <div class="bg-white shadow-md border-b-2 border-gray-700">
            <div class="container mx-auto flex flex-col justify-between items-center">

                <!-- Section: Navigation list horizontal -->
                <nav class="relative flex text-sm text-gray-600">
                    <div v-for="(navigation, idxNavigation) in MenuDescriptor" href="#" class="group w-full ">
                        <div class="px-5   flex items-center justify-center gap-x-1 h-full cursor-not-allowed">
                            <div class="w-fit text-center">{{ navigation.label }}</div>
                            <FontAwesomeIcon icon="fas fa-chevron-down" class="text-[11px]"></FontAwesomeIcon>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

    </div>

</template>


<style scss></style>
