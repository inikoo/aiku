<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { trans } from "laravel-vue-i18n"
import SelectQuery from "@/Components/SelectQuery.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { get } from "lodash"
import { routeType } from "@/types/route"
import { ref } from 'vue'
import { v4 as uuidv4 } from 'uuid';

library.add(faPlus)

const props = defineProps<{
	storedItemsRoute: {
        store: routeType
        index: routeType
    }
	form: {}
	stored_items: {}[]
}>()

const _selectQuery = ref(null)
const key = ref(uuidv4())

const emits = defineEmits<{
    (e: 'onSave', event: any): void
}>()

const createPallet = async (option, select) => {
	try {
		const response: any = await axios.post(
			route(props.storedItemsRoute.store.name, props.storedItemsRoute.store.parameters),
			{ reference: option.id },
			{ headers: { "Content-Type": "multipart/form-data" } }
		)
		props.form.errors = {}
		console.log(response)
		_selectQuery.value.page = 1
		return response.data
	} catch (error: any) {
		console.log(error)
		props.form.errors.id = error.response.data.message
		notify({
			title: "Failed to add new stored items",
			text: error.data.message ? error.data.message : 'failed to create stored item',
			type: "error",
		})
		return false
	}
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
	<div>
		<label class="block text-sm font-medium text-gray-700">{{ trans("Reference") }}</label>
		<div class="mt-1">
			<SelectQuery
			    :key="key"
				ref="_selectQuery"
				:urlRoute="route(storedItemsRoute.index.name, storedItemsRoute.index.parameters)"
				:value="form"
				:placeholder="'Select or add item'"
				:required="true"
				:trackBy="'reference'"
				:label="'reference'"
				:valueProp="'id'"
				:closeOnSelect="true"
				:clearOnSearch="false"
				:fieldName="'id'"
				:createOption="true"
				:onCreate="createPallet"
				@afterCreate="(value,option) => form['id'] = value"
                @updateVModel="() => form.errors.id = ''"
            />
		</div>
		<p v-if="get(form, ['errors', 'id'])" class="mt-2 text-sm text-red-500">
			{{ form.errors.id }}
		</p>
	</div>

	<div>
		<label class="block text-sm font-medium text-gray-700">{{ trans("Quantity") }}</label>
		<div class="mt-1">
			<input
				v-model="form.quantity"
				id="quantity"
				name="quantity"
				:autofocus="true"
				type="number"
				autocomplete="quantity"
				:required="true"
				:min="1"
                @update:modelValue="form.errors.quantity = ''"
				class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
		</div>
		<p v-if="get(form, ['errors', 'quantity'])" class="mt-2 text-sm text-red-600">
			{{ form.errors.quantity }}
		</p>
	</div>


	<div class="space-y-2">
		<Button full @click="onSaved" label="Submit" :loading="props.form.processing"> </Button>
	</div>
</template>
