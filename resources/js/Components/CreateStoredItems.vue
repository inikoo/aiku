<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Button from "@/Components/Elements/Buttons/Button.vue"
import { trans } from "laravel-vue-i18n"
import SelectQuery from "@/Components/SelectQuery.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { get } from "lodash"
import { routeType } from "@/types/route"
import { ref } from 'vue'
import Tag from '@/Components/Tag.vue'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus, faChevronDown, faTimes, faMinus, faSparkles } from "@fas"
import { faTrashAlt, faExclamationTriangle } from "@far"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faPlus, faChevronDown, faTimes, faMinus, faTrashAlt, faSparkles, faExclamationTriangle)

const props = defineProps<{
	storedItemsRoute: {
		store: routeType
		index: routeType
		delete: routeType
	}
	form: {}
	stored_items: {}[]
}>()

const loadingAddStoredItem = ref(false)
const newStoredItem = ref(null)
const messageMode = ref(false)
const disabledSelect = ref({
	edit: props.form.id ? true : false,
	disabled: props.form.id ? true : false,
})
const _selectQuery = ref(null)

const emits = defineEmits<{
	(e: 'onSave', event: any): void
	(e: 'closeModal'): void
}>()

const createStoredItems = async (option, select) => {
	loadingAddStoredItem.value = true
	try {
		const response: any = await axios.post(
			route(props.storedItemsRoute.store.name, props.storedItemsRoute.store.parameters),
			{ reference: option.id },
			{ headers: { "Content-Type": "multipart/form-data" } }
		)
		props.form.errors = {}
		props.form.id = response.data.id
		disabledSelect.value.disabled = true
		newStoredItem.value = response.data
		_selectQuery.value._multiselectRef.close()
		loadingAddStoredItem.value = false
	} catch (error: any) {
		props.form.errors.id = error.response.data.message
		_selectQuery.value._multiselectRef.close()
		loadingAddStoredItem.value = false
		notify({
			title: "Failed to add new stored items",
			text: error.message ? error.message : 'failed to create stored item',
			type: "error",
		})
		return false
	}
}

const deleteStoredItems = async (closeModal : boolean) => {
	try {
		const response: any = await axios.delete(
			route(props.storedItemsRoute.delete.name, {storedItem : props.form.id}),
		)
		props.form.errors = {}
		props.form.id = null
		loadingAddStoredItem.value = false
		if(closeModal) emits('closeModal')
		else disabledSelect.value.disabled = false
	} catch (error: any) {
		console.log(error)
		props.form.errors.id = error?.response?.data?.message
		loadingAddStoredItem.value = false
		notify({
			title: "Failed to add new stored items",
			text: error.message ? error.message : 'failed to create stored item',
			type: "error",
		})
		return false
	}
}


const filterOptionsStoredItems = (e) => {
	if (!props.form.id) {
		return e.filter((item) =>
			!props.stored_items.map((storedItem) => storedItem.id).includes(item.id)
		);
	}
	return e;
};

const incrementQuantity = () => {
	props.form.quantity = Number(props.form.quantity) + 1;
}

const decrementQuantity = () => {
	if (props.form.quantity > 1) {
		props.form.quantity = Number(props.form.quantity) - 1;
	}
}

const onCancel = () =>{
	if(newStoredItem.value && !disabledSelect.value.edit) messageMode.value = true
	else emits('closeModal')
}

const onSaved = async () => {
	let newData = []

	if (props.form.oldData) {
		const index = props.stored_items.findIndex((item) => item.id === props.form.oldData.id)
		if (index !== -1) {
			const updatedStoredItems = [...props.stored_items]
			updatedStoredItems.splice(index, 1, props.form.data())
			newData = updatedStoredItems
		}
	} else {
		newData = [...props.stored_items, { ...props.form.data() }]
	}

	const finalData = {}
	newData.forEach((d) => {
		finalData[d.id] = { quantity: d.quantity }
	})

	emits("onSave", finalData)
}

</script>

