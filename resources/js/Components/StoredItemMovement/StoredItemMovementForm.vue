<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { defineEmits } from "vue"
import { trans } from "laravel-vue-i18n"
import SelectQuery from "@/Components/SelectQuery.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { get } from "lodash"
import { routeType } from "@/types/route"
import PureRadio from "@/Components/Pure/PureRadio.vue"

library.add(faPlus)
const props = defineProps<{
	form: {}
	locationRoute: {
		index : routeType
	}
	palletRoute: {
		index: routeType
	}
	pallet : {}
}>()

const emits = defineEmits<{
    (e: 'onSave'): void
}>()


const onSubmit=()=>{
	const data = props.form.data()
	console.log('data',data)
	const finalData = data.type != 'location' ? { pallet_id: data.pallet_id, quantity : data.quantity } : { location_id : data.location_id, quantity : data.quantity }
	emits('onSave',finalData)
}

</script>


<template>
	<div>
		<label class="block text-sm font-medium text-gray-700">{{ trans("Type") }}</label>
		<div
			class="flex flex-auto justify-evenly border rounded-md w-full dark:border-gray-600/60 dark:text-white mt-1">
			<button
				class="border-none px-2 py-1 rounded-md w-full"
				:class="{ 'bg-blue-500 text-white': form.type === 'pallet' }">
				<input type="radio" class="hidden" id="pallet" value="pallet" v-model="form.type" />
				<label
					for="pallet"
					class="cursor-pointer transition-colors duration-300 ease-in-out px-2 py-1 rounded-md"
					>Pallet</label
				>
			</button>

			<button
				class="border-none px-2 py-1 rounded-md w-full"
				:class="{ 'bg-blue-500 text-white': form.type === 'location' }">
				<input
					type="radio"
					class="hidden"
					id="location"
					value="location"
					v-model="form.type" />
				<label
					for="location"
					class="cursor-pointer transition-colors duration-300 ease-in-out px-2 py-1 rounded-md"
					>Location</label
				>
			</button>
		</div>
	</div>

	<div v-if="form.type == 'pallet'">
		<label class="block text-sm font-medium text-gray-700">{{ trans("Pallet") }}</label>
		<div class="mt-1">
			<SelectQuery
				:urlRoute="route(palletRoute.index.name, palletRoute.index.parameters)"
				:value="form"
				:placeholder="'Select Pallet'"
				:required="true"
				:trackBy="'reference'"
				:label="'reference'"
				:valueProp="'id'"
				:closeOnSelect="true"
				:clearOnSearch="false"
				:fieldName="'pallet_id'" />
		</div>
		<p v-if="get(form, ['errors', 'pallet_id'])" class="mt-2 text-sm text-red-600">{{ form.errors.pallet_id }}</p>
	</div>

	<div v-else-if="form.type == 'location'">
		<label class="block text-sm font-medium text-gray-700">{{ trans("Location") }}</label>
		<div class="mt-1">
			<SelectQuery
				:urlRoute="route(locationRoute.index.name,{ ...locationRoute.index.parameters, pallet : pallet.id })"
				:value="form"
				:placeholder="'Select Locations'"
				:required="true"
				:trackBy="'code'"
				:label="'code'"
				:valueProp="'id'"
				:closeOnSelect="true"
				:clearOnSearch="false"
				:fieldName="'location_id'" />
		</div>
		<p v-if="get(form, ['errors', 'location_id'])" class="mt-2 text-sm text-red-600">{{ form.errors.location_id }}</p>
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
				class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
		</div>
		<p v-if="get(form, ['errors', 'quantity'])" class="mt-2 text-sm text-red-600">{{ form.errors.quantity }}</p>
	</div>

	<div class="space-y-2">
		<Button full  label="Submit" :loading="props.form.processing" @click="onSubmit"> </Button>
	</div>
</template>

<style></style>
