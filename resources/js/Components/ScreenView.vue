<script setup lang="ts">
import { ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faDesktop, faMobileAndroidAlt, faTabletAndroidAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faDesktop, faMobileAndroidAlt, faTabletAndroidAlt)

const props = withDefaults(defineProps<{
    currentView?: string
}>(), {
    currentView: 'desktop'
})

defineEmits<{
    (e: 'screenView', deviceType: string): void
}>()

const screenView = ref(props.currentView)

</script>

<template>
    <div class="flex">
        <div
            class="py-1 px-2 cursor-pointer" title="Mobile view"
            :class="[screenView == 'mobile' ? 'bg-gray-300/70' : 'hover:bg-gray-200/60']"
            @click="screenView = 'mobile', $emit('screenView', 'mobile')"
            v-tooltip="'Mobile view'"
        >
            <FontAwesomeIcon icon='fal fa-mobile-android-alt' class='' aria-hidden='true' />
        </div>
        <div
            class="py-1 px-2 cursor-pointer" title="Tablet view"
            :class="[screenView == 'tablet' ? 'bg-gray-300/70' : 'hover:bg-gray-200/60']"
            @click="screenView = 'tablet', $emit('screenView', 'tablet')"
            v-tooltip="'Tablet view'"
        >
            <FontAwesomeIcon icon='fal fa-tablet-android-alt' class='' aria-hidden='true' />
        </div>
        <div
            class="py-1 px-2 cursor-pointer" title="Desktop view"
            :class="[screenView == 'desktop' ? 'bg-gray-300/70' : 'hover:bg-gray-200/60']"
            @click="screenView = 'desktop', $emit('screenView', 'desktop')"
            v-tooltip="'Desktop view'"
        >
            <FontAwesomeIcon icon='fal fa-desktop' class='' aria-hidden='true' />
        </div>
    </div>
</template>
