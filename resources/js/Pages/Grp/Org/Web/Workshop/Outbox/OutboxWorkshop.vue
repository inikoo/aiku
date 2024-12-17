<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Unlayer from "@/Components/CMS/Website/Outboxes/Unlayer/UnlayerV2.vue"
import Beetree from '@/Components/CMS/Website/Outboxes/Beefree.vue'
import Publish from "@/Components/Publish.vue"
import { notify } from '@kyvg/vue3-notification'
import axios from 'axios'

import { PageHeading as TSPageHeading } from "@/types/PageHeading";
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow } from '@fal'
import { routeType } from '@/types/route'
import EmptyState from '@/Components/Utils/EmptyState.vue'

library.add(faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow)

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    builder: String
    imagesUploadRoute : routeType
    updateRoute : routeType
    snapshot : routeType
    publishRoute : routeType
    apiKey : {
        client_id : string,
        client_secret : string,
        grant_type : string
    }
}>()

const comment = ref('')
const isLoading = ref(false)
const openTemplates = ref(false)
const _beefree = ref()
const _unlayer = ref()

console.log(props)

const onSendPublish = async (data) => {
    console.log(data)
    try {
        const response = await axios.post(route(props.publishRoute.name, props.publishRoute.parameters), {
            comment: comment.value,
            layout: JSON.parse(data?.jsonFile),
            compiled_layout: data?.htmlFile
        });
        console.log("Publish response:", response.data);
    } catch (error) {
        console.log(error)
        const errorMessage = error.response?.data?.message || error.message || "Unknown error occurred";
        notify({
            title: "Something went wrong.",
            text: errorMessage,
            type: "error",
        });
    } finally {
        isLoading.value = false;
    }
}



const onPublish = (popover: {}) => {
    if (props.builder === 'beefree' && _beefree.value?.beeInstance) {
        _beefree.value.beeInstance.save()
        popover.close();
    }
};


</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <Publish
				:isLoading="isLoading"
				:is_dirty="true"
				v-model="comment"
                @onPublish="(popover) => onPublish(popover)"
			/>
        </template>
    </PageHeading>

     <!-- beefree -->
    <Beetree 
        v-if="builder == 'beefree'"
        :updateRoute="updateRoute" 
        :imagesUploadRoute="imagesUploadRoute" 
        :snapshot="snapshot"
        :apiKey="apiKey"
        @onSave="onSendPublish"
        ref="_beefree"
    />

    <!-- unlayer -->
    <Unlayer 
        v-else-if="builder == 'unlayer'"
        :updateRoute="updateRoute" 
        :imagesUploadRoute="imagesUploadRoute" 
        :snapshot="snapshot"
        ref="_unlayer"
    />

    <div v-else>
        <EmptyState 
            :data="{
                title : 'Builder Not Set Up',
                description : 'you neeed to set up the builder'
            }"
        />
    </div>

   <!--  <Modal :isOpen="openTemplates" @onClose="openTemplates = false" width="w-[600px]">
        <div class="overflow-y-auto">
            <TemplateMailshot @changeTemplate="changeTemplate" :mailshot="mailshot"/>
        </div>
    </Modal> -->
</template>
