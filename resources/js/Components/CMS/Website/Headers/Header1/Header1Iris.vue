<script setup lang="ts">
import { ref } from 'vue'

import { getStyles } from "@/Composables/styles";
import { checkVisible, textReplaceVariables } from '@/Composables/Workshop'
import { inject } from 'vue'

import { faPresentation, faCube, faText, faPaperclip } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faImage, faSignInAlt, faFileAlt } from '@fas';
import { faHeart } from '@far';
import Image from "@/Components/Image.vue"
import { routeType } from '@/types/route'

library.add(faPresentation, faCube, faText, faImage, faPaperclip, faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle, faSignInAlt, faFileAlt)

const props = defineProps<{
    fieldValue: {
        headerText: string
        chip_text: string
    }
    loginMode: boolean
}>()

const isLoggedIn = inject('isPreviewLoggedIn', false)




</script>

<template>
    <div class="shadow-sm" :style="getStyles(fieldValue.container.properties)">
        <div class="flex flex-col justify-between items-center py-4 px-6">
            <div class="w-full grid grid-cols-3 items-center gap-6">
                <!-- Logo -->
                <div :style="getStyles(fieldValue.logo.properties)">
                    <img v-if="!fieldValue.logo.source" :src="fieldValue?.logo?.url" :alt="fieldValue?.logo?.alt"
                        :style="{ width: `${fieldValue.logo.width}%` }" />
                    <Image v-else :alt="fieldValue?.logo?.alt" :src="fieldValue?.logo?.source"
                        :style="{ width: `${fieldValue.logo.width}%` }"></Image>
                </div>

                <!-- Search Bar -->
                <div class="relative justify-self-center w-full max-w-md">
                    <input type="text" placeholder="Search Products"
                        class="border border-gray-300 py-2 px-4 rounded-md text-sm w-full shadow-inner focus:outline-none focus:border-gray-500">
                    <FontAwesomeIcon icon="fas fa-search"
                        class="absolute top-1/2 -translate-y-1/2 right-4 text-gray-500" fixed-width />
                </div>

                <!-- Gold Member Button -->
                <div class="justify-self-end w-fit">
                    <div v-if="checkVisible(fieldValue?.button_1?.visible || null, isLoggedIn)"
                        :href="fieldValue?.button_1?.visible" class="space-x-1.5 cursor-pointer whitespace-nowrap" id=""
                        :style="getStyles(fieldValue?.button_1?.container?.properties)">
                        <span v-html="fieldValue?.button_1.text" />
                    </div>
                </div>
            </div>
        </div>        
    </div>
</template>

<style scoped></style>