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
import Dialog from 'primevue/dialog';
import PureInput from "@/Components/Pure/PureInput.vue";
import Button from "@/Components/Elements/Buttons/Button.vue";
import SelectButton from 'primevue/selectbutton';
import { trans } from "laravel-vue-i18n"

import { PageHeading as TSPageHeading } from "@/types/PageHeading";
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow, faPaperPlane, faCheckCircle, faCircle } from '@fal'
import { routeType } from '@/types/route'
import EmptyState from '@/Components/Utils/EmptyState.vue'
import PureRadio from '@/Components/Pure/PureRadio.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faArrowAltToTop, faArrowAltToBottom, faTh, faBrowser, faCube, faPalette, faCheeseburger, faDraftingCompass, faWindow)

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    builder: String
    imagesUploadRoute : routeType
    updateRoute : routeType
    snapshot : routeType
    status : string
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
const visibleEmailTestModal = ref(false)
const visibleSAveEmailTemplateModal = ref(false)
const email = ref('')
const templateName = ref('')
const temporaryData = ref()
const active = ref(props.status)
const options = ref([
    { name: 'Active', value: "active" },
    { name: 'Suspended', value: "suspended" },
]);

const onSendPublish = async (data) => {
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

const openSendTest = (data) => {
    visibleEmailTestModal.value = true
    temporaryData.value = {
        layout : data?.jsonFile,
        compiled_layout: data?.htmlFile
    }
}

const sendTestToServer = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post('xxx', 
            {...temporaryData.value, email : comment.value}
        );
        console.log("sendTest response:", response.data);
    } catch (error) {
        console.error("Error in sendTest:", error);
        visibleEmailTestModal.value = false
        temporaryData.value = null
        const errorMessage = error.response?.data?.message || error.message || "An unknown error occurred.";
        notify({
            title: "Something went wrong",
            text: errorMessage,
            type: "error",
        });
    } finally {
        isLoading.value = false;
    }
};


const saveTemplate = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post('xxx', {
            email: comment.value,
        });
        console.log("sendTest response:", response.data);
    } catch (error) {
        console.error("Error in sendTest:", error);
        const errorMessage = error.response?.data?.message || error.message || "An unknown error occurred.";
        notify({
            title: "Something went wrong",
            text: errorMessage,
            type: "error",
        });
    } finally {
        isLoading.value = false;
    }
}

const updateActiveValue = async (action) => {
    router.patch(route(action.name, action.parameters),
        { active: active.value },
        {
            onStart: () => console.log('start'),
            onSuccess: () => {
                notify({
                    title: trans('Success!'),
                    text: trans('change status'),
                    type: 'success',
                })
            },
            onError: () => {
                notify({
                    title: trans('Something went wrong'),
                    text: trans('Unsuccessfully change status'),
                    type: 'error',
                })
            },
            onFinish: () => console.log('finish'),
        }
    )
} 


</script>


<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <Publish :isLoading="isLoading" :is_dirty="true" v-model="comment"
                @onPublish="(popover) => onPublish(popover)" />
        </template>
        <template #button-toogle="action">
            <div class=" rounded-md px-2 py-1">
                <SelectButton 
                    v-model="active" 
                    :options="options" 
                    optionLabel="name" 
                    optionValue="value"
                    :unstyled="true"
                    @update:model-value="(e) => updateActiveValue(action.action.route)" 
                    >
                    <template #option="slotProps">
                        <div class="px-3 py-1 text-center cursor-pointer transition duration-300 font-medium"
                            :class="[
                                active == slotProps.option.value ? 
                                slotProps.option.value == 'active' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                                slotProps.index == 0 ? 'rounded-l-lg ' : 'rounded-r-lg '
                            ]">
                            <div>
                                <FontAwesomeIcon :icon="active == slotProps.option.value ? faCheckCircle : faCircle" class="mx-2" />
                                <span class="mx-1">{{ slotProps.option.name }}</span> 
                            </div>
                           
                          
                        </div>
                    </template>
                </SelectButton>
            </div>
        </template>
    </PageHeading>

    <!-- beefree -->
    <Beetree v-if="builder == 'beefree'" :updateRoute="updateRoute" :imagesUploadRoute="imagesUploadRoute"
        :snapshot="snapshot" :apiKey="apiKey" @onSave="onSendPublish" @sendTest="openSendTest"
        @saveTemplate="visibleSAveEmailTemplateModal = true" ref="_beefree" />

    <!-- unlayer -->
    <Unlayer v-else-if="builder == 'unlayer'" :updateRoute="updateRoute" :imagesUploadRoute="imagesUploadRoute"
        :snapshot="snapshot" ref="_unlayer" />

    <div v-else>
        <EmptyState :data="{
                title : 'Builder Not Set Up',
                description : 'you neeed to set up the builder'
            }" />
    </div>

    <Dialog v-model:visible="visibleEmailTestModal" modal :closable="false" :showHeader="false"
        :style="{ width: '25rem' }">
        <div class="pt-4">
            <div class="font-semibold w-24 mb-3">Email</div>
            <PureInput v-model="email" placeholder="example@gmail.com" />
            <div class="flex justify-end mt-3 gap-3">
                <Button :type="'tertiary'" label="Cancel" @click="visibleEmailTestModal = false"></Button>
                <Button @click="sendTestToServer" :icon="faPaperPlane" label="Send"></Button>
            </div>
        </div>
    </Dialog>

    <Dialog v-model:visible="visibleSAveEmailTemplateModal" modal :closable="false" :showHeader="false"
        :style="{ width: '25rem' }">
        <div class="pt-4">
            <div class="font-semibold mb-3">Template Name</div>
            <PureInput v-model="templateName" placeholder="Template Name" />
            <div class="flex justify-end mt-3 gap-3">
                <Button :type="'tertiary'" label="Cancel" @click="visibleSAveEmailTemplateModal = false"></Button>
                <Button type="save"></Button>
            </div>
        </div>
    </Dialog>

    <!--  <Modal :isOpen="openTemplates" @onClose="openTemplates = false" width="w-[600px]">
        <div class="overflow-y-auto">
            <TemplateMailshot @changeTemplate="changeTemplate" :mailshot="mailshot"/>
        </div>
    </Modal> -->
</template>
