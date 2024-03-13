<script setup lang="ts">
import { get, defaultTo, fromPairs } from "lodash"
import axios from "axios"
import { onMounted, ref, defineProps } from "vue"
import { useForm } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { notify } from "@kyvg/vue3-notification"
import PureInput from "@/Components/Pure/PureInput.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faSpinnerThird, faSearch } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { routeType } from "@/types/route"

// Modal: on Add Pallet

library.add(faSpinnerThird, faSearch)

const props = defineProps<{
	dataRoute: routeType
	saveRoute: routeType
	descriptor: object
}>()

const emits = defineEmits()
const dataList = ref([])
const loading = ref(false)
const form = useForm({ pallets: [] })
const checkedAll = ref(false)
const tableFilter = useForm({
	search: null,
})


const closeModal = () => {
	emits('onClose')
}

const getData = async () => {
	loading.value = true
	try {
		const response = await axios.get(
			route(props.dataRoute.name, props.dataRoute.parameters),
			{ params: { ['pallets_filter[global]']: tableFilter.search } } // Changed from { search: tableFilter.filter }
		)
		console.log(response.data.data)
		dataList.value = response.data.data
		loading.value = false
	} catch (error) {
		console.log("error", error)
		loading.value = false
		notify({
			title: error.response.statusText,
			text: error.message,
			type: "error",
		})
	}
}

const selectAll = () => {
	const value = []
	if (!checkedAll.value) {
		dataList.value.forEach((item) => value.push(item.id))
		checkedAll.value = true
	} else checkedAll.value = false
	form.pallets = value
}

const onChecked = (value) => {
	if (form.data().pallets.length > dataList.value.length && form.data().pallets.length != 0)
		checkedAll.value = false
	if (form.data().pallets.length == dataList.value.length && form.data().pallets.length != 0)
		checkedAll.value = true
	else checkedAll.value = false
}

const onSubmitPallet = () => {
	form.post(route(props.saveRoute.name, props.saveRoute.parameters), {
		preserveScroll: true,
		onBefore: () => {
			loading.value = true
		},
		onFinish: () => {
			loading.value = false
		},
		onSuccess: () => {
			form.reset("pallets")
			checkedAll.value = false
			getData()
			closeModal()
		},
	})
}

onMounted(getData)
</script>

<template>
	<div class="sm:flex sm:items-center px-2">
		<div class="flex-auto">
			<div class="w-1/4 pt-2">
				<PureInput v-model="tableFilter.search" placeholder="Search" :loading="loading" :copy-button="true"
					@keyup.enter.native="getData">
					<template #copyButton>
						<div class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer opacity-20 hover:opacity-75 active:opacity-100"
							@click="() => getData()">
							<FontAwesomeIcon icon="fad fa-search" class="text-lg leading-none" aria-hidden="true" />
						</div>
					</template>
				</PureInput>
			</div>
		</div>
		<div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
			<Button :style="'create'" label="Add Pallet" :disabled="!form.pallets.length" :key="form.pallets.length"
				@click="onSubmitPallet"></Button>
		</div>
	</div>
	<div class="px-1 sm:px-1 lg:px-8">
		<div class="mt-8 flow-root">
			<div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
				<div
					class="inline-block min-w-full py-2 align-middle shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
					<table class="min-w-full border-separate border-spacing-0">
						<thead class="bg-gray-50">
							<tr>
								<th scope="col"
									class="sticky top-0 z-10 border-b border-gray-300 bg-white bg-opacity-75 py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:pl-6 lg:pl-8">
									<input type="checkbox" :checked="checkedAll" @change="selectAll"
										class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
								</th>
								<th v-for="(item, index) in descriptor.column" :key="`header-${item.key}`" scope="col"
									class="sticky top-0 z-10 hidden border-b border-gray-300 bg-white bg-opacity-75 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 backdrop-blur backdrop-filter sm:table-cell">
									{{ item.label }}</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(pallet, index) in dataList" :key="pallet.id">
								<td
									:class="[index !== dataList.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8']">
									<input type="checkbox" :id="pallet.id" :value="pallet.id" v-model="form.pallets"
										@change="onChecked"
										class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
								</td>
								<td v-for="(column, columnIndex) in descriptor.column" :key="`header-${column.key}`"
									:class="[index !== dataList.length - 1 ? 'border-b border-gray-200' : '', 'whitespace-nowrap hidden px-3 py-4 text-sm text-gray-500 sm:table-cell']">
									{{ defaultTo(get(pallet, [column.key]), "-") }}</td>
							</tr>
						</tbody>
					</table>
					<div v-if="loading" class="flex justify-center items-center w-full h-full p-12">
						<div>
							<FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin w-6" aria-hidden="true" />
						</div>
					</div>
					<div v-if="dataList.length == 0 && !loading"
						class="flex justify-center items-center w-full h-full p-12">
						<div>
							No Data
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>