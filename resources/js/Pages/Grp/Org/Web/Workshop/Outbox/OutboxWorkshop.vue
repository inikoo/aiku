<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Unlayer from "@/Components/CMS/Website/Outboxes/Unlayer/UnlayerV2.vue"
import Beetree from '@/Components/CMS/Website/Outboxes/Beetree.vue'
import Publish from "@/Components/Publish.vue"

import { PageHeading as TSPageHeading } from "@/types/PageHeading";
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow } from '@fal'
import { routeType } from '@/types/route'

library.add(faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow)

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    builder: String
    imagesUploadRoute : routeType
    updateRoute : routeType
    snapshot : routeType
    apiKey : {
        client_id : string,
        client_secret : string,
        grant_type : string
    }
}>()

const comment = ref('')
const isLoading = ref(false)
const openTemplates = ref(false)

</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-publish="{ action }">
			<Publish
				:isLoading="isLoading"
				:is_dirty="true"
				v-model="comment"
			/>
		</template>
     <!--    <template #other>
            <Button @click="openTemplates = true" icon="fas fa-th-large" label="Templates" :style="'tertiary'"  />
        </template> -->
    </PageHeading>

     <!-- beefree -->
    <Beetree 
        v-if="builder == 'beefree'"
        :updateRoute="updateRoute" 
        :imagesUploadRoute="imagesUploadRoute" 
        :snapshot="snapshot"
        :apiKey="apiKey"
    />

    <!-- unlayer -->
    <Unlayer 
        v-if="builder == 'unlayer'"
        :updateRoute="updateRoute" 
        :imagesUploadRoute="imagesUploadRoute" 
        :snapshot="snapshot"
    />

   <!--  <Modal :isOpen="openTemplates" @onClose="openTemplates = false" width="w-[600px]">
        <div class="overflow-y-auto">
            <TemplateMailshot @changeTemplate="changeTemplate" :mailshot="mailshot"/>
        </div>
    </Modal> -->
</template>
