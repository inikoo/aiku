<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Unlayer from "@/Components/CMS/Website/Outboxes/Unlayer/UnlayerV2.vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import Modal from "@/Components/Utils/Modal.vue"
import TemplateMailshot from '@/Components/CMS/Website/Outboxes/Templates/TemplateMailshot.vue'
import Beetree from '@/Components/CMS/Website/Outboxes/Beetree.vue'


import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow } from '@fal'

library.add(faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow)

const props = defineProps<{
    title: string,
    pageHead: {}
    snapshot: any
}>()

const openTemplates = ref(false)

console.log(props)

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
     <!--    <template #other>
            <Button @click="openTemplates = true" icon="fas fa-th-large" label="Templates" :style="'tertiary'"  />
        </template> -->
    </PageHeading>

     <!-- beefree -->
    <Beetree 
        :updateRoute="{}" 
        :imagesUploadRoute="{}" 
        :snapshot="snapshot" 
    />


    <!-- unlayer -->
    <!-- <Unlayer :updateRoute="updateRoute" :loadRoute="loadRoute" :imagesUploadRoute="imagesUploadRoute" :mailshot="{}" /> -->

   <!--  <Modal :isOpen="openTemplates" @onClose="openTemplates = false" width="w-[600px]">
        <div class="overflow-y-auto">
            <TemplateMailshot @changeTemplate="changeTemplate" :mailshot="mailshot"/>
        </div>
    </Modal> -->
</template>
