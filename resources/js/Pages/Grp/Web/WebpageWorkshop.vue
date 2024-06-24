<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import { library } from '@fortawesome/fontawesome-svg-core'
import { ref } from 'vue'
import { faBrowser, faDraftingCompass, faRectangleWide, faStars } from '@fal'
import draggable from "vuedraggable"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Modal from "@/Components/Utils/Modal.vue"
import BlockList from '@/Components/Fulfilment/Website/Block/BlockList.vue'
import { getComponent } from '@/Components/Fulfilment/Website/BlocksList'
import axios from 'axios'
import debounce from 'lodash/debounce'
import Publish from '@/Components/Publish.vue'
import { notify } from "@kyvg/vue3-notification"

library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars)

const props = defineProps<{
  title: string,
  pageHead: PageHeadingTypes,
  webpage: Object
  webBlockTypes :  {
        data : Array
    }
}>();

console.log(props)


const isModalBlocksList = ref(false)
const comment = ref("");
const isLoading = ref(false)
const selectedBlock = ref(null)
const data = ref({
  ...props.webpage,
  layout: props.webpage.layout.web_blocks ? props.webpage.layout.web_blocks : []
});


const sendUpdate = async (block) => {
  try {
    const response = await axios.post(
      route(props.webpage.add_web_block_route.name, props.webpage.add_web_block_route.parameters), 
      {web_block_type_id : block.id }
    );
  /*   const set = {...response.data.data, layout : response.data.data.layout.blocks }
    data.value = set */
    console.log('saved', response);
  } catch (error: any) {
    console.error('error', error);
  }
};


/* const debouncedSendUpdate = debounce(sendUpdate, 1000);

const onUpdated = () => {
  debouncedSendUpdate();
}; */



const onPickBlock = (block) => {
	sendUpdate(block)
	isModalBlocksList.value = false;
};

const deleteBlock = (index) => {
  data.value.layout.splice(index, 1);
/*   onUpdated(); */
};

const setData = () => {
  console.log(data.value);
};


const onPublish = async (action) => {
  try {
    // Ensure action is defined and has necessary properties
    if (!action || !action.method || !action.name || !action.parameters) {
      throw new Error('Invalid action parameters');
    }

    isLoading.value = true;

    // Make sure route and axios are defined and used correctly
    const response = await axios[action.method](route(action.name, action.parameters), {
      comment: comment.value,
      publishLayout: {blocks : data.value.layout }
    });


  } catch (error) {
    // Ensure the error is logged properly
    console.error('Error:', error);

    // Ensure the error notification is user-friendly
    const errorMessage = error.response?.data?.message || error.message || 'Unknown error occurred';
    notify({
      title: 'Something went wrong.',
      text: errorMessage,
      type: 'error',
    });
  } finally {
    // Ensure loading state is updated
    isLoading.value = false;
  }
};



</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #button-publish="{ action }">
			<!--  <Action v-if="action.action" :action="action.action" :dataToSubmit="data" /> -->
			<Publish
				:isLoading="isLoading"
				:is_dirty="data.is_dirty"
				v-model="comment"
				@onPublish="onPublish(action.action.route)" />
		</template>
	</PageHeading>

	<div class="mx-auto px-4 py-4 sm:px-6 lg:px-8 w-full h-screen">
		<div class="mx-auto grid grid-cols-4 gap-1 lg:mx-0 lg:max-w-none">
			<div class="col-span-3 h-screen overflow-auto border-2 border-dashed">
				<div v-if="data.layout?.length">
					<TransitionGroup tag="div" name="zzz" class="relative">
                        <div v-for="(activityItem, activityItemIdx) in data.layout"
                            :key="activityItem.id" @click="()=>selectedBlock = activityItem"
                            class="w-full">
                            <component
                                :is="getComponent(activityItem['component'])"
                                :key="activityItemIdx"
                                :webpageData="webpage"
                                v-bind="activityItem.fieldData"
                                v-model="activityItem.fieldValue"
                                @autoSave="() => onUpdated()" />
                        </div>
                    </TransitionGroup>
				</div>

                <div v-else
					class="relative block w-full h-full border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
					<font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
					<span class="mt-2 block text-sm font-semibold" >You dont have block</span
					>
				</div>
			</div>
			<div class="col-span-1 h-screen">
				<div class="border-2 bg-gray-200 p-3 h-full">
					<div class="flex justify-between">
						<h2 class="text-sm font-semibold leading-6">Block List</h2>
						<Button
                            icon="fas fa-plus"
							size="xs"
							@click="() => (isModalBlocksList = true)" />
					</div>
                    
					<draggable v-if="data?.layout?.length > 0"
						:list="data.layout"
						ghost-class="ghost"
						group="column"
						itemKey="column_id"
						class="mt-2 space-y-1">
						<template #item="{ element, index }">

                            <div class="group flex justify-between items-center gap-x-2 relative border border-gray-300 px-3 py-2 rounded cursor-pointer hover:bg-gray-100">
                                <div class="flex gap-x-2">
                                    <div class="flex items-center justify-center">
                                        <FontAwesomeIcon :icon='element.icon' class='' fixed-width aria-hidden='true' />
                                    </div>
                                    <h3 class="text-sm font-medium">
                                        {{ element.name }}
                                    </h3>
                                </div>
                                
                                <div class="py-0 text-xs text-gray-400 hover:text-red-500 px-1 cursor-pointer"
                                    @click="() => deleteBlock(index)">
                                    <font-awesome-icon :icon="['fal', 'times']" />
                                </div>
                            </div>
						</template>
					</draggable>

                    <!-- Section: if no blocks selected -->
					<div v-else
						class="h-fit mt-4 rounded-lg border-2 p-4 text-center border-gray-300"
                    >
						<font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
						<span class="mt-2 block text-sm font-semibold text-gray-600">You dont have block</span
						>
					</div>

                   <!--  <Button
                        type="dashed"
                        icon="fal fa-plus"
                        label="Add block"
                        full
                        size="s"
                        class="mt-2"
                        @click="() => (isModalBlocksList = true)"
                    /> -->
				</div>
			</div>
		</div>
	</div>
	<Modal :isOpen="isModalBlocksList" @onClose="isModalBlocksList = false">
		<BlockList :onPickBlock="onPickBlock" :webBlockTypes="webBlockTypes" />
	</Modal>
	<div @click="setData">see data</div>
</template>