<template>
	<div v-if="!messageMode">
		<div class="text-center font-semibold text-2xl mb-4">
                {{ disabledSelect.edit ? trans('edit stored item') : trans('Add stored item') }}
            </div>
		<label class="block text-sm font-medium text-gray-700">{{ trans("Reference") }}</label>
		<div class="mt-1">
			<SelectQuery ref="_selectQuery" :filterOptions="filterOptionsStoredItems"
				:urlRoute="route(storedItemsRoute.index.name, storedItemsRoute.index.parameters)" :value="form"
				:placeholder="'Select or add item'" :required="true" :trackBy="'reference'" :label="'reference'"
				:valueProp="'id'" :closeOnSelect="true" :clearOnSearch="false" :fieldName="'id'" :createOption="false"
				:onCreate="createStoredItems" @afterCreate="(value, option) => form['id'] = value"
				:disabled="disabledSelect.disabled" @updateVModel="() => form.errors.id = ''"
				:loadingCaret="loadingAddStoredItem">
				<template #nooptions="{ search }: { search: string }">
					<div class="px-2 py-3" @click="() => createStoredItems({ id: search, reference: search }, [])">
						<font-awesome-icon v-if="search !== '' || search" :icon="['fas', 'plus']" class="mr-3" />
						{{ search !== "" || search ? `${trans(`Create stored Items`)} : ${search}` : trans("No Result")
						}}
					</div>
				</template>
				<template #noresults="{ search }: { search: string }">
					<div class="px-2 py-3" @click="() => createStoredItems({ id: search, reference: search }, [])">
						<font-awesome-icon :icon="['fas', 'plus']" class="mr-3" />
						{{ `${trans(`Create stored Items`)} : ${search}` }}
					</div>
				</template>
				<template v-if="!disabledSelect.edit" #caret="{ handleCaretClick, isOpen }">
					<div class="px-2">
						<font-awesome-icon v-if="!disabledSelect.disabled" :icon="['fas', 'chevron-down']"
							class="text-xs mr-2" />
						<font-awesome-icon v-else :icon="faTrashAlt" class="text-xs mr-2 text-red-600"
							@click="deleteStoredItems(false)" />
					</div>
				</template>
				<template #singlelabel="{ value }">
					<div class="flex justify-start w-full px-2 gap-3">
						{{ value["reference"] }}
						<Tag label="New" :theme="4" v-if="newStoredItem" >
						<template #label>
							<font-awesome-icon  :icon="faSparkles" v-tooltip="'New Stored Item'" class="text-xs  text-yellow-500"/>
							New
						</template>
						</Tag>
					</div>
				</template>
			</SelectQuery>
		</div>
		<p v-if="get(form, ['errors', 'id'])" class="mt-2 text-sm text-red-500">
			{{ form.errors.id }}
		</p>


		<div class="mt-3">
			<label class="block text-sm font-medium text-gray-700">{{ trans("Quantity") }}</label>
			<div class=" mt-1 flex items-center gap-2">
				<input v-model="form.quantity" id="quantity" name="quantity" :autofocus="true" type="number"
					autocomplete="quantity" :required="true" :min="1" @update:modelValue="form.errors.quantity = ''"
					class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
				<Button type="tertiary" :icon="faPlus" @click="incrementQuantity" />
				<Button type="tertiary" :icon="faMinus" @click="decrementQuantity" />
			</div>
			<p v-if="get(form, ['errors', 'quantity'])" class="mt-2 text-sm text-red-600">
				{{ form.errors.quantity }}
			</p>
		</div>
	</div>
	<div v-else>
	<div class="flex justify-center mb-6"><font-awesome-icon :icon="['far', 'exclamation-triangle']" class="text-8xl text-yellow-500"/></div>
		
		<div class="text-center font-semibold text-2xl mb-6">
			{{ trans('Do you want to delete') }} {{ newStoredItem?.reference }} ?
		</div>
	</div>

	<div v-if="!messageMode" class="grid grid-cols-2 gap-3">
		<div class="col-span-1 relative">
			<Button full type="tertiary" label="Cancel" @click="onCancel"></Button>
		</div>
		<div class="col-span-1">
			<Button full @click="onSaved" type="save" :loading="props.form.processing"></Button>
		</div>
	</div>

	<div v-else class="grid grid-cols-2 gap-3">
		<div class="col-span-1 relative">
			<Button full type="tertiary" label="No" @click="()=>emits('closeModal')"></Button>
		</div>
		<div class="col-span-1">
			<Button full @click="deleteStoredItems(true)" label="Yes" :loading="props.form.processing"></Button>
		</div>
	</div>

</template>
