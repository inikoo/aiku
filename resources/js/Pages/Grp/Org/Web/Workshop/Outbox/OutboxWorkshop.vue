<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Unlayer from "@/Components/Websites/Outboxes/Unlayer.vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import Modal from "@/Components/Utils/Modal.vue"
import TemplateMailshot from '@/Components/Websites/Outboxes/Templates/TemplateMailshot.vue'


import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow } from '@fal'

library.add(faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow)

const props = defineProps<{
    title: string,
    pageHead: {}
    imagesUploadRoute: routeType
    updateRoute: routeType
    emailTemplate: routeType
    publishRoute: routeType
    loadRoute: routeType
}>()

const openTemplates = ref(false)

console.log(props)

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <Button @click="openTemplates = true" icon="fas fa-th-large" label="Templates" :style="'tertiary'"  />
        </template>
    </PageHeading>
    <Unlayer :updateRoute="updateRoute" :loadRoute="loadRoute" :imagesUploadRoute="imagesUploadRoute" :mailshot="{}"/>

    <Modal :isOpen="openTemplates" @onClose="openTemplates = false" width="w-[600px]">
        <div class="overflow-y-auto">
            <TemplateMailshot @changeTemplate="changeTemplate" :mailshot="mailshot"/>
        </div>
        </Modal>
</template>
